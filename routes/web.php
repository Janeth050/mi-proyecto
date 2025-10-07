<?php

use Illuminate\Support\Facades\Route;

// Importa tus controladores
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\MovimientoController;
use App\Http\Controllers\KardexController;
use App\Http\Controllers\ListaPedidoController;
use App\Http\Controllers\ListaPedidoItemController;

/*
|--------------------------------------------------------------------------
| Rutas públicas / inicio
|--------------------------------------------------------------------------
|
| Si usas Breeze/Jetstream, los endpoints de auth (login, register, etc.)
| vienen en routes/auth.php. Abajo los incluimos.
|
*/

// Página raíz → manda al login (si no está logueado)
Route::redirect('/', '/login')->name('home');

// Rutas de autenticación (Breeze/Jetstream)
require __DIR__ . '/auth.php';

/*
|--------------------------------------------------------------------------
| Rutas protegidas por login
|--------------------------------------------------------------------------
|
| Todo lo que está dentro de este grupo requiere sesión iniciada.
|
*/
Route::middleware(['auth'])->group(function () {

    // Dashboard: decide admin o empleado según el rol del usuario
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /*
    |----------------------------------------------------------------------
    | Productos (CRUD)
    |----------------------------------------------------------------------
    | GET    /productos              -> productos.index
    | GET    /productos/create       -> productos.create
    | POST   /productos              -> productos.store
    | GET    /productos/{id}         -> productos.show
    | GET    /productos/{id}/edit    -> productos.edit
    | PUT    /productos/{id}         -> productos.update
    | DELETE /productos/{id}         -> productos.destroy
    */
    Route::resource('productos', ProductoController::class);

    /*
    |----------------------------------------------------------------------
    | Proveedores (CRUD)
    |----------------------------------------------------------------------
    */
    Route::resource('proveedors', ProveedorController::class);

    /*
    |----------------------------------------------------------------------
    | Movimientos de inventario (Entradas/Salidas)
    |----------------------------------------------------------------------
    */
    Route::resource('movimientos', MovimientoController::class)->only(['index','create','store','show']);

    /*
    |----------------------------------------------------------------------
    | Kardex (histórico por producto y global con filtros)
    |----------------------------------------------------------------------
    */
    Route::get('/kardex', [KardexController::class, 'index'])->name('kardex.index');
    Route::get('/kardex/producto/{producto}', [KardexController::class, 'showProducto'])->name('kardex.producto');

    /*
    |----------------------------------------------------------------------
    | Listas de pedido (no crean movimientos)
    |----------------------------------------------------------------------
    | Solo creamos/mostramos/eliminamos listas, y gestionamos sus ítems.
    */
    Route::resource('listas', ListaPedidoController::class)->only(['index','create','store','show','destroy']);
    Route::post('listas/{lista}/enviar',   [ListaPedidoController::class, 'enviar'])  ->name('listas.enviar');
    Route::post('listas/{lista}/cerrar',   [ListaPedidoController::class, 'cerrar'])  ->name('listas.cerrar');
    Route::post('listas/{lista}/cancelar', [ListaPedidoController::class, 'cancelar'])->name('listas.cancelar');

    // Ítems dentro de una lista
    Route::post  ('listas/{lista}/items',               [ListaPedidoItemController::class, 'store' ]) ->name('listas.items.store');
    Route::put   ('listas/{lista}/items/{item}',        [ListaPedidoItemController::class, 'update']) ->name('listas.items.update');
    Route::delete('listas/{lista}/items/{item}',        [ListaPedidoItemController::class, 'destroy'])->name('listas.items.destroy');
});

/*
|--------------------------------------------------------------------------
| Fallback (404 amable)
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
