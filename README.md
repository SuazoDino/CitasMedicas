# MediReserva · Plataforma de citas médicas

MediReserva es una solución full‑stack para gestionar citas entre pacientes y médicos. El backend en Laravel expone una API autenticada con Sanctum, mientras que el frontend en Vue 3 ofrece una experiencia moderna para reservar horarios, revisar agendas y administrar el ciclo completo de una consulta médica.【F:routes/api.php†L1-L42】【F:resources/js/router/index.js†L1-L90】

## Funcionalidades destacadas

### Autenticación y gestión de cuentas
- Registro diferenciado para pacientes y médicos, guardando atributos clínicos básicos y asignando el rol correspondiente de forma automática.【F:app/Http/Controllers/Api/AuthController.php†L16-L88】
- Inicio y cierre de sesión basados en tokens personales de Laravel Sanctum, con almacenamiento local en el navegador y guardas de ruta para proteger secciones privadas.【F:app/Http/Controllers/Api/AuthController.php†L90-L118】【F:resources/js/router/index.js†L52-L111】
- Recuperación de contraseña mediante envío de enlaces y restablecimiento validado por token temporal.【F:app/Http/Controllers/Api/PasswordResetController.php†L16-L74】

### Experiencia del paciente
- Panel de control que lista próximas citas, métricas rápidas y recomendaciones, optimizado para pacientes autenticados.【F:resources/js/ui/pages/PacienteHome.vue†L1-L164】
- Flujo guiado para reservar citas filtrando por especialidad, seleccionando médico disponible y eligiendo un horario libre en tiempo real.【F:resources/js/ui/pages/ReservarCita.vue†L1-L156】
- Consulta de citas vigentes a través de la API y conversión de horarios al formato legible en el cliente.【F:app/Http/Controllers/Api/PacienteCitasController.php†L13-L66】

### Experiencia del médico
- Tablero diario con resumen de citas, estado de verificación profesional y acciones rápidas para confirmar, completar o cancelar atenciones.【F:resources/js/ui/pages/MedicoHome.vue†L1-L210】
- Endpoints protegidos que devuelven la agenda del día y permiten modificar el estado clínico de cada cita respetando la autoría del médico autenticado.【F:app/Http/Controllers/Api/MedicoCitasController.php†L13-L64】

### Catálogo y disponibilidad
- Catálogo público de especialidades y médicos asociados, con filtros por especialidad para acelerar la búsqueda.【F:app/Http/Controllers/Api/CatalogoController.php†L11-L34】
- Generación de slots disponibles por médico considerando configuraciones horarias, duración mínima de turno y citas ya reservadas o pasadas.【F:app/Http/Controllers/Api/MedicoSlotsController.php†L13-L106】

## Arquitectura y stack tecnológico
- **Backend:** Laravel 12 (PHP 8.2) con Sanctum para autenticación por tokens y colas / sesiones respaldadas en base de datos.【F:composer.json†L8-L47】
- **Frontend:** Vue 3 + Vue Router con Vite 7; Axios como cliente HTTP y Tailwind 4 en modo Vite para utilidades de estilo.【F:package.json†L1-L22】
- **Base de datos:** configurada en modo SQLite por defecto para desarrollo, con opción de adaptar a MySQL/PostgreSQL cambiando el `.env`.【F:.env.example†L1-L43】

### Organización del código
- `app/Http/Controllers/Api`: controladores REST para autenticación, catálogos, citas y slots disponibles.【F:app/Http/Controllers/Api/AuthController.php†L1-L118】
- `app/Models`: modelos Eloquent y asociaciones entre usuarios, roles, pacientes, médicos y citas.【F:app/Models/Cita.php†L1-L30】
- `routes/api.php`: definición centralizada de endpoints públicos y protegidos con middleware `auth:sanctum`.【F:routes/api.php†L1-L42】
- `resources/js/ui`: componentes Vue para formularios, paneles y flujo de reserva con estilos dedicados.【F:resources/js/ui/pages/ReservarCita.vue†L1-L156】

## Modelo de datos
Las migraciones incluyen entidades para gestionar usuarios, roles y el dominio médico:
- `users`, `roles` y `user_role` para autenticación y autorización basada en roles.【F:database/migrations/2025_10_27_190400_create_users_table.php†L1-L36】【F:database/migrations/2025_10_27_190410_create_roles_table.php†L1-L27】【F:database/migrations/2025_10_27_190420_create_user_role_table.php†L1-L28】
- `pacientes` y `medicos` almacenan información clínica y de verificación profesional; los médicos mantienen estado de validación y límites de agenda provisoria.【F:database/migrations/2025_10_27_200247_create_pacientes_table.php†L1-L37】【F:database/migrations/2025_10_27_200248_create_medicos_table.php†L1-L40】
- `especialidades`, tabla pivote `medico_especialidad` y `medico_horarios` definen el catálogo y disponibilidad semanal de cada profesional.【F:database/migrations/2025_10_30_015728_create_especialidades_table.php†L1-L33】【F:database/migrations/2025_10_30_015948_create_medico_especialidad_table.php†L1-L33】【F:database/migrations/2025_10_30_020057_create_medico_horarios_table.php†L1-L40】
- `citas` registra cada reserva con estado, motivo, autores y restricciones de unicidad por horario/médico.【F:database/migrations/2025_10_30_020155_create_citas_table.php†L1-L34】
- `password_reset_tokens` y `personal_access_tokens` respaldan seguridad y recuperación de cuentas.【F:database/migrations/2025_10_31_170219_create_password_reset_tokens_table.php.php†L1-L29】【F:database/migrations/2025_10_27_190743_create_personal_access_tokens_table.php†L1-L38】

