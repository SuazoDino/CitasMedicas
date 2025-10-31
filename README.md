# MediReserva · Plataforma de citas médicas

Una solución full-stack para coordinar pacientes y médicos mediante reservas en línea, confirmaciones en tiempo real y gestión integral de agendas clínicas. El backend en Laravel expone una API autenticada con Sanctum y el frontend en Vue 3 ofrece una experiencia moderna y responsiva para cada rol de usuario.

---

## Tabla de contenido
1. [Visión general](#visión-general)
2. [Stack y arquitectura](#stack-y-arquitectura)
3. [Funcionalidades clave](#funcionalidades-clave)
4. [Estructura del proyecto](#estructura-del-proyecto)
5. [Flujos destacados](#flujos-destacados)
6. [Puesta en marcha](#puesta-en-marcha)
7. [Variables de entorno](#variables-de-entorno)
8. [API principal](#api-principal)
9. [Modelo de datos](#modelo-de-datos)
10. [Pruebas y mantenimiento](#pruebas-y-mantenimiento)
11. [Hoja de ruta](#hoja-de-ruta)

---

## Visión general
MediReserva permite que pacientes encuentren especialistas disponibles, reserven horarios y gestionen su historial de consultas, mientras que los profesionales validan, confirman o reprograman su agenda diaria. La plataforma prioriza la seguridad de los datos médicos, la trazabilidad de cada cita y la comunicación ágil entre las partes.【F:routes/api.php†L1-L42】【F:resources/js/router/index.js†L1-L111】

## Stack y arquitectura
| Capa | Tecnología | Descripción |
| --- | --- | --- |
| Backend | Laravel 12 · PHP 8.2 | API REST protegida con Laravel Sanctum, migraciones Eloquent y colas opcionales en base de datos.【F:composer.json†L8-L47】 |
| Frontend | Vue 3 · Vite 7 · Tailwind | SPA que consume la API mediante Axios, con rutas protegidas y componentes reutilizables para cada rol.【F:package.json†L1-L22】【F:resources/js/router/index.js†L1-L111】 |
| Base de datos | SQLite (dev) / MySQL / PostgreSQL | Definida mediante migraciones; configurable desde `.env`.【F:.env.example†L1-L43】 |
| Autenticación | Laravel Sanctum | Tokens personales almacenados en el navegador con guards dedicados para pacientes y médicos.【F:app/Http/Controllers/Api/AuthController.php†L16-L118】 |

> 💡 El repositorio incluye scripts para levantar simultáneamente API, frontend y workers durante el desarrollo (`composer run dev`).【F:composer.json†L35-L43】

## Funcionalidades clave
### Pacientes
- Registro con datos clínicos básicos y preferencia de especialidad.【F:app/Http/Controllers/Api/AuthController.php†L16-L55】
- Buscador de médicos por especialidad con agenda disponible en tiempo real.【F:resources/js/ui/pages/ReservarCita.vue†L1-L156】
- Panel personal con próximas citas, recordatorios y métricas rápidas.【F:resources/js/ui/pages/PacienteHome.vue†L1-L164】

### Médicos
- Alta con validación de licencia y verificación profesional progresiva.【F:app/Http/Controllers/Api/AuthController.php†L57-L88】
- Tablero diario para confirmar, completar o cancelar citas desde la web.【F:resources/js/ui/pages/MedicoHome.vue†L1-L210】
- Endpoints protegidos para gestionar agenda según el médico autenticado.【F:app/Http/Controllers/Api/MedicoCitasController.php†L13-L64】

### Funciones compartidas
- Recuperación de contraseña con correos firmados por token temporal.【F:app/Http/Controllers/Api/PasswordResetController.php†L16-L74】
- Middleware de Sanctum que restringe rutas a usuarios autenticados según su rol.【F:routes/api.php†L1-L42】
- Catálogo público de especialidades y médicos para facilitar descubrimiento.【F:app/Http/Controllers/Api/CatalogoController.php†L11-L34】

## Estructura del proyecto
```
consultas/
├── app/
│   ├── Http/Controllers/Api/    # Autenticación, catálogos, agenda, slots
│   └── Models/                  # Entidades Eloquent (Cita, Paciente, Médico, etc.)
├── database/migrations/         # Esquema de tablas y pivotes
├── resources/js/
│   ├── router/                  # Rutas protegidas por guardas
│   └── ui/pages/                # Vistas de pacientes y médicos
├── routes/api.php               # Definición de endpoints REST
└── composer.json & package.json # Scripts útiles de backend y frontend
```

## Flujos destacados
### Autenticación paso a paso
1. Registro según rol (`/api/auth/register/paciente` o `/api/auth/register/medico`).【F:app/Http/Controllers/Api/AuthController.php†L16-L88】
2. Inicio de sesión con email y contraseña → emisión de token Sanctum.【F:app/Http/Controllers/Api/AuthController.php†L90-L118】
3. El frontend guarda el token y protege rutas mediante guards en Vue Router.【F:resources/js/router/index.js†L52-L111】
4. Cierre de sesión invalida el token activo desde el backend.【F:app/Http/Controllers/Api/AuthController.php†L100-L118】

### Reserva de cita
1. Paciente consulta catálogo público de especialidades y médicos.【F:app/Http/Controllers/Api/CatalogoController.php†L11-L34】
2. Solicita slots disponibles del médico elegido (`/api/public/medicos/{id}/slots`).【F:app/Http/Controllers/Api/MedicoSlotsController.php†L16-L106】
3. Crea la reserva (`/api/paciente/citas`) validando conflictos de horario.【F:app/Http/Controllers/Api/PacienteCitasController.php†L46-L86】
4. Médico confirma, cancela o completa la cita desde su tablero.【F:app/Http/Controllers/Api/MedicoCitasController.php†L35-L64】

## Puesta en marcha
### Requisitos
- PHP 8.2+, Composer y extensiones habituales de Laravel (pdo, mbstring, tokenizer, openssl).
- Node.js 18+ con npm para compilar assets de Vite.【F:package.json†L1-L22】
- SQLite instalado (predeterminado) o credenciales para MySQL/PostgreSQL.【F:.env.example†L1-L43】

### Configuración inicial (una sola vez)
```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
npm install
```

### Servidores en desarrollo
```bash
# API de Laravel (http://localhost:8000)
php artisan serve

# Frontend de Vite con recarga en caliente (http://localhost:5173)
npm run dev
```

> 🛠️ Para levantar API, colas y Vite de un solo golpe puedes usar `composer run dev`, que ejecuta `php artisan serve`, `php artisan queue:work` y `npm run dev` en paralelo.【F:composer.json†L35-L43】

### Scripts útiles
- `composer run setup`: automatiza instalación, generación de clave y migraciones desde cero.【F:composer.json†L21-L33】
- `composer test`: ejecuta la suite de pruebas (`php artisan test`).【F:composer.json†L44-L47】
- `npm run build`: compila assets para producción.【F:package.json†L5-L16】

## Variables de entorno
Ajusta estos valores en `.env` antes de desplegar:
- `APP_URL`, `FRONTEND_URL`: URLs base para API y SPA.
- `DB_CONNECTION`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`: conexión a la base de datos.
- `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`: envío de correos reales para recuperación de contraseñas.【F:.env.example†L1-L57】

## API principal
| Método | Ruta | Descripción |
| --- | --- | --- |
| POST | `/api/auth/register/paciente` | Alta de paciente con datos personales opcionales.【F:app/Http/Controllers/Api/AuthController.php†L16-L55】 |
| POST | `/api/auth/register/medico` | Alta de médico con información de licencia y verificación provisoria.【F:app/Http/Controllers/Api/AuthController.php†L57-L88】 |
| POST | `/api/auth/login` | Autenticación y emisión de token personal.【F:app/Http/Controllers/Api/AuthController.php†L90-L118】 |
| POST | `/api/auth/forgot-password` | Envío de enlace de restablecimiento vía email.【F:routes/api.php†L11-L17】 |
| POST | `/api/auth/reset-password` | Restablecimiento validado por token temporal.【F:app/Http/Controllers/Api/PasswordResetController.php†L44-L74】 |
| GET | `/api/public/especialidades` | Listado de especialidades médicas disponibles.【F:app/Http/Controllers/Api/CatalogoController.php†L11-L18】 |
| GET | `/api/public/medicos` | Catálogo de médicos filtrable por especialidad.【F:app/Http/Controllers/Api/CatalogoController.php†L20-L34】 |
| GET | `/api/public/medicos/{id}/slots` | Horarios disponibles/ocupados de un médico para un rango de fechas.【F:app/Http/Controllers/Api/MedicoSlotsController.php†L16-L106】 |
| GET | `/api/paciente/citas/proximas` | Próximas citas del paciente autenticado.【F:app/Http/Controllers/Api/PacienteCitasController.php†L13-L44】 |
| POST | `/api/paciente/citas` | Reserva de cita validando conflictos de agenda.【F:app/Http/Controllers/Api/PacienteCitasController.php†L46-L86】 |
| POST | `/api/paciente/citas/{id}/cancelar` | Cancelación realizada por el paciente dueño de la reserva.【F:app/Http/Controllers/Api/PacienteCitasController.php†L88-L107】 |
| GET | `/api/medico/citas` | Agenda diaria filtrable por fecha para el médico autenticado.【F:app/Http/Controllers/Api/MedicoCitasController.php†L13-L33】 |
| POST | `/api/medico/citas/{id}/confirmar` | Confirma una cita pendiente del médico logueado.【F:app/Http/Controllers/Api/MedicoCitasController.php†L35-L44】 |
| POST | `/api/medico/citas/{id}/cancelar` | Cancela cita registrando motivo y responsable.【F:app/Http/Controllers/Api/MedicoCitasController.php†L52-L64】 |
| POST | `/api/medico/citas/{id}/completar` | Marca la atención como completada tras realizarse.【F:app/Http/Controllers/Api/MedicoCitasController.php†L46-L51】 |

## Modelo de datos
```
Usuarios ─┬─< Pacientes
          └─< Médicos ─┬─< MédicoEspecialidad >─ Especialidades
                         └─< MédicoHorarios

Pacientes ─┬─< Citas >─ Médicos
Citas incluyen estado (pendiente, confirmada, completada, cancelada), motivo y marcas de auditoría.
```
- Migraciones organizadas por fecha definen llaves foráneas, restricciones únicas y tablas pivote.【F:database/migrations/2025_10_27_190400_create_users_table.php†L1-L36】【F:database/migrations/2025_10_30_020155_create_citas_table.php†L1-L34】
- Tokens personales y reinicios de contraseña mantienen la seguridad de las cuentas.【F:database/migrations/2025_10_27_190743_create_personal_access_tokens_table.php†L1-L38】【F:database/migrations/2025_10_31_170219_create_password_reset_tokens_table.php.php†L1-L29】

## Pruebas y mantenimiento
- Ejecuta la suite con `php artisan test` para validar controladores y casos de uso críticos.【F:composer.json†L44-L47】
- Usa `php artisan migrate:fresh --seed` para restablecer la base y cargar datos de prueba.
- `npm run build` genera assets optimizados antes del despliegue.【F:package.json†L5-L16】
- Supervisa logs con `php artisan tail` y tareas en segundo plano con `php artisan queue:work`.

## Hoja de ruta
- Editor visual para disponibilidades del médico desde la interfaz web.【F:resources/js/ui/pages/MedicoHome.vue†L67-L134】
- Historial completo y favoritos reales para pacientes (actualmente placeholders).【F:resources/js/ui/pages/PacienteHome.vue†L101-L164】
- Integración con proveedor de correo transaccional (Mailgun, SES) para producción.【F:app/Http/Controllers/Api/PasswordResetController.php†L16-L41】
- Notificaciones push o SMS para recordatorios de citas.
