@component('mail::message')
# Prueba de smoke de correo

Este mensaje confirma que la configuración SMTP/driver permite renderizar vistas y enviar notificaciones.

Gracias,
{{ config('app.name') }}
@endcomponent