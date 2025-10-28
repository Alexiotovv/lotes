<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Documento')</title>
    @yield('css')
</head>
<body>
    @yield('content')
    @yield('scripts')
</body>
</html>