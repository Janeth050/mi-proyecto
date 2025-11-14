@extends('layouts.app')

@section('title','Ajustes de notificaciones')

@section('content')
<style>
  .card{background:#fff;border:1px solid #d9c9b3;border-radius:16px;padding:16px;max-width:680px;margin:16px auto;box-shadow:0 8px 24px rgba(0,0,0,.06)}
  .label{font-weight:700;color:#5c3a21;margin-bottom:6px}
  .hint{color:#7a6b5f;font-size:13px}
  .row{display:grid;grid-template-columns:1fr;gap:10px;margin-bottom:14px}
  input[type="text"]{width:100%;border:1px solid #d9c9b3;border-radius:12px;padding:10px}
  .switch{display:flex;align-items:center;gap:10px}
  .btn{border:none;border-radius:12px;padding:10px 14px;font-weight:700;cursor:pointer;background:#8b5e3c;color:#fff}
  .flash{margin-bottom:10px;color:#2b8a3e;font-weight:700}
  .error{color:#c04444}
</style>

<div class="card">
  <h2 style="margin:0 0 10px;color:#8b5e3c">Notificaciones</h2>

  @if(session('success'))
    <div class="flash">{{ session('success') }}</div>
  @endif

  @if($errors->any())
    <div class="error">
      @foreach($errors->all() as $e)
        <div>• {{ $e }}</div>
      @endforeach
    </div>
  @endif

  <form method="POST" action="{{ route('settings.notifications.save') }}">
    @csrf
    <div class="row">
      <label class="label">Número de WhatsApp (destino)</label>
      <input type="text" name="whatsapp_phone" value="{{ old('whatsapp_phone', $user->whatsapp_phone) }}"
             placeholder="Ej: 5281XXXXXXXX o +5281XXXXXXXX">
      <div class="hint">Se usará este número para recibir alertas (bajo stock y listas). Déjalo vacío para desactivar el envío.</div>
    </div>

    <div class="row">
      <label class="label">Alertas de bajo stock</label>
      <label class="switch">
        <input type="hidden" name="notify_low_stock" value="0">
        <input type="checkbox" name="notify_low_stock" value="1" {{ $user->notify_low_stock ? 'checked' : '' }}>
        <span>Activar envío automático cuando un producto quede por debajo del mínimo</span>
      </label>
    </div>

    <button class="btn" type="submit">Guardar cambios</button>
  </form>
</div>
@endsection

