# MediReserva - Sistema de Gestión de Citas Médicas

<div align="center">

![MediReserva](https://img.shields.io/badge/MediReserva-v1.0.0-blue?style=for-the-badge)
![Laravel](https://img.shields.io/badge/Laravel-12.x-red?style=for-the-badge&logo=laravel)
![Vue.js](https://img.shields.io/badge/Vue.js-3.5-green?style=for-the-badge&logo=vue.js)
![PHP](https://img.shields.io/badge/PHP-8.2+-purple?style=for-the-badge&logo=php)

**Plataforma web moderna para la gestión de citas médicas entre pacientes y profesionales de la salud**

[Características](#-características) • [Tecnologías](#-tecnologías-utilizadas) • [Instalación](#-instalación) • [Documentación](#-documentación)

</div>

---

## 📋 Tabla de Contenidos

- [Introducción](#-introducción)
- [Descripción del Proyecto](#-descripción-del-proyecto)
- [Audiencia Objetivo](#-audiencia-objetivo)
- [Características](#-características)
- [Tecnologías Utilizadas](#-tecnologías-utilizadas)
- [Arquitectura](#-arquitectura)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [Instalación](#-instalación)
- [Configuración](#-configuración)
- [Uso](#-uso)
- [API Endpoints](#-api-endpoints)
- [Componentes Principales](#-componentes-principales)
- [Base de Datos](#-base-de-datos)
- [Seguridad](#-seguridad)
- [Desarrollo](#-desarrollo)
- [Contribución](#-contribución)

---

## 🎯 Introducción

**MediReserva** es una plataforma web integral diseñada para facilitar la gestión de citas médicas entre pacientes y profesionales de la salud. El sistema permite a los pacientes buscar médicos, reservar citas, gestionar su historial médico y calificar servicios, mientras que los médicos pueden administrar su agenda, gestionar pacientes y configurar sus horarios de disponibilidad.

La plataforma está construida con tecnologías modernas que garantizan una experiencia de usuario fluida, escalabilidad y mantenibilidad del código.

---

## 📖 Descripción del Proyecto

MediReserva es una aplicación web full-stack que funciona como intermediario entre pacientes y médicos, proporcionando:

- **Para Pacientes:**
  - Búsqueda avanzada de médicos y especialidades
  - Reserva y gestión de citas médicas
  - Historial completo de citas
  - Sistema de calificaciones y reseñas
  - Gestión de médicos favoritos
  - Configuración de notificaciones personalizadas

- **Para Médicos:**
  - Gestión completa de agenda y horarios
  - Visualización de citas del día
  - Búsqueda de pacientes y su historial
  - Configuración de especialidades
  - Sistema de verificación de perfil
  - Estadísticas y reportes

---

## 👥 Audiencia Objetivo

### Usuarios Primarios

1. **Pacientes**
   - Personas que necesitan agendar citas médicas
   - Usuarios que buscan profesionales de la salud específicos
   - Pacientes que requieren seguimiento de su historial médico

2. **Médicos y Profesionales de la Salud**
   - Médicos que necesitan gestionar su agenda
   - Profesionales que buscan expandir su base de pacientes
   - Clínicas y consultorios que requieren un sistema de gestión

### Usuarios Secundarios

- **Administradores del Sistema**: Gestión de usuarios, verificación de médicos, y administración general

---

## ✨ Características

### 🔐 Autenticación y Autorización

- Registro de usuarios (Pacientes y Médicos)
- Sistema de login seguro con tokens
- Recuperación de contraseña por email
- Roles y permisos basados en usuario
- Protección de rutas con middleware

### 👤 Gestión de Perfiles

- **Perfil de Paciente:**
  - Información personal completa
  - Historial de citas
  - Médicos favoritos
  - Estadísticas de uso

- **Perfil de Médico:**
  - Información profesional
  - Gestión de especialidades
  - Sistema de verificación
  - Estadísticas de práctica

### 📅 Sistema de Citas

- Reserva de citas con validación de disponibilidad
- Gestión de estados (pendiente, confirmada, completada, cancelada)
- Reprogramación de citas
- Cancelación con motivo
- Recordatorios automáticos por email

### 🔍 Búsqueda y Descubrimiento

- Búsqueda de médicos por nombre o especialidad
- Búsqueda de pacientes (para médicos)
- Búsqueda de citas
- Filtros avanzados
- Perfiles públicos de médicos

### ⭐ Sistema de Calificaciones

- Calificación de citas completadas (1-5 estrellas)
- Reseñas escritas
- Visualización de calificaciones en perfiles públicos
- Estadísticas de rating por médico

### 🔔 Notificaciones

- Configuración personalizada de notificaciones
- Notificaciones por email
- Notificaciones por SMS (configurable)
- Notificaciones push (preparado para futuro)

### 📊 Dashboard y Estadísticas

- Dashboard personalizado para pacientes
- Dashboard de gestión para médicos
- Estadísticas en tiempo real
- Visualización de citas del día

---

## 🛠 Tecnologías Utilizadas

### Backend

#### **Laravel 12.x**
- **¿Por qué Laravel?**
  - Framework PHP robusto y maduro
  - ORM Eloquent para interacciones con base de datos intuitivas
  - Sistema de autenticación integrado
  - Migraciones de base de datos versionadas
  - Sistema de colas para tareas asíncronas
  - Validación de datos integrada
  - Ecosistema extenso y comunidad activa

#### **Laravel Sanctum**
- **¿Por qué Sanctum?**
  - Autenticación basada en tokens para APIs
  - Ligero y eficiente
  - Integración nativa con Laravel
  - Soporte para SPA (Single Page Applications)
  - Tokens con expiración configurable

#### **PHP 8.2+**
- **¿Por qué PHP 8.2?**
  - Mejoras significativas de rendimiento
  - Tipado estricto mejorado
  - Nuevas características del lenguaje
  - Compatibilidad con Laravel 12

### Frontend

#### **Vue.js 3.5**
- **¿Por qué Vue.js?**
  - Framework progresivo y fácil de aprender
  - Reactividad eficiente
  - Componentes reutilizables
  - Excelente rendimiento
  - Ecosistema maduro (Vue Router, Pinia)
  - Sintaxis intuitiva y clara

#### **Vue Router 4.x**
- **¿Por qué Vue Router?**
  - Enrutamiento oficial de Vue.js
  - Navegación del lado del cliente
  - Guards para protección de rutas
  - Lazy loading de componentes
  - Integración perfecta con Vue 3

#### **VeeValidate 4.x**
- **¿Por qué VeeValidate?**
  - Validación de formularios declarativa
  - Reglas de validación extensibles
  - Integración con Vue 3 Composition API
  - Mensajes de error personalizables
  - Validación en tiempo real

#### **Axios**
- **¿Por qué Axios?**
  - Cliente HTTP robusto
  - Interceptores para manejo de tokens
  - Manejo de errores centralizado
  - Soporte para promesas
  - Configuración global

### Estilos

#### **Tailwind CSS 4.x**
- **¿Por qué Tailwind CSS?**
  - Framework utility-first
  - Desarrollo rápido sin escribir CSS personalizado
  - Diseño responsive fácil
  - Purge automático de CSS no utilizado
  - Personalización mediante configuración
  - Consistencia visual

### Base de Datos

#### **MySQL/PostgreSQL**
- **¿Por qué SQL?**
  - Relaciones complejas entre entidades
  - Integridad referencial
  - Transacciones ACID
  - Consultas complejas optimizadas
  - Madurez y estabilidad

### Herramientas de Desarrollo

#### **Vite**
- **¿Por qué Vite?**
  - Build tool moderno y rápido
  - Hot Module Replacement (HMR) instantáneo
  - Optimización automática de assets
  - Soporte nativo para ES modules
  - Mejor experiencia de desarrollo

#### **Composer**
- Gestión de dependencias PHP
- Autoloading PSR-4
- Scripts personalizados

#### **NPM**
- Gestión de dependencias JavaScript
- Scripts de build y desarrollo

---

## 🏗 Arquitectura

### Patrón de Arquitectura

MediReserva sigue una arquitectura **MVC (Modelo-Vista-Controlador)** con separación clara entre frontend y backend:

```
┌─────────────────────────────────────────────────────────┐
│                    Frontend (Vue.js)                    │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐            │
│  │ Component│  │  Router  │  │  Store   │            │
│  │    s     │  │          │  │          │            │
│  └──────────┘  └──────────┘  └──────────┘            │
└──────────────────────┬──────────────────────────────────┘
                       │ HTTP/REST API
                       │ (Axios)
┌──────────────────────▼──────────────────────────────────┐
│                 Backend (Laravel)                       │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐            │
│  │Controller│  │  Models  │  │  Routes  │            │
│  │    s     │  │          │  │          │            │
│  └──────────┘  └──────────┘  └──────────┘            │
└──────────────────────┬──────────────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────────────┐
│                  Base de Datos                          │
│              (MySQL/PostgreSQL)                         │
└─────────────────────────────────────────────────────────┘
```

### Flujo de Datos

1. **Usuario interactúa** con componente Vue
2. **Componente hace petición** HTTP mediante Axios
3. **Laravel recibe petición** en ruta API
4. **Middleware valida** autenticación/autorización
5. **Controller procesa** la lógica de negocio
6. **Model accede** a la base de datos
7. **Respuesta JSON** se envía al frontend
8. **Vue actualiza** la interfaz reactivamente

---

## 📁 Estructura del Proyecto

```
consultas/
├── app/
│   ├── Console/Commands/          # Comandos Artisan personalizados
│   ├── Http/
│   │   └── Controllers/
│   │       └── Api/               # Controladores API
│   │           ├── AuthController.php
│   │           ├── CatalogoController.php
│   │           ├── Medico/
│   │           │   ├── CitasController.php
│   │           │   ├── EspecialidadesController.php
│   │           │   └── PacienteController.php
│   │           └── Paciente/
│   │               ├── CitasController.php
│   │               ├── DashboardController.php
│   │               ├── PerfilController.php
│   │               └── RatingController.php
│   ├── Jobs/                      # Tareas en cola
│   ├── Mail/                      # Clases de email
│   ├── Models/                    # Modelos Eloquent
│   │   ├── User.php
│   │   ├── Paciente.php
│   │   ├── Medico.php
│   │   ├── Cita.php
│   │   ├── Especialidad.php
│   │   └── NotificationPreference.php
│   ├── Notifications/             # Notificaciones
│   └── Services/                  # Servicios de negocio
│
├── database/
│   ├── migrations/                # Migraciones de BD
│   └── seeders/                   # Seeders de datos
│
├── resources/
│   ├── js/
│   │   ├── app.js                 # Punto de entrada JS
│   │   ├── App.vue                # Componente raíz
│   │   ├── router/
│   │   │   └── index.js           # Configuración de rutas
│   │   ├── auth/
│   │   │   └── store.js           # Store de autenticación
│   │   ├── services/
│   │   │   └── api.js             # Cliente Axios configurado
│   │   └── ui/
│   │       ├── components/        # Componentes reutilizables
│   │       ├── pages/             # Páginas/Vistas
│   │       ├── forms/             # Formularios
│   │       └── layouts/           # Layouts
│   └── css/                       # Estilos globales
│
├── routes/
│   └── api.php                    # Rutas de API
│
└── public/                        # Archivos públicos
```

---

## 🚀 Instalación

### Requisitos Previos

- **PHP** >= 8.2
- **Composer** >= 2.0
- **Node.js** >= 18.x y NPM
- **MySQL** >= 8.0 o **PostgreSQL** >= 13
- **Git**

### Pasos de Instalación

1. **Clonar el repositorio**
   ```bash
   git clone <repository-url>
   cd CitasMedicas/consultas
   ```

2. **Instalar dependencias PHP**
   ```bash
   composer install
   ```

3. **Instalar dependencias JavaScript**
   ```bash
   npm install
   ```

4. **Configurar variables de entorno**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configurar base de datos en `.env`**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=medireserva
   DB_USERNAME=tu_usuario
   DB_PASSWORD=tu_contraseña
   ```

6. **Ejecutar migraciones**
   ```bash
   php artisan migrate
   ```

7. **Ejecutar seeders (opcional)**
   ```bash
   php artisan db:seed
   ```

8. **Compilar assets**
   ```bash
   npm run build
   ```

---

## ⚙️ Configuración

### Variables de Entorno Importantes

```env
# Aplicación
APP_NAME=MediReserva
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Base de Datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=medireserva
DB_USERNAME=root
DB_PASSWORD=

# Email
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=noreply@medireserva.com
MAIL_FROM_NAME="${APP_NAME}"

# SMS (opcional)
SMS_PROVIDER=twilio
SMS_FROM=+1234567890
```

### Configuración de Autenticación

El sistema utiliza **Laravel Sanctum** para autenticación basada en tokens. Los tokens se almacenan en la tabla `personal_access_tokens` y se envían en el header `Authorization: Bearer {token}`.

---

## 💻 Uso

### Desarrollo

Para iniciar el servidor de desarrollo:

```bash
# Terminal 1: Servidor Laravel
php artisan serve

# Terminal 2: Vite (Hot Reload)
npm run dev
```

O usar el comando combinado:

```bash
composer run dev
```

### Producción

1. **Optimizar aplicación**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

2. **Compilar assets para producción**
   ```bash
   npm run build
   ```

3. **Configurar servidor web** (Nginx/Apache) para apuntar a `public/`

---

## 🔌 API Endpoints

### Autenticación

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `/api/auth/register-paciente` | Registro de paciente |
| POST | `/api/auth/register-medico` | Registro de médico |
| POST | `/api/auth/login` | Inicio de sesión |
| POST | `/api/auth/logout` | Cerrar sesión |
| GET | `/api/auth/me` | Obtener usuario actual |

### Pacientes

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/paciente/dashboard` | Dashboard del paciente |
| GET | `/api/paciente/perfil` | Perfil del paciente |
| PUT | `/api/paciente/perfil` | Actualizar perfil |
| GET | `/api/paciente/citas` | Listar citas |
| GET | `/api/paciente/citas/{id}` | Detalle de cita |
| POST | `/api/paciente/citas` | Crear cita |
| PUT | `/api/paciente/citas/{id}` | Actualizar cita |
| POST | `/api/paciente/citas/{id}/cancelar` | Cancelar cita |
| POST | `/api/paciente/citas/{id}/rating` | Calificar cita |

### Médicos

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/medico/citas` | Listar citas del médico |
| POST | `/api/medico/citas/{id}/confirmar` | Confirmar cita |
| POST | `/api/medico/citas/{id}/completar` | Completar cita |
| POST | `/api/medico/citas/{id}/cancelar` | Cancelar cita |
| GET | `/api/medico/especialidades` | Especialidades del médico |
| POST | `/api/medico/especialidades` | Agregar especialidad |
| DELETE | `/api/medico/especialidades/{id}` | Eliminar especialidad |
| GET | `/api/medico/pacientes/{id}` | Detalle de paciente |
| GET | `/api/medico/search` | Búsqueda de pacientes/citas |

### Catálogo (Público)

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/catalogo/especialidades` | Listar especialidades |
| GET | `/api/catalogo/medicos` | Listar médicos |
| GET | `/api/catalogo/search?q={query}` | Búsqueda general |
| GET | `/api/catalogo/medico/{id}` | Perfil público de médico |

### Notificaciones

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/notificaciones/paciente` | Preferencias del paciente |
| PUT | `/api/notificaciones/paciente` | Actualizar preferencias |
| GET | `/api/notificaciones/medico` | Preferencias del médico |
| PUT | `/api/notificaciones/medico` | Actualizar preferencias |

---

## 🧩 Componentes Principales

### Frontend (Vue.js)

#### Páginas Principales

- **`Login.vue`**: Página de inicio de sesión
- **`Register.vue`**: Página de registro (paciente/médico)
- **`PacienteHome.vue`**: Dashboard del paciente
- **`MedicoHome.vue`**: Dashboard del médico
- **`PacientePerfil.vue`**: Perfil del paciente
- **`MedicoPerfil.vue`**: Perfil del médico
- **`PacienteMisCitas.vue`**: Gestión de citas del paciente
- **`ReservarCita.vue`**: Reserva de citas
- **`MedicoHorarios.vue`**: Configuración de horarios

#### Componentes Reutilizables

- **`BuscarMedicos.vue`**: Búsqueda de médicos (pacientes)
- **`BuscarPacientesCitas.vue`**: Búsqueda de pacientes/citas (médicos)
- **`PacienteDetalleModal.vue`**: Modal de detalle de paciente
- **`RatingModal.vue`**: Modal de calificación
- **`ReprogramarModal.vue`**: Modal de reprogramación

### Backend (Laravel)

#### Controladores Principales

- **`AuthController`**: Autenticación y registro
- **`Paciente/DashboardController`**: Dashboard del paciente
- **`Paciente/CitasController`**: Gestión de citas del paciente
- **`Paciente/PerfilController`**: Perfil del paciente
- **`Medico/CitasController`**: Gestión de citas del médico
- **`Medico/EspecialidadesController`**: Gestión de especialidades
- **`CatalogoController`**: Catálogo público

---

## 🗄 Base de Datos

### Modelos Principales

#### **User**
- Información básica de usuario
- Relación con roles (paciente/médico)
- Autenticación

#### **Paciente**
- Información adicional del paciente
- Relación con citas
- Médicos favoritos

#### **Medico**
- Información profesional
- Estado de verificación
- Relación con especialidades
- Horarios de disponibilidad

#### **Cita**
- Relación médico-paciente
- Fechas y horarios
- Estado de la cita
- Calificaciones y reseñas

#### **Especialidad**
- Especialidades médicas
- Relación muchos-a-muchos con médicos

#### **NotificationPreference**
- Preferencias de notificación por usuario
- Configuración de email, SMS, push

### Relaciones Principales

```
User
├── hasOne → Paciente
├── hasOne → Medico
└── belongsToMany → Roles

Paciente
├── belongsTo → User
├── hasMany → Citas
└── belongsToMany → Medicos (favoritos)

Medico
├── belongsTo → User
├── hasMany → Citas
├── belongsToMany → Especialidades
└── hasMany → Horarios

Cita
├── belongsTo → Medico
├── belongsTo → Paciente
└── belongsTo → Especialidad
```

---

## 🔒 Seguridad

### Medidas Implementadas

1. **Autenticación**
   - Tokens JWT mediante Laravel Sanctum
   - Expiración automática de tokens
   - Protección CSRF

2. **Autorización**
   - Middleware de autenticación
   - Validación de roles
   - Protección de rutas

3. **Validación**
   - Validación de entrada en backend
   - Sanitización de datos
   - Validación en frontend con VeeValidate

4. **Seguridad de Contraseñas**
   - Hashing con bcrypt
   - Validación de fortaleza
   - Recuperación segura por email

5. **Protección de Datos**
   - Preparación de consultas SQL (prevención de SQL injection)
   - Escape de datos en vistas
   - Headers de seguridad

---

## 🧪 Desarrollo

### Comandos Útiles

```bash
# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Ejecutar migraciones
php artisan migrate
php artisan migrate:refresh --seed

# Crear migración
php artisan make:migration nombre_migracion

# Crear controlador
php artisan make:controller Api/NombreController

# Crear modelo
php artisan make:model Nombre

# Ejecutar tests
php artisan test
```

### Estructura de Commits

Se recomienda seguir el formato:

```
tipo(alcance): descripción breve

Descripción detallada (opcional)

Ejemplo:
feat(auth): agregar recuperación de contraseña
fix(citas): corregir validación de fechas pasadas
docs(readme): actualizar documentación de API
```

---

## 🤝 Contribución

Las contribuciones son bienvenidas. Por favor:

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'feat: Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

### Estándares de Código

- Seguir PSR-12 para PHP
- Usar ESLint para JavaScript
- Escribir tests para nuevas funcionalidades
- Documentar código complejo

---

## 📝 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

---

## 👨‍💻 Autor

Desarrollado con ❤️ para facilitar la gestión de citas médicas.

---

## 📞 Soporte

Para soporte, por favor abre un issue en el repositorio o contacta al equipo de desarrollo.

---

## 🗺 Roadmap

### Próximas Características

- [ ] Notificaciones push en tiempo real
- [ ] Integración con calendarios (Google Calendar, Outlook)
- [ ] Video consultas (Telemedicina)
- [ ] Sistema de pagos en línea
- [ ] App móvil (React Native)
- [ ] Dashboard de administración avanzado
- [ ] Reportes y analytics
- [ ] Integración con sistemas de salud externos

---

**Última actualización**: Enero 2025
