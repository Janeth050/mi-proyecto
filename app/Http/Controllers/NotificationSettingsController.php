<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationSettingsController extends Controller
{
    public function show()
    {
        /** @var User $user */
        $user = User::findOrFail(Auth::id());
        return view('settings.notifications', compact('user'));
    }

    public function update(Request $request)
    {
        /** @var User $user */
        $user = User::findOrFail(Auth::id());

        $role = strtolower((string)($user->role ?? $user->rol ?? ''));
        if ($role !== 'admin') abort(403, 'Solo administradores pueden modificar estas opciones.');

        $data = $request->validate([
            'whatsapp_phone'   => ['nullable','string','max:20'],
            'notify_low_stock' => ['sometimes','boolean'],
        ]);

        $normalized = $this->normalizePhone($data['whatsapp_phone'] ?? null);
        if ($normalized === false) {
            return back()
                ->withErrors(['whatsapp_phone' => 'Número inválido. Usa 10–15 dígitos. En MX se usa 521 + 10 dígitos.'])
                ->withInput();
        }

        $user->whatsapp_phone   = $normalized ?: null;
        $user->notify_low_stock = (bool)($data['notify_low_stock'] ?? false);
        $user->save();

        return back()->with('success','Notificaciones actualizadas.');
    }

    /**
     * Normaliza el teléfono a solo dígitos.
     * Reglas MX para CallMeBot:
     *   - Si recibe 10 dígitos, antepone 521 (no 52).
     *   - Si recibe 12 dígitos que empiezan con 52, inserta el 1 -> 521 + 10.
     *   - Si ya viene 521 + 10 (13 dígitos), lo deja igual.
     */
    private function normalizePhone(?string $raw)
    {
        if ($raw === null) return null;

        $digits = preg_replace('/\D+/', '', $raw);
        if ($digits === '') return null;

        // Ya viene como 521 + 10 dígitos (13)
        if (strlen($digits) === 13 && str_starts_with($digits, '521')) {
            return $digits;
        }

        // Viene como 10 dígitos (MX) -> forzamos 521
        if (strlen($digits) === 10) {
            return '521'.$digits;
        }

        // Viene como 52 + 10 (12) -> convertimos a 521 + 10 (13)
        if (strlen($digits) === 12 && str_starts_with($digits, '52')) {
            return '521'.substr($digits, 2); // inserta el 1
        }

        // Acepta otros países entre 10 y 15 dígitos tal cual
        if (strlen($digits) >= 10 && strlen($digits) <= 15) {
            return $digits;
        }

        return false;
    }
}
