<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>MediReserva - Tu Salud Reimaginada</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @vite(['resources/js/app.js'])
  {{-- ... --}}
  @vite(['resources/css/theme.css','resources/js/app.js'])
</head>
<body style="margin:0;background:#0a0118;color:#fff">
  <div id="app"></div>
</body>
</html>
