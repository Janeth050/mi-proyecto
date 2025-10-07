<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    private function adminOnly(): void
    {
        $u = Auth::user();
        if (!$u || $u->role !== 'admin') {
            abort(403, 'Solo administradores.');
        }
    }

    public function index()
    {
        $this->adminOnly();
        $usuarios = User::orderBy('name')->paginate(15);
        return view('usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        $this->adminOnly();
        return view('usuarios.create');
    }

    public function store(Request $request)
    {
        $this->adminOnly();
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role'     => 'required|in:admin,empleado',
        ]);

        User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => $data['role'],
        ]);

        return redirect()->route('usuarios.index')->with('success','Usuario creado.');
    }

    public function edit(User $usuario)
    {
        $this->adminOnly();
        return view('usuarios.edit', compact('usuario'));
    }

    public function update(Request $request, User $usuario)
    {
        $this->adminOnly();
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email,'.$usuario->id,
            'role'     => 'required|in:admin,empleado',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $usuario->name  = $data['name'];
        $usuario->email = $data['email'];
        $usuario->role  = $data['role'];

        if (!empty($data['password'])) {
            $usuario->password = Hash::make($data['password']);
        }

        $usuario->save();

        return redirect()->route('usuarios.index')->with('success','Usuario actualizado.');
    }

    public function destroy(User $usuario)
    {
        $this->adminOnly();

        // Evitar que un admin se borre a sí mismo
        if (Auth::id() === $usuario->id) {
            return back()->withErrors('No puedes eliminar tu propio usuario.');
        }

        $usuario->delete();
        return redirect()->route('usuarios.index')->with('success','Usuario eliminado.');
    }
}
