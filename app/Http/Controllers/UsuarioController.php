<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        // 🔒 Admin obligatorio para todo el controlador (cierre rápido)
        $this->middleware(function ($request, $next) {
            $u = $request->user();
            if (!$u) abort(401, 'Debes iniciar sesión.');
            $role = strtolower((string)($u->role ?? $u->rol ?? ''));
            if ($role !== 'admin') abort(403, 'Solo administradores.');
            return $next($request);
        });
    }

    // 🔐 Método usado en las acciones (faltaba y daba error)
    protected function authorizeAdmin(): void
    {
        $u = Auth::user();
        if (!$u) abort(401, 'Debes iniciar sesión.');
        $role = strtolower((string)($u->role ?? $u->rol ?? ''));
        if ($role !== 'admin') abort(403, 'Solo administradores.');
    }

    public function index()
    {
        $this->authorizeAdmin();
        $usuarios = User::orderBy('name')->paginate(15);
        return view('usuarios.index', compact('usuarios'));
    }

    public function show(User $usuario)
    {
        $this->authorizeAdmin();

        return response()->json([
            'ok'      => true,
            'usuario' => $usuario->only(['id','name','email','role','created_at','updated_at']),
        ]);
    }

    public function create()
    {
        $this->authorizeAdmin();
        return response()->json([
            'ok'      => true,
            'defaults'=> ['role' => 'empleado']
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255','unique:users,email'],
            'password' => ['required','string','min:6','confirmed'],
            'role'     => ['required', Rule::in(['admin','empleado'])],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => $data['role'],
        ]);

        return response()->json([
            'ok'      => true,
            'message' => 'Usuario creado.',
            'usuario' => $user->only(['id','name','email','role']),
        ]);
    }

    public function edit(User $usuario)
    {
        $this->authorizeAdmin();

        return response()->json([
            'ok'      => true,
            'usuario' => $usuario->only(['id','name','email','role']),
        ]);
    }

    public function update(Request $request, User $usuario)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255', Rule::unique('users','email')->ignore($usuario->id)],
            'role'     => ['required', Rule::in(['admin','empleado'])],
            'password' => ['nullable','string','min:6','confirmed'],
        ]);

        $usuario->name  = $data['name'];
        $usuario->email = $data['email'];
        $usuario->role  = $data['role'];

        if (!empty($data['password'])) {
            $usuario->password = Hash::make($data['password']);
        }

        $usuario->save();

        return response()->json([
            'ok'      => true,
            'message' => 'Usuario actualizado.',
            'usuario' => $usuario->only(['id','name','email','role']),
        ]);
    }

    public function destroy(User $usuario)
    {
        $this->authorizeAdmin();

        if (Auth::id() === $usuario->id) {
            return response()->json(['ok'=>false,'message'=>'No puedes eliminar tu propio usuario.'], 422);
        }

        $quedanAdmins = User::where('id','!=',$usuario->id)->where('role','admin')->exists();
        if ($usuario->role === 'admin' && !$quedanAdmins) {
            return response()->json(['ok'=>false,'message'=>'Debe quedar al menos un administrador.'], 422);
        }

        $usuario->delete();

        return response()->json([
            'ok'      => true,
            'message' => 'Usuario eliminado.',
        ]);
    }
}
