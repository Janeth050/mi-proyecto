<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>@yield('title', 'Inventario')</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  {{-- (Opcional) Fuentes --}}
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

  <style>
    :root{
      --cafe:#8b5e3c; --cafe-osc:#70472e; --beige:#f9f3e9; --texto:#5c3a21; --borde:#d9c9b3;
      --ok:#2ecc71; --warn:#f1c40f; --bad:#e74c3c;
    }
    *{box-sizing:border-box}
    html,body{margin:0;padding:0;font-family:'Inter',system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:var(--beige);color:var(--texto)}
    a{color:inherit;text-decoration:none}
    button{font-family:inherit}

    /* NAVBAR */
    .navbar-wrap{position:sticky;top:0;z-index:50;background:#fff;border-bottom:1px solid var(--borde)}
    .navbar{max-width:1200px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;padding:10px 16px;gap:12px}
    .brand{display:flex;align-items:center;gap:10px}
    .brand img{height:38px;width:auto;display:block}
    .brand .title{font-weight:700;color:var(--cafe);letter-spacing:.3px}

    .navlinks{display:flex;align-items:center;gap:10px}
    .navlink{padding:8px 12px;border-radius:8px}
    .navlink:hover{background:#f2e8db}
    .navlink.active{background:var(--cafe);color:#fff}

    .right{display:flex;align-items:center;gap:10px}
    .logout-btn{background:var(--bad);color:#fff;border:none;border-radius:8px;padding:8px 12px;cursor:pointer}
    .logout-btn:hover{background:#c0392b}

    /* HAMBURGER */
    .hamb{display:none;background:transparent;border:1px solid var(--borde);border-radius:8px;padding:8px}
    .hamb span{display:block;width:22px;height:2px;background:#69482f;margin:4px 0}

    /* MOBILE MENU */
    .mobile-menu{display:none;border-top:1px solid var(--borde);background:#fff}
    .mobile-menu a, .mobile-menu form{display:block}
    .mobile-menu .navlink{display:block;padding:12px 16px;border-radius:0}
    .mobile-menu .logout-btn{margin:10px 16px;width:calc(100% - 32px)}

    /* LAYOUT CONTENT */
    .container{max-width:1200px;margin:16px auto;padding:0 16px}
    .card{background:#fff;border:1px solid var(--borde);border-radius:12px;box-shadow:0 4px 14px rgba(0,0,0,.06);padding:16px}

    /* FLASH */
    .flash{background:#d4edda;color:#155724;border:1px solid #c3e6cb;padding:10px;border-radius:8px;margin-bottom:12px}
    .flash.error{background:#f8d7da;color:#721c24;border-color:#f5c6cb}

    /* RESPONSIVE */
    @media (max-width: 900px){
      .navlinks{display:none}
      .hamb{display:inline-flex}
    }
  </style>
  @stack('head')
</head>
<body>

  <header class="navbar-wrap">
    <div class="navbar">
      {{-- Marca / Logo --}}
      <a href="{{ route('dashboard') }}" class="brand">
        {{-- Cambia el src por la ruta de tu logo --}}
        <img src="{{ asset('images/logo.png') }}" alt="Logo">
        <span class="title">Inventario Panadería</span>
      </a>

      {{-- Links desktop --}}
      <nav class="navlinks">
        <a class="navlink {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
        <a class="navlink {{ request()->routeIs('productos.*') ? 'active' : '' }}" href="{{ route('productos.index') }}">Productos</a>
        <a class="navlink {{ request()->routeIs('proveedors.*') ? 'active' : '' }}" href="{{ route('proveedors.index') }}">Proveedores</a>
        <a class="navlink {{ request()->routeIs('movimientos.*') ? 'active' : '' }}" href="{{ route('movimientos.index') }}">Movimientos</a>
        <a class="navlink {{ request()->routeIs('kardex.*') ? 'active' : '' }}" href="{{ route('kardex.index') }}">Kardex</a>
        <a class="navlink {{ request()->routeIs('listas.*') ? 'active' : '' }}" href="{{ route('listas.index') }}">Listas</a>
      </nav>

      {{-- Usuario / Logout --}}
      <div class="right">
        @auth
          <div style="font-size:14px;color:#7a6b5f;white-space:nowrap;max-width:200px;overflow:hidden;text-overflow:ellipsis;">
            {{ auth()->user()->name }} ({{ auth()->user()->role }})
          </div>
          <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="logout-btn" type="submit">Salir</button>
          </form>
        @endauth

        {{-- Botón hamburguesa (móvil) --}}
        <button class="hamb" id="hamb" aria-label="Abrir menú">
          <span></span><span></span><span></span>
        </button>
      </div>
    </div>

    {{-- Menú móvil --}}
    <div class="mobile-menu" id="mobileMenu">
      <a class="navlink {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
      <a class="navlink {{ request()->routeIs('productos.*') ? 'active' : '' }}" href="{{ route('productos.index') }}">Productos</a>
      <a class="navlink {{ request()->routeIs('proveedors.*') ? 'active' : '' }}" href="{{ route('proveedors.index') }}">Proveedores</a>
      <a class="navlink {{ request()->routeIs('movimientos.*') ? 'active' : '' }}" href="{{ route('movimientos.index') }}">Movimientos</a>
      <a class="navlink {{ request()->routeIs('kardex.*') ? 'active' : '' }}" href="{{ route('kardex.index') }}">Kardex</a>
      <a class="navlink {{ request()->routeIs('listas.*') ? 'active' : '' }}" href="{{ route('listas.index') }}">Listas</a>
      @auth
        <form action="{{ route('logout') }}" method="POST">
          @csrf
          <button class="logout-btn" type="submit">Cerrar sesión</button>
        </form>
      @endauth
    </div>
  </header>

  <main class="container">
    {{-- Mensajes flash globales --}}
    @if(session('success')) <div class="flash">{{ session('success') }}</div> @endif
    @if(session('error'))   <div class="flash error">{{ session('error') }}</div> @endif

    @yield('content')
  </main>

  @stack('scripts')

  <script>
    // Toggle del menú móvil
    const hamb = document.getElementById('hamb');
    const mobileMenu = document.getElementById('mobileMenu');
    hamb?.addEventListener('click', () => {
      const visible = mobileMenu.style.display === 'block';
      mobileMenu.style.display = visible ? 'none' : 'block';
      hamb.setAttribute('aria-label', visible ? 'Abrir menú' : 'Cerrar menú');
    });

    // Cerrar el menú móvil al navegar (accesibilidad UX)
    mobileMenu?.querySelectorAll('a, button').forEach(el=>{
      el.addEventListener('click', ()=>{ mobileMenu.style.display='none' });
    });
  </script>
</body>
</html>
