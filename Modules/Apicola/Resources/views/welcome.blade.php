<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>Unidad Apícola SENA - Centro de Formación Agroindustrial La Angostura</title>
    <meta name="description" content="Formación, investigación y desarrollo para una apicultura sostenible. Unidad Apícola del SENA - Centro de Formación Agroindustrial La Angostura.">
    <meta name="keywords" content="apicultura, SENA, abejas, miel, formación, apiarios, sostenibilidad">
    <meta name="author" content="SENA - Unidad Apícola">
    <link rel="icon" type="image/png" href="{{ asset('modules/apicola/img/logo.png') }}">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    {{-- Custom CSS --}}
    <link rel="stylesheet" href="{{ asset('apicola-assets/assets/css/welcome.css') }}">

    <link rel="stylesheet" href="{{ asset('modules/apicola/css/welcome-interactive.css') }}">
</head>
<body class="apicola-landing">

    {{-- ==================== NAVBAR ==================== --}}
    <nav class="ap-navbar" id="apNavbar">
        <a href="{{ route('apicola.welcome') }}" class="ap-navbar-brand">
            {{-- SENA Logo SVG --}}
            <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="50" cy="50" r="48" fill="#1a5c2a" stroke="#ffffff" stroke-width="2"/>
                <text x="50" y="42" text-anchor="middle" fill="#ffffff" font-size="16" font-weight="800" font-family="Inter, sans-serif">SENA</text>
                <path d="M30 55 Q50 70 70 55" stroke="#f9a825" stroke-width="3" fill="none" stroke-linecap="round"/>
                <circle cx="50" cy="65" r="3" fill="#f9a825"/>
            </svg>
            <span class="brand-text">
                Unidad Apícola
                <span class="brand-icon">🐝</span>
            </span>
        </a>

        <ul class="ap-nav-links" id="apNavLinks">
            <li><a href="#" class="active">Inicio</a></li>
            <li><a href="#">Nosotros</a></li>
            <li><a href="#">Formación</a></li>
            <li><a href="#apFeatures">Apiario</a></li>
            <li><a href="#">Seguimiento de Miel</a></li>
        </ul>

        <div class="ap-nav-actions">
            @auth
                @if(Auth::user()->roles->contains('slug', 'apicola.aprendiz') && !Auth::user()->roles->contains('slug', 'apicola.admin') && !Auth::user()->hasSuperAdmin())
                    <a href="{{ route('apicola.aprendiz.dashboard') }}" class="ap-btn-login">
                        <i class="fas fa-graduation-cap"></i> Panel Aprendiz
                    </a>
                @else
                    <a href="{{ route('apicola.admin.dashboard') }}" class="ap-btn-login">
                        <i class="fas fa-th-large"></i> Panel Admin
                    </a>
                @endif
            @else
                <a href="{{ route('login', ['redirect' => route('apicola.auth.redirect')]) }}" class="ap-btn-login">
                    <i class="fas fa-sign-in-alt"></i> Iniciar sesión
                </a>
            @endauth

            <div class="ap-lang-selector">
                <i class="fas fa-globe"></i>
                ES
                <i class="fas fa-chevron-down" style="font-size: 0.65rem;"></i>
            </div>
        </div>

        <button class="ap-menu-toggle" id="apMenuToggle" aria-label="Abrir menú">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </nav>

    {{-- ==================== HERO SECTION ==================== --}}
    <section class="ap-hero" id="apHero">
        {{-- Background image --}}
        <div class="ap-hero-bg">
            <img src="{{ asset('apicola-assets/assets/img/hero-apicultor.jpg') }}" alt="Apicultor trabajando en colmena">
        </div>
        <div class="ap-hero-overlay"></div>

        <div class="ap-hero-content">
            {{-- Left: Text content --}}
            <div class="ap-hero-text">
                <div class="ap-hero-badge">
                    <span class="badge-dot"></span>
                    Centro de Formación Agroindustrial La Angostura
                </div>

                <h1 class="ap-hero-title">
                    <span>Unidad Apícola</span>
                    <span>SENA</span>
                </h1>

                <p class="ap-hero-subtitle">
                    <strong>Formación, investigación</strong> y <strong>desarrollo</strong> para<br>
                    una <span class="highlight">apicultura sostenible</span>
                </p>

                <p class="ap-hero-description">
                    En la Unidad Apícola del SENA formamos aprendices y fortaleceremos
                    el conocimiento en el manejo de abejas, la producción de miel y el cuidado
                    del medio ambiente, impulsando el desarrollo del sector apícola en la región.
                </p>

                <div class="ap-hero-buttons">
                    @auth
                        @if(Auth::user()->roles->contains('slug', 'apicola.aprendiz') && !Auth::user()->roles->contains('slug', 'apicola.admin') && !Auth::user()->hasSuperAdmin())
                            <a href="{{ route('apicola.aprendiz.dashboard') }}" class="ap-btn-primary">
                                🐝 Explorar Módulos
                                <span class="btn-icon"><i class="fas fa-arrow-right"></i></span>
                            </a>
                        @else
                            <a href="{{ route('apicola.admin.dashboard') }}" class="ap-btn-primary">
                                🐝 Explorar Módulos
                                <span class="btn-icon"><i class="fas fa-arrow-right"></i></span>
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login', ['redirect' => route('apicola.auth.redirect')]) }}" class="ap-btn-primary">
                            🐝 Explorar Módulos
                            <span class="btn-icon"><i class="fas fa-arrow-right"></i></span>
                        </a>
                    @endauth
                </div>
            </div>

            {{-- Right: Visual --}}
            <div class="ap-hero-visual">
                {{-- Rotating stamp --}}
                <div class="ap-stamp">
                    <div class="ap-stamp-inner">
                        <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                <path id="circlePath" d="M 100, 100 m -75, 0 a 75,75 0 1,1 150,0 a 75,75 0 1,1 -150,0"/>
                            </defs>
                            <circle cx="100" cy="100" r="90" fill="none" stroke="rgba(249,168,37,0.6)" stroke-width="2" stroke-dasharray="5,5"/>
                            <text fill="#f9a825" font-size="14" font-weight="700" font-family="Inter, sans-serif">
                                <textPath href="#circlePath" startOffset="0%">
                                    ✦ Abejas que generan futuro ✦ Abejas que generan futuro
                                </textPath>
                            </text>
                            <circle cx="100" cy="100" r="6" fill="#f9a825"/>
                        </svg>
                    </div>
                </div>

                {{-- Diamond image --}}
                <div class="ap-diamond-frame">
                    <img src="{{ asset('apicola-assets/assets/img/abejas-panal.jpg') }}" alt="Abejas en panal de miel">
                </div>

                {{-- Hex decorations --}}
                <div class="ap-hex-decoration ap-hex-1">
                    <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                        <polygon points="50,3 95,25 95,75 50,97 5,75 5,25" fill="rgba(249,168,37,0.3)" stroke="rgba(249,168,37,0.5)" stroke-width="2"/>
                    </svg>
                </div>
                <div class="ap-hex-decoration ap-hex-2">
                    <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                        <polygon points="50,3 95,25 95,75 50,97 5,75 5,25" fill="rgba(249,168,37,0.25)" stroke="rgba(249,168,37,0.4)" stroke-width="2"/>
                    </svg>
                </div>
                <div class="ap-hex-decoration ap-hex-3">
                    <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                        <polygon points="50,3 95,25 95,75 50,97 5,75 5,25" fill="rgba(249,168,37,0.2)" stroke="rgba(249,168,37,0.35)" stroke-width="2"/>
                    </svg>
                </div>
            </div>
        </div>
    </section>

    {{-- ==================== FEATURES SECTION ==================== --}}
    <section class="ap-features" id="apFeatures">
        {{-- Interactive Hint --}}
        <div class="ap-features-interactive-hint">
            <span class="ap-hint-pill">
                <i class="fas fa-hand-pointer"></i> Haz clic en cualquiera de las áreas para conocer qué es y su importancia
            </span>
        </div>

        <div class="ap-features-grid">
            {{-- Feature 1: Apiario --}}
            <div class="ap-feature-card" onclick="selectFeature('apiarios')" data-feature="apiarios" title="Clic para ver información del Apiario SENA">
                <div class="ap-feature-icon">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <h3>Apiario</h3>
                <p>Manejo y control técnico de 20 colmenas a cargo del apicultor Sergio Barrera.</p>
            </div>

            {{-- Feature 2: Producción de Miel --}}
            <div class="ap-feature-card" onclick="selectFeature('miel')" data-feature="miel" title="Clic para ver información de Producción de Miel">
                <div class="ap-feature-icon">
                    <i class="fas fa-flask"></i>
                </div>
                <h3>Producción de Miel</h3>
                <p>Seguimiento, cosecha y trazabilidad del producto.</p>
            </div>

            {{-- Feature 3: Colmenas --}}
            <div class="ap-feature-card" onclick="selectFeature('colmenas')" data-feature="colmenas" title="Clic para ver información de Colmenas">
                <div class="ap-feature-icon">
                    <i class="fas fa-hive"></i>
                </div>
                <h3>Colmenas</h3>
                <p>Registro, control y seguimiento de las 20 colmenas activas.</p>
            </div>

            {{-- Feature 4: Calendario floral --}}
            <div class="ap-feature-card" onclick="selectFeature('floral')" data-feature="floral" title="Clic para ver información de Calendario floral">
                <div class="ap-feature-icon">
                    <i class="fas fa-seedling"></i>
                </div>
                <h3>Calendario floral</h3>
                <p>Registro de floraciones y disponibilidad de alimento.</p>
            </div>

            {{-- Feature 5: Inventario --}}
            <div class="ap-feature-card" onclick="selectFeature('inventario')" data-feature="inventario" title="Clic para ver información de Inventario">
                <div class="ap-feature-icon">
                    <i class="fas fa-box"></i>
                </div>
                <h3>Inventario</h3>
                <p>Registro, control y seguimiento de los productos apícolas.</p>
            </div>

            {{-- Stamp CTA --}}
            <div class="ap-features-stamp">
                <div class="ap-stamp-text">
                    La apicultura<br>
                    también es<br>
                    <span class="stamp-highlight">educación</span>
                </div>
            </div>
        </div>

        {{-- ==================== DETALLE EDUCATIVO INTERACTIVO ==================== --}}
        <div class="ap-feature-detail-wrapper" id="apFeatureDetailWrapper">
            <div class="ap-detail-box">
                <!-- Botón cerrar -->
                <button class="ap-detail-close-btn" onclick="closeFeatureDetail()" title="Cerrar detalle">
                    <i class="fas fa-times"></i>
                </button>

                <!-- Etiquetas superiores -->
                <div class="ap-detail-top-tags">
                    <span class="ap-badge-pedagogico">
                        <i class="fas fa-certificate"></i> Formación Apícola SENA
                    </span>
                    <span class="ap-topic-badge" id="apDetailBadge">
                        <i class="fas fa-info-circle"></i> Módulo Pedagógico
                    </span>
                </div>

                <!-- Contenido Principal en 2 Columnas -->
                <div class="ap-detail-grid">
                    <!-- Columna Izquierda: Introducción e Importancia -->
                    <div class="ap-detail-col-left">
                        <div class="ap-detail-header-flex">
                            <div class="ap-detail-big-icon" id="apDetailIconBox">
                                <i class="fas fa-map-marked-alt" id="apDetailBigIcon"></i>
                            </div>
                            <div class="ap-detail-titles">
                                <h2 id="apDetailTitle">¿Qué es el Apiario SENA?</h2>
                                <h4 id="apDetailSubtitle">1 apiario tecnificado que alberga más de 20 colmenas productivas</h4>
                            </div>
                        </div>

                        <p class="ap-detail-lead" id="apDetailLead">
                            Un apiario (o colmenar) es el espacio geográfico debidamente seleccionado y acondicionado donde se ubica el conjunto de colmenas para la cría, manejo y producción de abejas melíferas (<em>Apis mellifera</em>). En el Centro La Angostura disponemos de <strong>1 único apiario centralizado con más de 20 colmenas tecnificadas</strong>.
                        </p>

                        <!-- Tarjeta Destacada: ¿Por qué es importante? -->
                        <div class="ap-detail-importance-card">
                            <div class="ap-importance-tit">
                                <i class="fas fa-star" style="color: #f59e0b;"></i>
                                <span>¿Por qué es fundamental en la apicultura?</span>
                            </div>
                            <p class="ap-importance-text" id="apDetailImportance">
                                Un apiario bien planificado garantiza que las abejas trabajen sin estrés térmico, previene el pillaje entre colmenas, optimiza las rutas de vuelo para la recolección de néctar y propóleos, y resguarda la seguridad de la comunidad al respetar distancias mínimas reglamentarias (> 200m).
                            </p>
                        </div>
                    </div>

                    <!-- Columna Derecha: Puntos Clave y Enfoque SENA -->
                    <div class="ap-detail-col-right">
                        <div class="ap-right-heading">
                            <i class="fas fa-clipboard-check" style="color: #2e7d32;"></i>
                            <span>Aspectos Clave y Buenas Prácticas</span>
                        </div>

                        <!-- Lista de puntos inyectados por JS -->
                        <div class="ap-points-list" id="apPointsList">
                            <!-- Inyectado dinámicamente -->
                        </div>

                        <!-- Nota del Centro La Angostura -->
                        <div class="ap-sena-badge-foot">
                            <i class="fas fa-graduation-cap"></i>
                            <div>
                                <strong>Enfoque SENA La Angostura:</strong>
                                <span id="apDetailSenaNote">En el Centro La Angostura contamos con 1 apiario principal con más de 20 colmenas activas, donde los aprendices realizan prácticas reales de georreferenciación y manejo productivo.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ==================== SCRIPTS ==================== --}}
    <script src="{{ asset('modules/apicola/js/welcome.js') }}"></script>
</body>
</html>