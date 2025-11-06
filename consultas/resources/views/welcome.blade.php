<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <style>
                :root {
                    color-scheme: light;
                }

                *,
                *::before,
                *::after {
                    box-sizing: border-box;
                }

                body {
                    margin: 0;
                    font-family: 'Poppins', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif;
                    background: radial-gradient(circle at 15% 20%, rgba(21, 143, 174, 0.2), transparent 55%),
                        radial-gradient(circle at 80% 0%, rgba(94, 76, 209, 0.35), transparent 50%),
                        linear-gradient(135deg, #0d3c4d 0%, #120c3b 100%);
                    color: #f5f8ff;
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 2.5rem 1.5rem 3.5rem;
                }

                a {
                    color: inherit;
                    text-decoration: none;
                }

                .page-shell {
                    width: min(1120px, 100%);
                    display: flex;
                    flex-direction: column;
                    gap: 3.5rem;
                    position: relative;
                }

                .page-shell::before {
                    content: '';
                    position: absolute;
                    inset: -80px;
                    background: linear-gradient(135deg, rgba(28, 241, 205, 0.15), rgba(118, 88, 234, 0.05));
                    border-radius: 48px;
                    filter: blur(60px);
                    z-index: 0;
                }

                header,
                main,
                footer,
                section {
                    position: relative;
                    z-index: 1;
                }

                header.top-nav {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    padding: 1.25rem 1.75rem;
                    border-radius: 22px;
                    background: rgba(12, 25, 48, 0.65);
                    backdrop-filter: blur(24px);
                    border: 1px solid rgba(120, 204, 229, 0.25);
                    box-shadow: 0 24px 60px rgba(7, 10, 40, 0.35);
                }

                .brand {
                    display: flex;
                    align-items: center;
                    gap: 0.75rem;
                    font-weight: 600;
                    font-size: 1.25rem;
                    letter-spacing: 0.01em;
                }

                .brand span {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    width: 42px;
                    height: 42px;
                    border-radius: 14px;
                    background: linear-gradient(140deg, #52f4d6, #67d0ff);
                    color: #041625;
                    font-weight: 700;
                    font-size: 1.15rem;
                    box-shadow: 0 12px 25px rgba(97, 239, 228, 0.25);
                }

                .nav-actions {
                    display: inline-flex;
                    align-items: center;
                    gap: 0.75rem;
                    flex-wrap: wrap;
                }

                .nav-link,
                .nav-button {
                    font-size: 0.95rem;
                    padding: 0.65rem 1.2rem;
                    border-radius: 999px;
                    border: 1px solid transparent;
                    transition: transform 0.2s ease, background 0.2s ease, border 0.2s ease, box-shadow 0.2s ease;
                }

                .nav-link {
                    background: transparent;
                    border-color: rgba(129, 146, 202, 0.35);
                    color: rgba(225, 236, 255, 0.8);
                }

                .nav-button {
                    background: linear-gradient(135deg, #52f4d6, #6fc3ff);
                    color: #051425;
                    font-weight: 600;
                    box-shadow: 0 15px 35px rgba(88, 247, 218, 0.35);
                }

                .nav-link:hover,
                .nav-button:hover {
                    transform: translateY(-1px);
                }

                .nav-link:hover {
                    border-color: rgba(129, 146, 202, 0.6);
                    color: #ffffff;
                    background: rgba(46, 72, 124, 0.3);
                }

                .nav-button:hover {
                    box-shadow: 0 20px 40px rgba(88, 247, 218, 0.45);
                }

                main {
                    display: grid;
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                    gap: 3rem;
                    align-items: center;
                }

                .hero-copy {
                    display: flex;
                    flex-direction: column;
                    gap: 1.75rem;
                }

                .hero-eyebrow {
                    font-size: 0.85rem;
                    letter-spacing: 0.3em;
                    text-transform: uppercase;
                    color: rgba(205, 235, 255, 0.7);
                    display: inline-flex;
                    align-items: center;
                    gap: 0.4rem;
                }

                .hero-eyebrow::before {
                    content: '';
                    width: 40px;
                    height: 2px;
                    border-radius: 999px;
                    background: rgba(86, 245, 214, 0.55);
                }

                h1 {
                    font-size: clamp(2.35rem, 4vw, 3.4rem);
                    line-height: 1.1;
                    margin: 0;
                    font-weight: 600;
                }

                .hero-subtitle {
                    font-size: 1.05rem;
                    line-height: 1.7;
                    color: rgba(225, 236, 255, 0.75);
                }

                .hero-actions {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 1rem;
                }

                .button-primary,
                .button-secondary {
                    padding: 0.9rem 1.8rem;
                    border-radius: 16px;
                    font-size: 1rem;
                    font-weight: 600;
                    border: 1px solid transparent;
                    transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease, border 0.2s ease;
                }

                .button-primary {
                    background: linear-gradient(135deg, #52f4d6, #6fc3ff);
                    color: #051425;
                    box-shadow: 0 22px 50px rgba(88, 247, 218, 0.35);
                }

                .button-secondary {
                    background: rgba(18, 39, 74, 0.55);
                    border-color: rgba(111, 196, 255, 0.35);
                    color: rgba(225, 236, 255, 0.85);
                    box-shadow: 0 15px 35px rgba(7, 20, 56, 0.4);
                }

                .button-primary:hover,
                .button-secondary:hover {
                    transform: translateY(-2px);
                }

                .button-secondary:hover {
                    border-color: rgba(111, 196, 255, 0.6);
                    background: rgba(18, 39, 74, 0.75);
                }

                .hero-stats {
                    display: grid;
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                    gap: 1rem;
                }

                .stat-card {
                    padding: 1.1rem 1.3rem;
                    border-radius: 18px;
                    background: rgba(10, 25, 52, 0.55);
                    border: 1px solid rgba(120, 204, 229, 0.25);
                    display: flex;
                    flex-direction: column;
                    gap: 0.45rem;
                }

                .stat-value {
                    font-size: 1.6rem;
                    font-weight: 600;
                    color: #6ef7e8;
                }

                .stat-label {
                    font-size: 0.85rem;
                    color: rgba(225, 236, 255, 0.68);
                }

                .hero-visual {
                    position: relative;
                    display: grid;
                    gap: 1.5rem;
                }

                .dashboard-card {
                    background: rgba(12, 26, 52, 0.75);
                    border-radius: 28px;
                    padding: 2.2rem;
                    border: 1px solid rgba(107, 215, 255, 0.18);
                    box-shadow: 0 40px 90px rgba(5, 9, 34, 0.55);
                    display: grid;
                    gap: 1.8rem;
                }

                .dashboard-header {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 1rem;
                }

                .status-pill {
                    padding: 0.45rem 0.9rem;
                    border-radius: 999px;
                    background: rgba(82, 244, 214, 0.18);
                    color: #7efce3;
                    font-size: 0.8rem;
                    font-weight: 500;
                    letter-spacing: 0.04em;
                }

                .schedule-list {
                    display: grid;
                    gap: 0.95rem;
                }

                .appointment {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 1rem 1.2rem;
                    border-radius: 18px;
                    background: rgba(9, 20, 44, 0.7);
                    border: 1px solid rgba(110, 247, 232, 0.15);
                }

                .appointment strong {
                    font-size: 1rem;
                    font-weight: 600;
                }

                .appointment span {
                    font-size: 0.9rem;
                    color: rgba(225, 236, 255, 0.7);
                }

                .floating-card {
                    position: absolute;
                    bottom: -55px;
                    right: -30px;
                    background: linear-gradient(135deg, rgba(111, 196, 255, 0.95), rgba(82, 244, 214, 0.9));
                    color: #021328;
                    padding: 1.4rem 1.6rem;
                    border-radius: 24px;
                    box-shadow: 0 32px 80px rgba(18, 38, 70, 0.45);
                    border: 1px solid rgba(255, 255, 255, 0.45);
                    width: min(260px, 70%);
                }

                .floating-card h3 {
                    margin: 0 0 0.6rem;
                    font-size: 1.1rem;
                    font-weight: 600;
                }

                .floating-card p {
                    margin: 0;
                    font-size: 0.92rem;
                    line-height: 1.5;
                }

                .features {
                    display: grid;
                    gap: 2rem;
                    background: rgba(9, 20, 44, 0.55);
                    border-radius: 32px;
                    padding: 2.8rem;
                    border: 1px solid rgba(120, 204, 229, 0.18);
                    box-shadow: inset 0 0 0 1px rgba(82, 244, 214, 0.08), 0 30px 65px rgba(4, 12, 32, 0.55);
                }

                .section-header {
                    display: flex;
                    flex-direction: column;
                    gap: 0.75rem;
                    max-width: 720px;
                }

                .section-header h2 {
                    margin: 0;
                    font-size: clamp(1.7rem, 3vw, 2.35rem);
                    font-weight: 600;
                }

                .section-header p {
                    margin: 0;
                    color: rgba(225, 236, 255, 0.72);
                    line-height: 1.7;
                }

                .features-grid {
                    display: grid;
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                    gap: 1.5rem;
                }

                .feature-card {
                    padding: 1.6rem;
                    border-radius: 22px;
                    background: rgba(6, 21, 46, 0.85);
                    border: 1px solid rgba(111, 196, 255, 0.18);
                    display: grid;
                    gap: 0.9rem;
                    transition: transform 0.25s ease, box-shadow 0.25s ease;
                }

                .feature-card:hover {
                    transform: translateY(-6px);
                    box-shadow: 0 20px 45px rgba(5, 16, 40, 0.5);
                }

                .feature-icon {
                    width: 48px;
                    height: 48px;
                    border-radius: 18px;
                    display: grid;
                    place-items: center;
                    background: rgba(82, 244, 214, 0.18);
                    color: #6ef7e8;
                    font-size: 1.4rem;
                    font-weight: 600;
                }

                .feature-card h3 {
                    margin: 0;
                    font-size: 1.15rem;
                    font-weight: 600;
                }

                .feature-card p {
                    margin: 0;
                    font-size: 0.95rem;
                    color: rgba(225, 236, 255, 0.72);
                    line-height: 1.6;
                }

                .workflow {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 2.5rem;
                    background: rgba(6, 18, 40, 0.8);
                    border-radius: 28px;
                    padding: 2.6rem;
                    border: 1px solid rgba(111, 196, 255, 0.18);
                    box-shadow: 0 24px 60px rgba(3, 10, 28, 0.55);
                }

                .steps {
                    display: grid;
                    gap: 1.5rem;
                }

                .step {
                    display: grid;
                    grid-template-columns: auto 1fr;
                    gap: 1.25rem;
                    align-items: start;
                }

                .step-number {
                    width: 44px;
                    height: 44px;
                    border-radius: 16px;
                    background: rgba(82, 244, 214, 0.2);
                    border: 1px solid rgba(82, 244, 214, 0.4);
                    display: grid;
                    place-items: center;
                    color: #6ef7e8;
                    font-weight: 600;
                    font-size: 1.05rem;
                }

                .step h4 {
                    margin: 0 0 0.35rem;
                    font-size: 1.05rem;
                    font-weight: 600;
                }

                .step p {
                    margin: 0;
                    color: rgba(225, 236, 255, 0.7);
                    line-height: 1.6;
                }

                .support-card {
                    background: linear-gradient(135deg, rgba(111, 196, 255, 0.12), rgba(9, 26, 52, 0.8));
                    border-radius: 24px;
                    padding: 2.4rem;
                    border: 1px solid rgba(111, 196, 255, 0.22);
                    display: grid;
                    gap: 1.2rem;
                }

                .support-card h3 {
                    margin: 0;
                    font-size: 1.4rem;
                    font-weight: 600;
                }

                .support-card p {
                    margin: 0;
                    color: rgba(225, 236, 255, 0.75);
                    line-height: 1.7;
                }

                .support-actions {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 0.8rem;
                }

                .support-actions a {
                    padding: 0.7rem 1.4rem;
                    border-radius: 16px;
                    background: rgba(6, 22, 48, 0.85);
                    border: 1px solid rgba(111, 196, 255, 0.25);
                    color: rgba(225, 236, 255, 0.85);
                    font-weight: 500;
                    transition: background 0.2s ease, transform 0.2s ease, border 0.2s ease;
                }

                .support-actions a:hover {
                    transform: translateY(-2px);
                    background: rgba(6, 22, 48, 1);
                    border-color: rgba(111, 196, 255, 0.45);
                }

                footer {
                    text-align: center;
                    font-size: 0.85rem;
                    color: rgba(200, 217, 255, 0.55);
                }

                @media (max-width: 1024px) {
                    body {
                        padding: 2rem 1.2rem 3rem;
                    }

                    header.top-nav {
                        flex-direction: column;
                        gap: 1.2rem;
                        align-items: flex-start;
                    }

                    main {
                        grid-template-columns: 1fr;
                    }

                    .hero-visual {
                        order: -1;
                    }

                    .floating-card {
                        position: relative;
                        bottom: auto;
                        right: auto;
                        width: 100%;
                        margin-top: -0.5rem;
                    }

                    .features-grid {
                        grid-template-columns: repeat(2, minmax(0, 1fr));
                    }

                    .workflow {
                        grid-template-columns: 1fr;
                    }
                }

                @media (max-width: 720px) {
                    .features-grid {
                        grid-template-columns: 1fr;
                    }

                    header.top-nav {
                        padding: 1rem 1.25rem;
                    }

                    .hero-stats {
                        grid-template-columns: repeat(2, minmax(0, 1fr));
                    }

                    .page-shell {
                        gap: 2.75rem;
                    }
                }

                @media (max-width: 520px) {
                    body {
                        padding: 1.75rem 1rem 2.5rem;
                    }

                    .hero-actions {
                        flex-direction: column;
                        align-items: stretch;
                    }

                    .hero-stats {
                        grid-template-columns: 1fr;
                    }
                }
            </style>
        @endif
    </head>
    <body>
        <div class="page-shell">
            <header class="top-nav">
                <div class="brand">
                    <span>MR</span>
                    MediReserva
                </div>
                @if (Route::has('login'))
                    <div class="nav-actions">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="nav-button">Ir al panel</a>
                        @else
                            <a href="{{ route('login') }}" class="nav-link">Iniciar sesión</a>

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="nav-button">Crear cuenta</a>
                            @endif
                        @endauth
                    </div>
                @endif
            </header>

            <main>
                <div class="hero-copy">
                    <span class="hero-eyebrow">Plataforma médica digital</span>
                    <h1>Reserva tus consultas con estilo, claridad y total confianza</h1>
                    <p class="hero-subtitle">
                        MediReserva simplifica la agenda de tu consultorio con recordatorios automáticos, historial clínico accesible y una experiencia moderna que inspira tranquilidad en tus pacientes.
                    </p>
                    <div class="hero-actions">

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="button-primary">Crear cuenta gratuita</a>
                        @endif
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="button-secondary">Ver demo interactiva</a>
                        @endif
                    </div>
                    <div class="hero-stats">
                        <div class="stat-card">
                            <span class="stat-value">+250</span>
                            <span class="stat-label">Consultorios felices gestionando su agenda</span>
                        </div>
                        <div class="stat-card">
                            <span class="stat-value">98%</span>
                            <span class="stat-label">Pacientes que confían en los recordatorios inteligentes</span>
                        </div>
                        <div class="stat-card">
                            <span class="stat-value">24/7</span>
                            <span class="stat-label">Disponibilidad para tus pacientes donde estén</span>
                        </div>
                    </div>
                </div>
                <div class="hero-visual">
                    <div class="dashboard-card">
                        <div class="dashboard-header">
                            <div>
                                <h3 style="margin: 0; font-size: 1.4rem; font-weight: 600;">Agenda de hoy</h3>
                                <p style="margin: 0; font-size: 0.9rem; color: rgba(225, 236, 255, 0.65);">Viernes, 12 de abril</p>
                            </div>
                            <span class="status-pill">Sin cancelaciones</span>
                        </div>
                        <div class="schedule-list">
                            <div class="appointment">
                                <div>
                                    <strong>09:00 · Dra. Ríos</strong>
                                    <span>Consulta de control pediátrico</span>
                                </div>
                                <span>Confirmada</span>
                            </div>
                            <div class="appointment">
                                <div>
                                    <strong>11:30 · Dr. Méndez</strong>
                                    <span>Sesión de fisioterapia</span>
                                </div>
                                <span>Recordatorio enviado</span>
                            </div>
                            <div class="appointment">
                                <div>
                                    <strong>15:15 · Dra. Castro</strong>
                                    <span>Evaluación preventiva</span>
                                </div>
                                <span>Lista de espera</span>
                            </div>
                        </div>
                    </div>
                    <div class="floating-card">
                        <h3>Confianza al instante</h3>
                        <p>
                            Notificaciones claras, historial organizado y una experiencia pensada para pacientes modernos.
                        </p>
                    </div>
                </div>
            </main>
        <section class="features">
                <div class="section-header">
                    <h2>Un ecosistema elegante que convierte la gestión médica en una experiencia memorable</h2>
                    <p>
                        Todo el flujo de trabajo se alinea en un mismo lugar: visualiza tu agenda, controla los tiempos, entiende el pulso de tu consulta y ofrece el mejor cuidado posible.
                    </p>
                </div>
                <div class="features-grid">
                    <article class="feature-card">
                        <div class="feature-icon">⌛</div>
                        <h3>Agenda con inteligencia</h3>
                        <p>Bloques dinámicos que se ajustan automáticamente a la duración real de las consultas y evitan traslapes.</p>
                    </article>
                    <article class="feature-card">
                        <div class="feature-icon">💬</div>
                        <h3>Recordatorios humanos</h3>
                        <p>Mensajes personalizados con tu tono de voz, enviados por correo y WhatsApp para reducir ausencias.</p>
                    </article>
                    <article class="feature-card">
                        <div class="feature-icon">📊</div>
                        <h3>Insights accionables</h3>
                        <p>Paneles elegantes que muestran métricas clave para que tomes decisiones basadas en datos reales.</p>
                    </article>
                </div>
            </section>

            <section class="workflow">
                <div>
                    <div class="section-header">
                        <h2>Tu jornada médica, paso a paso</h2>
                        <p>
                            Acompañamos cada interacción para que tus pacientes vivan una experiencia premium desde la reserva hasta el seguimiento post consulta.
                        </p>
                    </div>
                </div>
                <div class="steps">
                    <div class="step">
                        <div class="step-number">01</div>
                        <div>
                            <h4>Reserva sin fricción</h4>
                            <p>Formulario intuitivo con disponibilidad en tiempo real que se adapta a cualquier dispositivo.</p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-number">02</div>
                        <div>
                            <h4>Seguimiento inteligente</h4>
                            <p>Recordatorios automáticos con enlaces directos y confirmaciones en un solo clic.</p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-number">03</div>
                        <div>
                            <h4>Fideliza con detalles</h4>
                            <p>Notas clínicas, recomendaciones y próximos pasos listos para compartir tras cada visita.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="support-card">
                <h3>¿Necesitas ayuda? Estamos contigo</h3>
                <p>
                    Nuestro equipo acompaña tu implementación para que cada profesional del consultorio aproveche al máximo MediReserva.
                </p>
                <div class="support-actions">
                    <a href="mailto:soporte@medireserva.com">soporte@medireserva.com</a>
                    <a href="mailto:alianzas@medireserva.com">alianzas@medireserva.com</a>
                </div>
            </section>

            <footer>
                © {{ date('Y') }} MediReserva. Diseñado para profesionales de la salud que valoran la excelencia.
            </footer>
        </div>

        
    </body>
</html>