## Endpoints principales
| Método | Ruta | Descripción |
| --- | --- | --- |
| POST | `/api/auth/register/paciente` | Alta de paciente con datos personales opcionales.【F:app/Http/Controllers/Api/AuthController.php†L16-L55】 |
| POST | `/api/auth/register/medico` | Alta de médico con información de licencia y verificación provisoria.【F:app/Http/Controllers/Api/AuthController.php†L57-L88】 |
| POST | `/api/auth/login` | Autenticación y emisión de token personal.【F:app/Http/Controllers/Api/AuthController.php†L90-L118】 |
| POST | `/api/auth/forgot-password` | Envío de enlace para restablecer contraseña.【F:routes/api.php†L11-L17】 |
| POST | `/api/auth/reset-password` | Restablecimiento de contraseña mediante token válido.【F:app/Http/Controllers/Api/PasswordResetController.php†L44-L74】 |
| GET | `/api/public/especialidades` | Listado de especialidades médicas disponibles.【F:app/Http/Controllers/Api/CatalogoController.php†L11-L18】 |
| GET | `/api/public/medicos` | Catálogo de médicos, filtrable por especialidad.【F:app/Http/Controllers/Api/CatalogoController.php†L20-L34】 |
| GET | `/api/public/medicos/{id}/slots` | Slots disponibles/ocupados de un médico para un rango de fechas.【F:app/Http/Controllers/Api/MedicoSlotsController.php†L16-L106】 |
| GET | `/api/paciente/citas/proximas` | Citas futuras del paciente autenticado con formato amigable.【F:app/Http/Controllers/Api/PacienteCitasController.php†L13-L44】 |
| POST | `/api/paciente/citas` | Reserva de cita validando horarios y conflictos de agenda.【F:app/Http/Controllers/Api/PacienteCitasController.php†L46-L86】 |
| POST | `/api/paciente/citas/{id}/cancelar` | Cancelación de cita por parte del paciente dueño de la reserva.【F:app/Http/Controllers/Api/PacienteCitasController.php†L88-L107】 |
| GET | `/api/medico/citas` | Agenda del día para el médico autenticado con filtro por fecha.【F:app/Http/Controllers/Api/MedicoCitasController.php†L13-L33】 |
| POST | `/api/medico/citas/{id}/confirmar` | Confirmación de cita pendiente por parte del médico titular.【F:app/Http/Controllers/Api/MedicoCitasController.php†L35-L44】 |
| POST | `/api/medico/citas/{id}/cancelar` | Cancelación de cita, registrando motivo y responsable.【F:app/Http/Controllers/Api/MedicoCitasController.php†L52-L64】 |
| POST | `/api/medico/citas/{id}/completar` | Marcar una atención como completada una vez realizada.【F:app/Http/Controllers/Api/MedicoCitasController.php†L46-L51】 |

## Puesta en marcha

### Prerrequisitos
- PHP 8.2+, Composer y extensiones típicas de Laravel (pdo, mbstring, tokenizer, openssl).
- Node.js 18+ y npm para compilar los assets de Vite.【F:package.json†L1-L22】
- SQLite (por defecto) o un servidor MySQL/PostgreSQL si prefieres otra base de datos.【F:.env.example†L1-L43】

### Instalación rápida
```bash
cp .env.example .env        # Define variables y claves de la app
composer install            # Dependencias PHP
php artisan key:generate    # Clave de aplicación
php artisan migrate         # Crea las tablas descritas arriba
npm install                 # Dependencias del frontend
npm run dev                 # Compila assets en modo desarrollo
php artisan serve           # Levanta la API en http://localhost:8000
```

> 💡 También puedes ejecutar `composer run setup` para automatizar los pasos anteriores en un entorno limpio.【F:composer.json†L21-L33】

### Entorno de desarrollo integrado
Ejecuta `composer run dev` para iniciar servidor HTTP, listener de colas, tail de logs y Vite en paralelo (usa `concurrently`).【F:composer.json†L35-L43】

### Variables de entorno relevantes
- `APP_URL`, `FRONTEND_URL`: ajusta las URLs base si trabajas con dominios distintos.
- `DB_CONNECTION` y credenciales para cambiar de SQLite a MySQL/PostgreSQL.
- `MAIL_MAILER` si deseas enviar correos reales en recuperación de contraseña.【F:.env.example†L1-L57】

## Pruebas y mantenimiento
- Ejecuta el suite de tests con `php artisan test` (alias `composer test`).【F:composer.json†L44-L47】
- Usa `php artisan migrate:fresh --seed` para resetear el estado de la base de datos cuando cambies el esquema.
- `npm run build` genera assets optimizados para producción.【F:package.json†L5-L16】

## Próximos pasos sugeridos
- Habilitar gestión de horarios desde la UI del médico (actualmente los slots se cargan desde base de datos).【F:resources/js/ui/pages/MedicoHome.vue†L67-L89】【F:database/migrations/2025_10_30_020057_create_medico_horarios_table.php†L1-L40】
- Completar lógica de favoritos/historial en el panel del paciente (ahora son datos de ejemplo).【F:resources/js/ui/pages/PacienteHome.vue†L101-L134】
- Configurar un proveedor de correo real para que el flujo de recuperación de contraseña envíe emails en producción.【F:app/Http/Controllers/Api/PasswordResetController.php†L16-L41】
