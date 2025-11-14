<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProveedorController extends Controller
{
    public function __construct()
    {
        // Autenticación
        $this->middleware('auth');

        // Doble cinturón: además del middleware can:manage-suppliers en rutas,
        // aquí validamos admin de forma tolerante (coincide con Productos).
        $this->middleware(function ($request, $next) {
            $this->authorizeAdmin();
            return $next($request);
        });
    }

    /** ======= Admin check TOLERANTE (igual que en Productos) ======= */
    protected function authorizeAdmin(): void
    {
        $u = Auth::user();
        if (!$u) abort(401, 'Debes iniciar sesión.');

        if (isset($u->is_admin) && (bool)$u->is_admin === true) return;

        $role = strtolower((string)($u->role ?? $u->rol ?? ''));
        if (in_array($role, ['admin','administrador','administradora','superadmin','super administrador','adm'], true)) return;

        abort(403, 'Solo administradores pueden acceder a Proveedores.');
    }

    /** INDEX → lista con filtro ?q= */
    public function index(Request $request)
    {
        $q = (string) $request->get('q', '');

        $proveedors = Proveedor::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('nombre', 'like', "%{$q}%")
                       ->orWhere('telefono', 'like', "%{$q}%")
                       ->orWhere('correo', 'like', "%{$q}%")
                       ->orWhere('direccion', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('id')
            ->get();

        return view('proveedors.index', compact('proveedors', 'q'));
    }

    /** SHOW → JSON para modal "Ver" */
    public function show(Proveedor $proveedor)
    {
        return response()->json(['ok'=>true,'proveedor'=>$proveedor]);
    }

    /** EDIT → JSON para precargar modal "Editar" */
    public function edit(Proveedor $proveedor)
    {
        return response()->json(['ok'=>true,'proveedor'=>$proveedor]);
    }

    /** STORE → crear proveedor (JSON) */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'    => ['required','string','max:255','unique:proveedors,nombre'],
            'telefono'  => ['nullable','string','max:50'],
            'correo'    => ['nullable','email','max:255'],
            'direccion' => ['nullable','string','max:255'],
            'notas'     => ['nullable','string'],
        ]);

        $prov = Proveedor::create($data);

        return response()->json(['ok'=>true,'message'=>'Proveedor agregado correctamente.','proveedor'=>$prov]);
    }

    /** UPDATE → actualizar proveedor (JSON) */
    public function update(Request $request, Proveedor $proveedor)
    {
        $data = $request->validate([
            'nombre'    => ['required','string','max:255', Rule::unique('proveedors','nombre')->ignore($proveedor->id)],
            'telefono'  => ['nullable','string','max:50'],
            'correo'    => ['nullable','email','max:255'],
            'direccion' => ['nullable','string','max:255'],
            'notas'     => ['nullable','string'],
        ]);

        $proveedor->update($data);

        return response()->json(['ok'=>true,'message'=>'Proveedor actualizado correctamente.','proveedor'=>$proveedor->refresh()]);
    }

    /** DESTROY → soft delete (con bloqueo si está en uso) */
    public function destroy(Proveedor $proveedor)
    {
        // Si el proveedor está referenciado, devolvemos 409
        $enMovimientos = $proveedor->movimientos()->exists();
        $enListas      = $proveedor->listaItems()->exists();

        if ($enMovimientos || $enListas) {
            return response()->json([
                'ok' => false,
                'message' => 'No se puede eliminar: el proveedor está referenciado por movimientos o listas.'
            ], 409);
        }

        $proveedor->delete();
        return response()->json(['ok'=>true,'message'=>'Proveedor eliminado correctamente.']);
    }
}
