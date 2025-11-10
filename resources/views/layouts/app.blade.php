<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gestión de Lotización</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  @yield('css')
  <style>
    body { min-height: 100vh; display: flex; flex-direction: column; }
    .wrapper { display: flex; flex: 1; overflow-y: hidden; }
    .sidebar { min-width: 250px; max-width: 250px; background-color: #f8f9fa; border-right: 1px solid #dee2e6; transition: all 0.3s; overflow-y: auto; }
    .sidebar .nav-link { font-weight: 500; color: #333; }
    .sidebar .nav-link:hover { background-color: #e9ecef; border-radius: 8px; }
    .content { flex-grow: 1; padding: 20px; overflow-x: auto; }
    @media (max-width: 768px) {
      .sidebar { position: absolute; left: -250px; top: 56px; height: calc(100% - 56px); z-index: 1000; }
      .sidebar.show { left: 0; }
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
  <div class="container-fluid">
    <button class="btn btn-outline-secondary d-md-none" id="toggleSidebar">☰</button>
    <a class="navbar-brand ms-2" href="{{ route('dashboard') }}">Gestión de Lotes</a>
    <div class="d-flex ms-auto align-items-center">
      <span class="me-3">👤 {{ Auth::user()->name }} ({{ Auth::user()->role }})</span>
      <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button class="btn btn-light btn-sm">🚪 Cerrar sesión</button>
      </form>
    </div>
  </div>
</nav>

<div class="wrapper">
  <div class="sidebar bg-light" id="sidebarMenu">
    <nav class="nav flex-column p-3">
      <a class="nav-link" href="{{ route('dashboard') }}">📊 Dashboard</a>
      

      @if(auth()->user()->is_admin())
        <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#menuFacturacion">
          🧾 Facturación <span>▾</span>
        </a>
        <div class="collapse ps-3" id="menuFacturacion">
          <a href="{{ route('clientes.index') }}" class="nav-link">🗸 Clientes</a>
          <a href="{{ route('reservas.index') }}" class="nav-link">🗸 Reservas</a>
          <a href="{{ route('ventas.index') }}" class="nav-link">🗸 Ventas</a>
          <a href="{{ route('creditos.index') }}" class="nav-link">🗸 Créditos</a>
          <a href="{{ route('pagos.index') }}" class="nav-link">🗸 Cobros</a>
          <a href="{{ route('cotizaciones.index') }}" class="nav-link">🗸 Cotizaciones</a>          
        </div>

        <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#menuLogistica">
          📦 Logística <span>▾</span>
        </a>
        <div class="collapse ps-3" id="menuLogistica">
          <a href="{{ route('lotes.indexView') }}" class="nav-link">🗸 Lotes</a>
          <a href="{{ route('compras.index') }}" class="nav-link">🗸 Compras</a>
          <a href="#" class="nav-link">🗸 Proveedores</a>
        
        </div>
        <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#menuMapas">
          🗺 Mapas <span>▾</span>
        </a>
        <div class="collapse ps-3" id="menuMapas">
          <a class="nav-link" href="{{ route('mapa.index') }}">🗸 Mapa de Lotes</a>
          <a class="nav-link" href="{{ route('lote.create') }}">🗸 Reg. Ráp de Lotes</a>
          <a class="nav-link" href="{{ route('mapa.ver.lotes') }}">🗸 Vista de Lotes</a>
          
          <a class="nav-link" href="{{ route('map.edit') }}">🗸 Editar Imagen en Mapa</a>

        </div>
        <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#menuConfiguracion">
          ⚙️ Configuraciones <span>▾</span>
        </a>
        <div class="collapse ps-3" id="menuConfiguracion">
          <a href="{{ route('admin.users.index') }}" class="nav-link">🗸 Gestión Usuarios</a>
          <a href="{{ route('metodopagos.index') }}" class="nav-link">🗸 Método de Pagos</a>
          <a href="{{ route('estado_lotes.index') }}" class="nav-link">🗸 Estados de Lotes</a>
          <a href="{{ route('tesoreria.conceptos.index') }}" class="nav-link">🗸 Conceptos</a>
          <a href="{{ route('tesoreria.cajas.index') }}" class="nav-link">🗸 Cajas</a>
          <a href="{{ route('tasas.index') }}" class="nav-link">🗸 Tasas</a>
          <a href="{{ route('empresa.edit') }}" class="nav-link">🗸 Empresa</a>
          <a href="{{ route('configuracion.edit') }}" class="nav-link">🗸 Ajustes</a>

        </div>

        <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#menuTesoreria">
          💰 Tesorería <span>▾</span>
        </a>
        <div class="collapse ps-3" id="menuTesoreria">
          <a class="nav-link" href="{{ route('tesoreria.index') }}">🗸 Movimientos</a>
          
        </div>


        <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#menuReportes">
          📊 Reportes <span>▾</span>
        </a>
        <div class="collapse ps-3" id="menuReportes">          
          <a href="{{ route('reportes.ventas') }}" class="nav-link active">📊 R. Ventas</a>
          {{-- <a href="#" class="nav-link active">📊 R. Ventas</a> --}}
          <a href="#" class="nav-link">🛒 R. Compras</a>
          <a href="#" class="nav-link">📈 R. Financiero</a>
          <a href="#" class="nav-link">🏘️ R. Lotes</a>
          <a href="#" class="nav-link">👥 R. Clientes</a>
          <a href="#" class="nav-link">🏭 R. Proveedores</a>
        </div>

      @elseif(auth()->user()->role === 'vendedor')
        <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#menuMapas">
          🗺 Mapas <span>▾</span>
        </a>
        <div class="collapse ps-3" id="menuMapas">
          <a class="nav-link"  href="{{ route('mapa.ver.lotes') }}">🗺 Vista de Lotes</a> 
        </div>
        
        <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#menuFacturacion">
          🧾 Facturación <span>▾</span>
        </a>
        <div class="collapse ps-3" id="menuFacturacion">
          <a href="{{ route('reservas.index') }}" class="nav-link">🗸 Reservas</a>
          <a href="{{ route('clientes.index') }}" class="nav-link">🗸 Clientes</a>
          <a href="{{ route('pagos.index') }}" class="nav-link">🗸 Cobros</a>
          <a class="nav-link" href="{{ route('clientes.create') }}">➕ Registrar Cliente</a>
          <a class="nav-link" href="{{ route('creditos.index') }}"> 👥 Créditos Cliente</a>
          <a class="nav-link" href="{{ route('cotizaciones.index') }}">🧾 Mis Cotizaciones</a>
          <a class="nav-link" href="{{ route('ventas.index') }}">💰 Mis Ventas</a>
        </div>
      @endif
    </nav>
  </div>

  <div class="content">
    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show">
        {!! session('success') !!}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif
    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show">
        {!! session('error') !!}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    @if($errors->any())
      <div class="alert alert-warning alert-dismissible fade show">
        <ul class="mb-0">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    @yield('content')
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>    
@yield('scripts')

<script>
  const toggleSidebar = document.getElementById('toggleSidebar');
  const sidebar = document.getElementById('sidebarMenu');
  toggleSidebar?.addEventListener('click', () => sidebar.classList.toggle('show'));
</script>
</body>
</html>