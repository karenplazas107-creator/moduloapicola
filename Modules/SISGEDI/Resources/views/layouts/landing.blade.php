<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SISGEDI — Sistema Integral de Gestión</title>

    <link rel="icon" type="image/png" href="{{ asset('general/assets/img/cefaempresa.png') }}">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link href="{{ asset('libs/Fontawesome6/css/fontawesome.css') }}" rel="stylesheet">
    <link href="{{ asset('libs/Fontawesome6/css/solid.css') }}" rel="stylesheet">
    <link href="{{ asset('libs/Fontawesome6/css/brands.css') }}" rel="stylesheet">
    <link href="{{ asset('libs/Fontawesome6/css/v5-font-face.css') }}" rel="stylesheet">

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        sena: {
                            green:  '#1B8C3E',
                            'green-d': '#166A30',
                            'green-l': '#E8F5ED',
                            dark:   '#0D3320',
                            'dark-2':'#1A4A2E',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui'],
                    }
                }
            }
        }
    </script>

    <style>
        * { font-family: 'Inter', sans-serif; }
        html { scroll-behavior: smooth; }
        body { background: #fff; }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-thumb { background: #1B8C3E; border-radius: 3px; }

        /* Nav underline active */
        .nav-item-active {
            color: #1B8C3E !important;
            border-bottom: 2px solid #1B8C3E;
            padding-bottom: 2px;
        }

        /* Input focus */
        .sena-input:focus {
            outline: none;
            border-color: #1B8C3E;
            box-shadow: 0 0 0 3px rgba(27,140,62,.15);
        }

        /* Hero overlay */
        .hero-bg {
            background: linear-gradient(
                105deg,
                rgba(255,255,255,1)  0%,
                rgba(255,255,255,.97) 35%,
                rgba(255,255,255,.6)  60%,
                rgba(255,255,255,0)   80%
            );
        }

        /* Feature card */
        .feat-card { transition: transform .2s ease, box-shadow .2s ease; }
        .feat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(27,140,62,.13);
        }

        /* Decorative shape */
        .green-shape {
            position: absolute;
            background: #1B8C3E;
            border-radius: 0 0 40% 40%;
        }
    </style>
</head>

<body class="antialiased">

