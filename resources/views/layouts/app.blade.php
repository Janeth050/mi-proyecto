<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Inventario Panadería')</title>
  <link rel="icon" href="{{ asset('icono.png') }}" type="image/png">
  <style>
    :root{
      --cafe:#8b5e3c;
      --cafe-hover:#70472e;
      --beige:#f9f3e9;
      --texto:#5c3a21;
      --borde:#d9c9b3;
      --rojo:#e73c3c;
      --rojo-hover:#c0392b;
    }
    *{box-sizing:border-box}
    body{
      margin:0;
      font-family:'Segoe UI',sans-serif;
      background:var(--beige);
      color:var(--texto);
    }

    header{
      background:#fff;
      border-bottom:1px solid var(--borde);
      box-shadow:0 4px 10px rgba(0,0,0,.05);
      position:sticky;
      top:0;
      z-index:100;
    }
    .navbar{
      display:flex;
      justify-content:space-between;
      align-items:center;
      padding:10px 24px;
      max-width:1200px;
      margin:0 auto;
      gap:12px;
    }
    .brand{
      display:flex;
      align-items:center;
      gap:10px;
      font-weight:700;
      font-size:18px;
      color:var(--cafe);
      text-decoration:none;
    }
    .brand img{
      height:60px;
      width:auto;
      border-radius:50%;
      display:block;
    }
    nav{
      display:flex;
      align-items:center;
      gap:16px;
      font-weight:600;
    }
    nav a{
      text-decoration:none;
      color:var(--texto);
      padding:6px 10px;
      border-radius:6px;
      transition:all .2s;
    }
    nav a:hover,nav a.active{
      background:var(--cafe);
      color:#fff;
    }
    .user-info{
      display:flex;
      align-items:center;
      gap:10px;
      font-size:14px;
      white-space:nowrap;
    }
    .logout-btn{
      background:var(--rojo);
      color:#fff;
      border:none;
      border-radius:6px;
      padding:6px 10px;
      cursor:pointer;
      font-weight:600;
    }
    .logout-btn:hover{background:var(--rojo-hover)}
    .hamb{
      display:none;
      justify-content:center;
      align-items:center;
      width:38px;
      height:38px;
      background:transparent;
      border:1px solid var(--borde);
      border-radius:8px;
      cursor:pointer;
      padding:0;
    }
    .hamb img{width:24px;height:24px;display:block;}
    .mobile-menu{
      display:none;
      flex-direction:column;
      background:#fff;
      border-top:1px solid var(--borde);
      padding:8px 0;
    }
    .mobile-menu a{
      padding:10px 18px;
      color:var(--texto);
      text-decoration:none;
      border-radius:0;
    }
    .mobile-menu a:hover{background:#f2e8db;}
    main{
      max-width:1200px;
      margin:0 auto;
      padding:24px 20px;
    }
    @media (max-width: 920px){
      nav{display:none;}
      .hamb{display:flex;}
      .mobile-menu.show{display:flex;}
      .user-info span{display:none;}
    }
  </style>
</head>
<body>
  <header>
    <div class="navbar">
      {{-- LOGO --}}
      <a href="{{ route('dashboard') }}" class="brand">
        <img src="{{ asset('images/logo.png') }}" alt="Logo">
        <span>Inventario Panadería</span>
      </a>

      {{-- MENÚ DE ESCRITORIO --}}
      <nav>
        <a href="{{ route('dashboard') }}"      class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
        <a href="{{ route('productos.index') }}" class="{{ request()->is('productos*')   ? 'active' : '' }}">Productos</a>

        {{-- Proveedores solo visible para administradores --}}
        @if(auth()->user() && auth()->user()->role === 'admin')
          <a href="{{ route('proveedors.index') }}" class="{{ request()->is('proveedors*')  ? 'active' : '' }}">Proveedores</a>
        @endif

        <a href="{{ route('movimientos.index') }}" class="{{ request()->is('movimientos*') ? 'active' : '' }}">Movimientos</a>
        <a href="{{ route('kardex.index') }}"     class="{{ request()->is('kardex*')      ? 'active' : '' }}">Kardex</a>

        {{-- Listas solo para administradores --}}
        @if(auth()->user() && auth()->user()->role === 'admin')
          <a href="{{ route('listas.index') }}"     class="{{ request()->is('listas*')      ? 'active' : '' }}">Listas</a>
        @endif

        {{-- Usuarios solo admin --}}
        @if(auth()->user() && auth()->user()->role === 'admin')
          <a href="{{ route('usuarios.index') }}" class="{{ request()->is('usuarios*')    ? 'active' : '' }}">Usuarios</a>
        @endif
      </nav>

      {{-- USUARIO + SALIR + HAMBURGUESA --}}
      <div class="user-info">
        <span>{{ auth()->user()->name ?? '' }} ({{ auth()->user()->role ?? '' }})</span>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="logout-btn">Salir</button>
        </form>
        <button class="hamb" id="hamb" aria-label="Abrir menú">
          <img src="{{ asset('images/icono1.png') }}" alt="Menú">
        </button>
      </div>
    </div>

    {{-- MENÚ MÓVIL --}}
    <div class="mobile-menu" id="mobileMenu">
      <a href="{{ route('dashboard') }}"      class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
      <a href="{{ route('productos.index') }}" class="{{ request()->is('productos*')   ? 'active' : '' }}">Productos</a>

      {{-- Proveedores solo visible para administradores --}}
      @if(auth()->user() && auth()->user()->role === 'admin')
        <a href="{{ route('proveedors.index') }}" class="{{ request()->is('proveedors*')  ? 'active' : '' }}">Proveedores</a>
      @endif

      <a href="{{ route('movimientos.index') }}" class="{{ request()->is('movimientos*') ? 'active' : '' }}">Movimientos</a>
      <a href="{{ route('kardex.index') }}"     class="{{ request()->is('kardex*')      ? 'active' : '' }}">Kardex</a>

      {{-- Listas solo para administradores --}}
      @if(auth()->user() && auth()->user()->role === 'admin')
        <a href="{{ route('listas.index') }}"     class="{{ request()->is('listas*')      ? 'active' : '' }}">Listas</a>
      @endif

      @if(auth()->user() && auth()->user()->role === 'admin')
        <a href="{{ route('usuarios.index') }}" class="{{ request()->is('usuarios*')    ? 'active' : '' }}">Usuarios</a>
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
