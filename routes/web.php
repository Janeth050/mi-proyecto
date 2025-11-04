<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\MovimientoController;
use App\Http\Controllers\KardexController;
use App\Http\Controllers\ListaPedidoController;
use App\Http\Controllers\ListaPedidoItemController;
use App\Http\Controllers\UsuarioController;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

Route::get('/__diag-gate', function () {
    $u = Auth::user();
    return [
        'user' => $u ? ['id' => $u->id, 'name' => $u->name, 'email' => $u->email, 'role' => $u->role] : null,
        'can_manage_lists' => Gate::forUser($u)->allows('manage-lists'),
    ];
})->middleware('auth');

Route::redirect('/', '/login')->name('home');
require __DIR__ . '/auth.php';

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ===== Productos =====
    Route::get('/productos/{producto}/kardex-json', [ProductoController::class, 'kardexJson'])->name('productos.kardex.json');
    Route::resource('productos', ProductoController::class);

    // Inline categoría/unidad (en ProductoController)
    Route::post('/productos/categorias/inline', [ProductoController::class, 'storeCategoriaInline'])->name('productos.categorias.inline');
    Route::delete('/productos/categorias/{categoria}', [ProductoController::class, 'destroyCategoriaInline'])->name('productos.categorias.destroy');

    Route::post('/productos/unidades/inline',  [ProductoController::class, 'storeUnidadInline'])->name('productos.unidades.inline');
    Route::delete('/productos/unidades/{unidad}', [ProductoController::class, 'destroyUnidadInline'])->name('productos.unidades.destroy');

    Route::get('/productos/{producto}/sugerencia', [ProductoController::class, 'sugerenciaReposicion'])->name('productos.sugerencia');

    // ===== Proveedores
    Route::resource('proveedors', ProveedorController::class)->middleware('can:manage-suppliers');

    // ===== Movimientos
    Route::resource('movimientos', MovimientoController::class)->only(['index','create','store','show']);
    Route::post('movimientos/{movimiento}/cancelar', [MovimientoController::class, 'cancelar'])->middleware('can:manage-movements')->name('movimientos.cancelar');
    Route::delete('movimientos/{movimiento}', [MovimientoController::class, 'destroy'])->middleware('can:manage-movements')->name('movimientos.destroy');

    // ===== Kardex
    Route::get('/kardex', [KardexController::class, 'index'])->name('kardex.index');
    Route::get('/kardex/producto/{producto}', [KardexController::class, 'showProducto'])->name('kardex.producto');

    // ===== Listas (solo admin por Gate)
    Route::middleware('can:manage-lists')->group(function () {
        Route::get('listas',                [ListaPedidoController::class, 'index'])->name('listas.index');
        Route::get('listas/create',         [ListaPedidoController::class, 'create'])->name('listas.create');
        Route::post('listas',               [ListaPedidoController::class, 'store'])->name('listas.store');
        Route::get('listas/{lista}',        [ListaPedidoController::class, 'show'])->name('listas.show');
        Route::delete('listas/{lista}',     [ListaPedidoController::class, 'destroy'])->name('listas.destroy');

        Route::post('listas/{lista}/enviar',   [ListaPedidoController::class, 'enviar'])->name('listas.enviar');
        Route::post('listas/{lista}/cerrar',   [ListaPedidoController::class, 'cerrar'])->name('listas.cerrar');
        Route::post('listas/{lista}/cancelar', [ListaPedidoController::class, 'cancelar'])->name('listas.cancelar');

        // Quick add desde Productos
        Route::post('/listas/quick-add/{producto}', [ListaPedidoController::class, 'quickAddFromProducto'])
            ->name('listas.quickadd');
        
        // Ítems de la lista
        Route::post('listas/{lista}/items',           [ListaPedidoItemController::class, 'store'])->name('listas.items.store');
        Route::put('listas/{lista}/items/{item}',     [ListaPedidoItemController::class, 'update'])->name('listas.items.update');
        Route::delete('listas/{lista}/items/{item}',  [ListaPedidoItemController::class, 'destroy'])->name('listas.items.destroy');
    });

    // ===== Usuarios (solo admin)
    Route::resource('usuarios', UsuarioController::class)
        ->parameters(['usuarios' => 'usuario'])
        ->except(['show'])
        ->middleware('can:manage-users');
});

Route::fallback(function () { abort(404); });
