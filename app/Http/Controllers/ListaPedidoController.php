<?php

namespace App\Http\Controllers;

use App\Models\ListaPedido;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;   // <-- IMPORTANTE
use Illuminate\Support\Facades\Http;  // <-- IMPORTANTE
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListaPedidoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        // Solo administradores
        $this->middleware(function ($request, $next) {
            $u = $request->user();
            if (!$u) abort(401, 'Debes iniciar sesión.');
            $role = strtolower((string)($u->role ?? $u->rol ?? ''));
            if ($role !== 'admin') abort(403, 'Solo administradores pueden acceder a Listas.');
            return $next($request);
        });
    }

    /** Vista principal: muestra lista activa + histórico (cerradas) */
    public function index(Request $request)
    {
        $userId = Auth::id();

        $activa = ListaPedido::with(['items.producto.unidad','items.proveedor','creador'])
            ->activaDe($userId)
            ->first();

        $historico = ListaPedido::withCount('items')
            ->where('user_id', $userId)
            ->where('status', 'cerrada')
            ->orderByDesc('id')
            ->get();

        return view('listas.index', compact('activa','historico'));
    }

    /** Detalle JSON (para modal/ver) */
    public function show(ListaPedido $lista)
    {
        $lista->load(['items.producto.unidad', 'items.proveedor', 'creador']);
        return response()->json([
            'ok'    => true,
            'lista' => $lista,
            'total_estimado' => $lista->total_estimado,
        ]);
    }

    /** Quick add desde Productos → usa o crea la lista activa */
    public function quickAddFromProducto(Request $request, Producto $producto)
    {
        $user = $request->user();

        $data = $request->validate([
            'cantidad'        => ['required','integer','min:1'],
            'proveedor_id'    => ['nullable','exists:proveedors,id'],
            'precio_estimado' => ['nullable','numeric','min:0'],
        ]);

        $lista = ListaPedido::activaOrCreateFor($user->id);

        // Evitar duplicado por producto
        if ($lista->items()->where('producto_id', $producto->id)->exists()) {
            return response()->json([
                'ok' => false,
                'message' => 'Este producto ya está en tu lista en borrador.',
                'lista_id' => $lista->id,
            ], 409);
        }

        $item = $lista->items()->create([
            'producto_id'     => $producto->id,
            'cantidad'        => (int)$data['cantidad'],
            'proveedor_id'    => $data['proveedor_id'] ?? null,
            'precio_estimado' => $data['precio_estimado'] ?? null,
        ]);

        return response()->json([
            'ok'          => true,
            'message'     => 'Producto agregado a tu lista.',
            'lista_id'    => $lista->id,
            'item_id'     => $item->id,
            'producto_id' => $producto->id,
        ]);
    }

    /** Archivar: de borrador → cerrada (histórico) */
    public function archivar(ListaPedido $lista)
    {
        if ($lista->status !== 'borrador') {
            return response()->json(['ok'=>false,'message'=>'Solo puedes archivar una lista activa.'], 422);
        }
        if ($lista->items()->count() === 0) {
            return response()->json(['ok'=>false,'message'=>'No puedes archivar una lista vacía.'], 422);
        }
        $lista->update(['status' => 'cerrada']);
        return response()->json(['ok'=>true,'message'=>'Lista archivada.','lista'=>$lista]);
    }

    /** Eliminar lista (solo si está en borrador) */
    public function destroy(ListaPedido $lista)
    {
        if ($lista->status !== 'borrador') {
            return response()->json(['ok'=>false,'message'=>'Solo puedes eliminar listas activas (borrador).'], 422);
        }
        $lista->items()->delete();
        $lista->delete();
        return response()->json(['ok'=>true,'message'=>'Lista eliminada.']);
    }

    /** Exportación CSV simple */
    public function exportCsv(ListaPedido $lista): StreamedResponse
    {
        $lista->load(['items.producto','items.proveedor','creador']);

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="lista_'.$lista->id.'.csv"',
        ];

        return response()->stream(function () use ($lista) {
            $out = fopen('php://output', 'w');
            // BOM para Excel
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['Lista #', $lista->id]);
            fputcsv($out, ['Creador', $lista->creador->name ?? '-']);
            fputcsv($out, ['Estatus', strtoupper($lista->status)]);
            fputcsv($out, ['Total estimado', number_format($lista->total_estimado,2)]);
            fputcsv($out, []); // línea en blanco
            fputcsv($out, ['#','Producto','Cantidad','Proveedor','Precio est.','Importe']);

            foreach ($lista->items as $it) {
                $imp = $it->precio_estimado !== null ? $it->precio_estimado * $it->cantidad : null;
                fputcsv($out, [
                    $it->id,
                    $it->producto->nombre ?? '-',
                    $it->cantidad,
                    $it->proveedor->nombre ?? '-',
                    $it->precio_estimado !== null ? number_format($it->precio_estimado,2) : '',
                    $imp !== null ? number_format($imp,2) : '',
                ]);
            }
            fclose($out);
        }, 200, $headers);
    }
}
