<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\MovimientoController;
use App\Http\Controllers\KardexController;
use App\Http\Controllers\ListaPedidoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\NotificationSettingsController;
use App\Models\Producto;
use App\Models\Proveedor;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

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

    // ===== Ajustes por usuario (admin)
    Route::get('/ajustes/notificaciones', [NotificationSettingsController::class, 'show'])
        ->name('settings.notifications');
    Route::post('/ajustes/notificaciones', [NotificationSettingsController::class, 'update'])
        ->name('settings.notifications.save');

    // ===== Productos
    Route::get('/productos/{producto}/kardex-json', [ProductoController::class, 'kardexJson'])->name('productos.kardex.json');
    Route::resource('productos', ProductoController::class);

    // Inline categoría/unidad
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

    // ===== Listas
    Route::middleware('can:manage-lists')->group(function () {
        Route::get('listas',                [ListaPedidoController::class, 'index'])->name('listas.index');
        Route::get('listas/{lista}',        [ListaPedidoController::class, 'show'])->name('listas.show');
        Route::delete('listas/{lista}',     [ListaPedidoController::class, 'destroy'])->name('listas.destroy');

        Route::post('listas/{lista}/archivar', [ListaPedidoController::class, 'archivar'])->name('listas.archivar');
        Route::post('/listas/quick-add/{producto}', [ListaPedidoController::class, 'quickAddFromProducto'])->name('listas.quickadd');
        Route::get('listas/{lista}/export', [ListaPedidoController::class, 'exportCsv'])->name('listas.export');
    });

    // ===== Usuarios (solo admin)
    Route::resource('usuarios', UsuarioController::class)
        ->parameters(['usuarios' => 'usuario'])
        ->except(['show'])
        ->middleware('can:manage-users');

    // TEST: envía un WhatsApp de prueba con CallMeBot y muestra la respuesta
Route::get('/debug/whatsapp', function () {
    $apiKey = trim(env('CALLMEBOT_APIKEY', ''));
    $enabled = filter_var(env('CALLMEBOT_ENABLED', false), FILTER_VALIDATE_BOOLEAN);

    // usa tu número guardado en ajustes o ponlo aquí directo para probar:
    $phone = Auth::user()?->whatsapp_phone ?? '5218122573165'; // <-- cámbialo si ocupas
    $phone = preg_replace('/\D+/', '', $phone); // solo dígitos, SIN +

    $msg = '✅ Prueba desde Laravel (debug).';

    $query = http_build_query([
        'phone'  => $phone,
        'text'   => $msg,
        'apikey' => $apiKey,
    ]);

    $url = "https://api.callmebot.com/whatsapp.php?".$query;

    // Llamada HTTP
    $resp = \Illuminate\Support\Facades\Http::withOptions([
        'verify'  => false,
        'timeout' => 20,
    ])->get($url);

    return response()->json([
        'enabled' => $enabled,
        'apiKey'  => $apiKey,
        'phone'   => $phone,
        'url'     => $url,
        'status'  => $resp->status(),
        'ok'      => $resp->ok(),
        'body'    => $resp->body(),
    ]);
})->middleware('auth');

});

Route::fallback(function () { abort(404); });
