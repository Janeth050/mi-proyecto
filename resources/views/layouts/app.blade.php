<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Inventario Panadería')</title>
  <link rel="icon" href="{{ asset('icono.png') }}" type="image/png">
  <style>
    :root{
      --cafe:#8b5e3c; --cafe-hover:#70472e;
      --beige:#f9f3e9; --texto:#5c3a21; --borde:#d9c9b3;
      --rojo:#e73c3c; --rojo-hover:#c0392b;
    }
    *{box-sizing:border-box}
    body{margin:0;font-family:'Segoe UI',system-ui,Arial,sans-serif;background:var(--beige);color:var(--texto)}

    header{background:#fff;border-bottom:1px solid var(--borde);box-shadow:0 4px 10px rgba(0,0,0,.05);position:sticky;top:0;z-index:100}
    .navbar{display:flex;justify-content:space-between;align-items:center;gap:12px;max-width:1200px;margin:0 auto;padding:10px 24px}

    .brand{display:flex;align-items:center;gap:10px;font-weight:700;font-size:18px;color:var(--cafe);text-decoration:none}
    .brand img{height:60px;width:auto;border-radius:50%;display:block}

    nav{display:flex;align-items:center;gap:16px;font-weight:600}
    nav a{color:var(--texto);text-decoration:none;padding:6px 10px;border-radius:6px;transition:.2s}
    nav a:hover, nav a.active{background:var(--cafe);color:#fff}

    /* --------- User dropdown (sin JS) --------- */
    .userbox{display:flex;align-items:center;gap:10px}
    .logout-btn{background:var(--rojo);color:#fff;border:none;border-radius:6px;padding:6px 10px;font-weight:600;cursor:pointer}
    .logout-btn:hover{background:var(--rojo-hover)}

    details.user-dd{position:relative}
    details.user-dd > summary{
      list-style:none; cursor:pointer; user-select:none;
      padding:6px 10px; border:1px solid var(--borde); border-radius:10px;
      display:flex; align-items:center; gap:8px; font-weight:600; color:var(--texto);
    }
    details.user-dd[open] > summary{background:#fff7ee;border-color:#e7d6bf}
    details.user-dd summary::-webkit-details-marker{display:none}

    .dd-menu{
      position:absolute; right:0; top:calc(100% + 8px);
      min-width:200px; background:#fff; border:1px solid var(--borde);
      border-radius:12px; box-shadow:0 18px 40px rgba(0,0,0,.12); padding:8px;
    }
    .dd-link{display:block; text-decoration:none; color:var(--texto);
      padding:10px 12px; border-radius:8px; font-weight:600}
    .dd-link:hover{background:#f2e8db}
    .dd-sep{height:1px; background:var(--borde); margin:6px 0}

    /* --------- Menú móvil --------- */
    .hamb{display:none; width:38px; height:38px; background:transparent; border:1px solid var(--borde); border-radius:8px; padding:0}
    .hamb img{width:24px;height:24px;display:block}
    .mobile-menu{display:none; flex-direction:column; background:#fff; border-top:1px solid var(--borde); padding:8px 0}
    .mobile-menu a{padding:10px 18px; color:var(--texto); text-decoration:none}
    .mobile-menu a:hover{background:#f2e8db}

    main{max-width:1200px;margin:0 auto;padding:24px 20px}

    @media (max-width: 920px){
      nav{display:none}
      .hamb{display:block}
      .mobile-menu.show{display:flex}
      /* en móvil: el dropdown es menos necesario, el link de Ajustes va en el menú móvil */
      details.user-dd{display:none}
    }
  </style>
</head>
<body>
  @php
    $user = auth()->user();
    $ES_ADMIN = $user && (($user->is_admin ?? false) || strtolower($user->role ?? $user->rol ?? '') === 'admin');
  @endphp

  <header>
    <div class="navbar">
      <!-- LOGO -->
      <a href="{{ route('dashboard') }}" class="brand">
        <img src="{{ asset('images/logo.png') }}" alt="Logo">
        <span>Inventario Panadería</span>
      </a>

      <!-- MENÚ ESCRITORIO -->
      <nav>
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
        <a href="{{ route('productos.index') }}" class="{{ request()->is('productos*') ? 'active' : '' }}">Productos</a>

        @if($ES_ADMIN)
          <a href="{{ route('proveedors.index') }}" class="{{ request()->is('proveedors*') ? 'active' : '' }}">Proveedores</a>
        @endif

        <a href="{{ route('movimientos.index') }}" class="{{ request()->is('movimientos*') ? 'active' : '' }}">Movimientos</a>
        <a href="{{ route('kardex.index') }}" class="{{ request()->is('kardex*') ? 'active' : '' }}">Kardex</a>

        @if($ES_ADMIN)
          <a href="{{ route('listas.index') }}" class="{{ request()->is('listas*') ? 'active' : '' }}">Listas</a>
          <a href="{{ route('usuarios.index') }}" class="{{ request()->is('usuarios*') ? 'active' : '' }}">Usuarios</a>
        @endif
      </nav>

      <!-- USUARIO / DROPDOWN / HAMB -->
      <div class="userbox">
        <!-- Dropdown solo escritorio -->
        <details class="user-dd">
          <summary>
            {{ $user->name ?? '' }} ({{ $user->role ?? '' }})
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="m6 9 6 6 6-6"/></svg>
          </summary>
          <div class="dd-menu">
            @if($ES_ADMIN)
              <a class="dd-link" href="{{ route('settings.notifications') }}">Ajustes de notificaciones</a>
              <div class="dd-sep"></div>
            @endif
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="dd-link" style="width:100%;text-align:left;background:none;border:none;cursor:pointer">Cerrar sesión</button>
            </form>
          </div>
        </details>

        <!-- Botón salir (por si quieres mantenerlo visible en escritorio) -->
        <form method="POST" action="{{ route('logout') }}" class="hide-mobile">
          @csrf
          <button type="submit" class="logout-btn">Salir</button>
        </form>

        <!-- Hamburguesa para móvil -->
        <button class="hamb" id="hamb" aria-label="Abrir menú">
          <img src="{{ asset('images/icono1.png') }}" alt="Menú">
        </button>
      </div>
    </div>

    <!-- MENÚ MÓVIL -->
    <div class="mobile-menu" id="mobileMenu">
      <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
      <a href="{{ route('productos.index') }}" class="{{ request()->is('productos*') ? 'active' : '' }}">Productos</a>

      @if($ES_ADMIN)
        <a href="{{ route('proveedors.index') }}" class="{{ request()->is('proveedors*') ? 'active' : '' }}">Proveedores</a>
      @endif

      <a href="{{ route('movimientos.index') }}" class="{{ request()->is('movimientos*') ? 'active' : '' }}">Movimientos</a>
      <a href="{{ route('kardex.index') }}" class="{{ request()->is('kardex*') ? 'active' : '' }}">Kardex</a>

      @if($ES_ADMIN)
        <a href="{{ route('listas.index') }}" class="{{ request()->is('listas*') ? 'active' : '' }}">Listas</a>
        <a href="{{ route('usuarios.index') }}" class="{{ request()->is('usuarios*') ? 'active' : '' }}">Usuarios</a>
        <!-- En móvil sí mostramos Ajustes como link normal -->
        <a href="{{ route('settings.notifications') }}" class="{{ request()->routeIs('settings.notifications*') ? 'active' : '' }}">Ajustes</a>
      @endif

      <form method="POST" action="{{ route('logout') }}" style="padding:8px 16px;">
        @csrf
        <button type="submit" class="logout-btn" style="width:100%">Cerrar sesión</button>
      </form>
    </div>
  </header>

  <main>
    @yield('content')
  </main>

  <script>
    // Solo para el menú móvil (el dropdown del usuario es puro HTML/CSS)
    const hamb = document.getElementById('hamb');
    const menu = document.getElementById('mobileMenu');
    hamb?.addEventListener('click', () => {
      menu.classList.toggle('show');
      hamb.setAttribute('aria-label', menu.classList.contains('show') ? 'Cerrar menú' : 'Abrir menú');
    });
    menu?.querySelectorAll('a, button').forEach(el => {
      el.addEventListener('click', () => menu.classList.remove('show'));
    });
  </script>
</body>
</html>

