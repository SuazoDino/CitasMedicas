<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>mediReserva — Tu Salud en Buenas Manos</title>
  <meta name="description" content="Agenda tus turnos en mediReserva. Sistema ágil, rápido y seguro.">
  <link rel="icon" type="image/x-icon" href="/favicon.ico" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
  <div id="app"></div>
</body>
</html>
