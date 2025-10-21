<x-guest-layout>
  <style>
    :root{
      --cafe:#8b5e3c; 
      --hover:#70472e;
      --beige:#f9f3e9; 
      --borde:#d9c9b3; 
      --texto:#5c3a21;
    }

    body{
      background:var(--beige);
      font-family:'Segoe UI',sans-serif;
      color:var(--texto);
      margin:0;
    }

    .login-box{
      max-width:420px;
      margin:60px auto;
      background:#fff;
      border:1px solid var(--borde);
      border-radius:16px;
      box-shadow:0 8px 20px rgba(0,0,0,.08);
      padding:32px;
      text-align:center;
    }

    /* Logo centrado */
    .logo-container{
      display:flex;
      justify-content:center;
      align-items:center;
      margin-bottom:12px;
    }
    .logo-container img{
      width:120px;
      height:auto;
    }

    h1{
      font-size:26px;
      color:var(--cafe);
      margin-bottom:24px;
      font-weight:800;
    }

    label{
      font-weight:600;
      color:var(--texto);
      display:block;
      text-align:left;
      margin-bottom:6px;
      font-size:15px;
    }

    input[type=email],
    input[type=password]{
      width:100%;
      padding:10px 12px;
      border:1px solid var(--borde);
      border-radius:8px;
      font-size:15px;
      margin-bottom:12px;
      transition:.2s;
    }

    input:focus{
      outline:none;
      border-color:var(--cafe);
      box-shadow:0 0 0 2px rgba(139,94,60,.2);
    }

    .checkbox{
      display:flex;
      align-items:center;
      gap:6px;
      justify-content:flex-start;
      margin:10px 0 16px;
      font-size:14px;
    }

    .btn-login{
      background:var(--cafe);
      color:#fff;
      border:none;
      border-radius:10px;
      padding:10px 18px;
      font-weight:700;
      cursor:pointer;
      transition:.2s;
      font-size:16px;
      width:100%;
    }
    .btn-login:hover{
      background:var(--hover);
    }

    .links{
      margin-top:16px;
      font-size:14px;
    }

    .links a{
      color:var(--cafe);
      text-decoration:none;
    }

    .links a:hover{
      text-decoration:underline;
    }
  </style>

  <div class="login-box">
    <!-- Logo centrado -->
    <div class="logo-container">
      <img src="{{ asset('images/logo.png') }}" alt="Logo El Señor del Pan">
    </div>

    <h1>Bienvenido</h1>

    <!-- Mensaje de sesión -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
      @csrf

      <!-- Email -->
      <div>
        <label for="email">Correo electrónico</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
      </div>

      <!-- Password -->
      <div>
        <label for="password">Contraseña</label>
        <input id="password" type="password" name="password" required autocomplete="current-password">
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
      </div>

      <!-- Remember Me -->
      <div class="checkbox">
        <input id="remember_me" type="checkbox" name="remember">
        <label for="remember_me">Recordarme</label>
      </div>

      <!-- Submit -->
      <button type="submit" class="btn-login">Iniciar sesión</button>

      <!-- Links -->
      <div class="links">
        @if (Route::has('password.request'))
          <a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
        @endif
      </div>
    </form>
  </div>
</x-guest-layout>