{{-- ═══════════════════ NAVBAR ═══════════════════ --}}
<nav class="sticky top-0 z-50 bg-white border-b border-gray-100 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            {{-- Logo --}}
            <a href="{{ route('sisgedi.index') }}"
               class="flex items-center gap-3 flex-shrink-0">
                <div class="flex-shrink-0">
                    <img src="{{ asset('general/assets/img/cefaempresa.png') }}"
                         alt="SENA"
                         class="w-10 h-10 object-contain"
                         onerror="this.onerror=null;this.src='';this.parentElement.innerHTML='<div class=\'w-10 h-10 rounded-full bg-sena-green flex items-center justify-center\'><i class=\'fas fa-leaf text-white\'></i></div>'">
                </div>
                <div class="border-l border-gray-200 pl-3">
                    <p class="font-bold text-gray-900 text-base leading-none">SISGEDI</p>
                    <p class="text-gray-500 text-xs leading-none mt-0.5">Sistema Integral de Gestión</p>
                </div>
            </a>

            {{-- Links de navegación --}}
            <div class="hidden lg:flex items-center gap-1">
                <a href="{{ route('sisgedi.index') }}"
                   class="flex items-center gap-1.5 px-3 py-2 text-sm font-medium
                          text-gray-600 hover:text-sena-green transition-colors rounded-lg
                          hover:bg-sena-green-l nav-item-active">
                    <i class="fas fa-home text-xs"></i> Inicio
                </a>
                <a href="#sobre"
                   class="flex items-center gap-1.5 px-3 py-2 text-sm font-medium
                          text-gray-600 hover:text-sena-green transition-colors rounded-lg
                          hover:bg-sena-green-l">
                    <i class="fas fa-info-circle text-xs"></i> Sobre SISGEDI
                </a>
                <a href="#modulos"
                   class="flex items-center gap-1.5 px-3 py-2 text-sm font-medium
                          text-gray-600 hover:text-sena-green transition-colors rounded-lg
                          hover:bg-sena-green-l">
                    <i class="fas fa-th-large text-xs"></i> Módulos
                </a>
                <a href="#roles"
                   class="flex items-center gap-1.5 px-3 py-2 text-sm font-medium
                          text-gray-600 hover:text-sena-green transition-colors rounded-lg
                          hover:bg-sena-green-l">
                    <i class="fas fa-users text-xs"></i> Roles
                </a>
                <a href="#organigrama"
                   class="flex items-center gap-1.5 px-3 py-2 text-sm font-medium
                          text-gray-600 hover:text-sena-green transition-colors rounded-lg
                          hover:bg-sena-green-l">
                    <i class="fas fa-sitemap text-xs"></i> Organigrama
                </a>
                <a href="#ayuda"
                   class="flex items-center gap-1.5 px-3 py-2 text-sm font-medium
                          text-gray-600 hover:text-sena-green transition-colors rounded-lg
                          hover:bg-sena-green-l">
                    <i class="fas fa-question-circle text-xs"></i> Ayuda
                </a>
            </div>

            {{-- Botón hamburger móvil --}}
            <button id="navToggle"
                    onclick="document.getElementById('mobileNav').classList.toggle('hidden')"
                    class="lg:hidden p-2 rounded-lg text-gray-500 hover:text-sena-green
                           hover:bg-sena-green-l transition-colors">
                <i class="fas fa-bars text-lg"></i>
            </button>
        </div>
    </div>

    {{-- Mobile nav --}}
    <div id="mobileNav" class="hidden lg:hidden border-t border-gray-100 bg-white">
        <div class="px-4 py-3 space-y-1">
            <a href="{{ route('sisgedi.index') }}"
               class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium
                      text-sena-green bg-sena-green-l">
                <i class="fas fa-home text-xs w-4"></i> Inicio
            </a>
            <a href="#sobre"
               class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium
                      text-gray-600 hover:bg-sena-green-l hover:text-sena-green transition-colors">
                <i class="fas fa-info-circle text-xs w-4"></i> Sobre SISGEDI
            </a>
            <a href="#modulos"
               class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium
                      text-gray-600 hover:bg-sena-green-l hover:text-sena-green transition-colors">
                <i class="fas fa-th-large text-xs w-4"></i> Módulos
            </a>
            <a href="{{ route('home') }}"
               class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium
                      text-gray-600 hover:bg-sena-green-l hover:text-sena-green transition-colors">
                <i class="fas fa-arrow-left text-xs w-4"></i> Volver a SICEFA
            </a>
        </div>
    </div>
</nav>

{{-- Contenido --}}
@yield('content')

{{-- ═══════════════════ FOOTER ═══════════════════ --}}
<footer style="background: #0D3320;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-white/70 text-sm">

            {{-- Col 1 --}}
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"
                     style="background:rgba(27,140,62,.3);">
                    <i class="fas fa-map-marker-alt text-white text-sm"></i>
                </div>
                <div>
                    <p class="text-white font-semibold leading-tight">
                        Centro de Formación Agroindustrial La Angostura
                    </p>
                    <p class="mt-1 leading-relaxed">
                        SENA Empresa<br>Campoalegre, Huila
                    </p>
                </div>
            </div>

            {{-- Col 2 --}}
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"
                     style="background:rgba(27,140,62,.3);">
                    <i class="fas fa-sitemap text-white text-sm"></i>
                </div>
                <div>
                    <p class="text-white font-semibold leading-tight">
                        Basado en el organigrama oficial
                    </p>
                    <p class="mt-1 leading-relaxed">
                        de SENA Empresa con sus tres gerencias<br>y siete roles dinámicos.
                    </p>
                </div>
            </div>

            {{-- Col 3 --}}
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"
                     style="background:rgba(27,140,62,.3);">
                    <i class="fas fa-shield-alt text-white text-sm"></i>
                </div>
                <div>
                    <p class="text-white font-semibold leading-tight">
                        Versión 8.0 — Agosto {{ date('Y') }}
                    </p>
                    <p class="mt-1 leading-relaxed">
                        Todos los derechos reservados
                    </p>
                </div>
            </div>

        </div>
    </div>
</footer>

<!-- jQuery -->
<script src="{{ asset('AdminLTE/plugins/jquery/jquery.min.js') }}"></script>
@yield('scripts')

</body>
</html>
