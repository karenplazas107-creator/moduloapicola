@extends('sisgedi::layouts.landing')

@section('content')

{{-- ═══════════════════════════════════════════════════════════════════════
     HERO — foto SENA de fondo + texto izquierda + card login derecha
════════════════════════════════════════════════════════════════════════ --}}
<section class="relative overflow-hidden bg-white" style="min-height: 520px;">

    {{-- Foto de fondo (lado derecho) --}}
    <div class="absolute inset-0 flex justify-end">
        <div class="w-full h-full relative">
            <img src="{{ asset('general/assets/img/sena_edificio.jpg') }}"
                 alt="Centro SENA La Angostura"
                 class="w-full h-full object-cover"
                 onerror="this.onerror=null;
                          this.parentElement.style.background='linear-gradient(135deg,#0D3320,#1B8C3E)';
                          this.remove();">
            {{-- Overlay blanco → transparente de izquierda a derecha --}}
            <div class="absolute inset-0 hero-bg"></div>
            {{-- Triángulos decorativos verdes (esquinas) --}}
            <div class="absolute top-0 right-0 w-0 h-0"
                 style="border-top: 80px solid rgba(27,140,62,.18);
                        border-left: 80px solid transparent;"></div>
            <div class="absolute bottom-0 left-0 w-0 h-0"
                 style="border-bottom: 60px solid rgba(27,140,62,.1);
                        border-right: 60px solid transparent;"></div>
        </div>
    </div>

    {{-- Contenido sobre la imagen --}}
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">

            {{-- ── Columna izquierda: título + features strip ── --}}
            <div>
                <h1 class="font-black leading-none mb-3"
                    style="font-size: clamp(2.8rem,6vw,4rem); color:#0D3320;">
                    SISGEDI
                </h1>
                <p class="font-bold text-gray-700 text-xl mb-4">
                    Sistema Integral de Gestión
                </p>
                <p class="text-gray-600 leading-relaxed text-sm max-w-md mb-8">
                    Plataforma que centraliza, digitaliza y da trazabilidad completa
                    a los procesos de gestión de documentos, evidencias
                    y paz y salvo de los colaboradores del Centro de Formación
                    Agroindustrial La Angostura.
                </p>

                {{-- ── Chips de funcionalidades ── --}}
                <div class="flex flex-wrap gap-3">
                    @php
                    $chips = [
                        ['fas fa-tasks',          'Gestión de Tareas'],
                        ['fas fa-code-branch',    'Flujo en Cascada'],
                        ['fas fa-user-check',     'Postulaciones'],
                        ['fas fa-pen-nib',        'Firmas de Instructores'],
                        ['fas fa-shield-check',   'Paz y Salvo por Entregable'],
                    ];
                    @endphp
                    @foreach($chips as [$icon, $label])
                    <div class="inline-flex items-center gap-2 bg-white/90 border border-gray-200
                                rounded-xl px-4 py-2.5 text-xs font-semibold text-gray-700
                                shadow-sm hover:border-sena-green hover:text-sena-green
                                transition-colors cursor-default"
                         style="backdrop-filter:blur(4px);">
                        <i class="{{ $icon }} text-sena-green"></i>
                        {{ $label }}
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- ── Columna derecha: card de inicio de sesión ── --}}
            <div class="flex justify-end">
                <div class="w-full max-w-sm bg-white rounded-2xl shadow-2xl overflow-hidden"
                     style="border: 1px solid rgba(0,0,0,.06);">

                    {{-- Header de la card --}}
                    <div class="px-8 pt-8 pb-6 text-center">
                        <div class="w-14 h-14 rounded-full mx-auto mb-4 flex items-center justify-center"
                             style="background: #E8F5ED; border: 2px solid #1B8C3E;">
                            <i class="fas fa-lock text-sena-green text-xl"></i>
                        </div>
                        <h2 class="font-bold text-gray-900 text-xl">Iniciar Sesión</h2>
                        <p class="text-gray-500 text-xs mt-1">
                            Ingresa tus credenciales para acceder al sistema
                        </p>
                    </div>

                    {{-- Formulario --}}
                    <form action="{{ route('sisgedi.login.post') }}" method="POST" class="px-8 pb-8">
                        @csrf

                        {{-- Error usuario --}}
                        @error('nickname')
                        <div class="mb-4 p-3 rounded-xl text-xs text-red-700 bg-red-50 border border-red-200 flex items-center gap-2">
                            <i class="fas fa-exclamation-circle flex-shrink-0"></i> {{ $message }}
                        </div>
                        @enderror

                        {{-- Error contraseña --}}
                        @error('password')
                        <div class="mb-4 p-3 rounded-xl text-xs text-red-700 bg-red-50 border border-red-200 flex items-center gap-2">
                            <i class="fas fa-exclamation-circle flex-shrink-0"></i> {{ $message }}
                        </div>
                        @enderror

                        {{-- Usuario --}}
                        <div class="mb-4">
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400">
                                    <i class="fas fa-user text-sm"></i>
                                </span>
                                <input type="text"
                                       name="nickname"
                                       placeholder="Usuario o correo electrónico"
                                       value="{{ old('nickname') }}"
                                       class="sena-input w-full pl-10 pr-4 py-3 text-sm rounded-xl
                                              border {{ $errors->has('nickname') ? 'border-red-400 bg-red-50' : 'border-gray-200 bg-gray-50' }}
                                              text-gray-800 placeholder-gray-400 transition-all">
                            </div>
                        </div>

                        {{-- Contraseña --}}
                        <div class="mb-4">
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400">
                                    <i class="fas fa-lock text-sm"></i>
                                </span>
                                <input type="password"
                                       id="passwordField"
                                       name="password"
                                       placeholder="Contraseña"
                                       class="sena-input w-full pl-10 pr-11 py-3 text-sm rounded-xl
                                              border {{ $errors->has('password') ? 'border-red-400 bg-red-50' : 'border-gray-200 bg-gray-50' }}
                                              text-gray-800 placeholder-gray-400 transition-all">
                                <button type="button"
                                        onclick="togglePassword()"
                                        class="absolute right-3.5 top-1/2 -translate-y-1/2
                                               text-gray-400 hover:text-gray-600 transition-colors">
                                    <i id="eyeIcon" class="fas fa-eye text-sm"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Recordarme --}}
                        <div class="flex items-center justify-between mb-5">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="remember"
                                       class="rounded border-gray-300 text-sena-green
                                              focus:ring-sena-green w-4 h-4">
                                <span class="text-xs text-gray-600">Recordarme</span>
                            </label>
                            <a href="#"
                               class="text-xs font-semibold text-sena-green hover:text-sena-green-d
                                      transition-colors">
                                ¿Olvidaste tu contraseña?
                            </a>
                        </div>

                        {{-- Botón ingresar --}}
                        <button type="submit"
                                class="w-full flex items-center justify-center gap-2 py-3
                                       text-white font-bold text-sm rounded-xl transition-all
                                       duration-200 hover:opacity-90 hover:-translate-y-0.5
                                       active:translate-y-0"
                                style="background: #1B8C3E;
                                       box-shadow: 0 4px 14px rgba(27,140,62,.4);">
                            <i class="fas fa-sign-in-alt text-sm"></i>
                            Ingresar
                        </button>

                        {{-- Separador --}}
                        <div class="flex items-center gap-3 my-4">
                            <div class="flex-1 h-px bg-gray-200"></div>
                            <span class="text-xs text-gray-400 font-medium">o</span>
                            <div class="flex-1 h-px bg-gray-200"></div>
                        </div>

                        {{-- Botón guía --}}
                        <a href="#modulos"
                           class="w-full flex items-center justify-center gap-2 py-3
                                  border border-gray-200 rounded-xl text-gray-600 text-sm
                                  font-medium hover:bg-gray-50 hover:border-sena-green
                                  hover:text-sena-green transition-all duration-200">
                            <i class="fas fa-book-open text-sm"></i>
                            Guía de Primeros Pasos
                        </a>
                    </form>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════════════
     ¿QUÉ PUEDES HACER CON SISGEDI?
════════════════════════════════════════════════════════════════════════ --}}
<section id="modulos" class="py-16 bg-white border-t border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <h2 class="text-center font-bold text-gray-900 text-2xl mb-10">
            ¿Qué puedes hacer con <span style="color:#1B8C3E;">SISGEDI</span>?
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">
            @php
            $features = [
                [
                    'icon'  => 'fas fa-file-signature',
                    'title' => 'Postulaciones a Cargos',
                    'desc'  => 'Gestiona el proceso completo de postulación, entrevistas y selección de personal.',
                ],
                [
                    'icon'  => 'fas fa-folder-open',
                    'title' => 'Gestión de Evidencias',
                    'desc'  => 'Organiza y almacena las evidencias de los sectores productivos por fase.',
                ],
                [
                    'icon'  => 'fas fa-clipboard-check',
                    'title' => 'Paz y Salvo',
                    'desc'  => 'Controla entregables y obtén las firmas digitales de los instructores responsables.',
                ],
                [
                    'icon'  => 'fas fa-code-branch',
                    'title' => 'Flujo en Cascada',
                    'desc'  => 'Ejecución jerárquica de tareas desde la gerencia hasta los colaboradores.',
                ],
                [
                    'icon'  => 'fas fa-cloud',
                    'title' => 'Almacenamiento Seguro',
                    'desc'  => 'Tus documentos y evidencias resguardados en la nube con disponibilidad garantizada.',
                ],
            ];
            @endphp

            @foreach($features as $feat)
            <div class="feat-card p-6 rounded-2xl border border-gray-100 bg-white cursor-default"
                 style="box-shadow: 0 2px 12px rgba(0,0,0,.05);">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-4"
                     style="background: #E8F5ED;">
                    <i class="{{ $feat['icon'] }} text-xl" style="color:#1B8C3E;"></i>
                </div>
                <h3 class="font-bold text-gray-900 text-sm mb-2 leading-tight">
                    {{ $feat['title'] }}
                </h3>
                <p class="text-gray-500 text-xs leading-relaxed">
                    {{ $feat['desc'] }}
                </p>
            </div>
            @endforeach
        </div>

    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════════════
     SECCIÓN SOBRE (roles, organigrama, ayuda) — sólo si hay sesión activa
     y el usuario necesita más contexto
════════════════════════════════════════════════════════════════════════ --}}

{{-- Sección sobre SISGEDI --}}
<section id="sobre" class="py-16" style="background:#f8fafb;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-14 items-center">

            {{-- Texto --}}
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest mb-3"
                   style="color:#1B8C3E;">Sobre el sistema</p>
                <h2 class="font-extrabold text-gray-900 text-3xl leading-tight mb-5">
                    Una plataforma diseñada<br>
                    <span style="color:#1B8C3E;">para el SENA</span>
                </h2>
                <p class="text-gray-600 leading-relaxed text-sm mb-5">
                    SISGEDI nace como proyecto formativo del programa
                    <strong class="text-gray-800">ADSO / ADSI</strong> del Centro de Formación
                    Agroindustrial La Angostura, con el objetivo de digitalizar y estandarizar
                    la gestión documental institucional bajo los lineamientos del
                    <strong class="text-gray-800">Sistema de Gestión de Calidad SENA</strong>.
                </p>
                <p class="text-gray-600 leading-relaxed text-sm mb-7">
                    Integrado a <strong class="text-gray-800">SICEFA</strong>, el ERP
                    institucional del centro, permite que instructores, aprendices y
                    administrativos gestionen sus documentos en un único repositorio
                    seguro, trazable y accesible desde cualquier dispositivo.
                </p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('sisgedi.index') }}"
                       class="inline-flex items-center gap-2 text-white text-sm font-semibold
                              px-6 py-2.5 rounded-full transition-all hover:opacity-90"
                       style="background:#1B8C3E;box-shadow:0 4px 14px rgba(27,140,62,.35);">
                        <i class="fas fa-sign-in-alt text-xs"></i> Acceder al sistema
                    </a>
                    <a href="{{ route('home') }}"
                       class="inline-flex items-center gap-2 text-gray-600 text-sm font-semibold
                              px-6 py-2.5 rounded-full border border-gray-200 transition-all
                              hover:border-sena-green hover:text-sena-green">
                        <i class="fas fa-th-large text-xs"></i> Ir a SICEFA
                    </a>
                </div>
            </div>

            {{-- Tarjetas de datos --}}
            <div class="grid grid-cols-2 gap-4">
                @php
                $infoCards = [
                    ['fas fa-users',         '#1B8C3E', '7',       'Roles dinámicos',       'Desde Gerente General hasta colaborador'],
                    ['fas fa-sitemap',       '#0D3320', '3',       'Gerencias',             'Estructura orgánica oficial del SENA'],
                    ['fas fa-file-alt',      '#1B8C3E', $totalDocumentos, 'Documentos',   'Registrados en el sistema'],
                    ['fas fa-check-circle',  '#0D3320', $documentosActivos, 'Activos',    'Documentos vigentes hoy'],
                ];
                @endphp
                @foreach($infoCards as [$icon, $color, $num, $label, $sub])
                <div class="p-5 rounded-2xl bg-white border border-gray-100 text-center"
                     style="box-shadow:0 2px 10px rgba(0,0,0,.05);">
                    <div class="w-10 h-10 rounded-xl mx-auto mb-3 flex items-center justify-center"
                         style="background:{{ $color }}1a;">
                        <i class="{{ $icon }}" style="color:{{ $color }};font-size:1.1rem;"></i>
                    </div>
                    <p class="font-extrabold text-gray-900 text-2xl leading-none">{{ $num }}</p>
                    <p class="font-semibold text-gray-700 text-sm mt-1">{{ $label }}</p>
                    <p class="text-gray-400 text-xs mt-0.5">{{ $sub }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════════════
     ROLES DEL SISTEMA
════════════════════════════════════════════════════════════════════════ --}}
<section id="roles" class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-10">
            <p class="text-xs font-semibold uppercase tracking-widest mb-2"
               style="color:#1B8C3E;">Estructura organizacional</p>
            <h2 class="font-extrabold text-gray-900 text-2xl">Roles del Sistema</h2>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-4">
            @php
            $roles = [
                ['fas fa-crown',        '#1B8C3E', 'Gerente General',      'Nivel estratégico'],
                ['fas fa-briefcase',    '#0D3320', 'Gerente Admin.',        'Área administrativa'],
                ['fas fa-hard-hat',     '#1B8C3E', 'Gerente Prod.',         'Área productiva'],
                ['fas fa-chalkboard-teacher', '#0D3320', 'Instructor',      'Formación'],
                ['fas fa-user-graduate','#1B8C3E', 'Aprendiz',             'En formación'],
                ['fas fa-user-tie',     '#0D3320', 'Colaborador',          'Personal de apoyo'],
                ['fas fa-shield-alt',   '#1B8C3E', 'Auditor',              'Control y calidad'],
            ];
            @endphp
            @foreach($roles as [$icon, $color, $rol, $area])
            <div class="feat-card text-center p-4 rounded-2xl border border-gray-100 bg-white"
                 style="box-shadow:0 2px 10px rgba(0,0,0,.04);">
                <div class="w-12 h-12 rounded-full mx-auto mb-3 flex items-center justify-center"
                     style="background:{{ $color }}15;border:2px solid {{ $color }}30;">
                    <i class="{{ $icon }}" style="color:{{ $color }};font-size:1.1rem;"></i>
                </div>
                <p class="font-bold text-gray-800 text-xs leading-tight">{{ $rol }}</p>
                <p class="text-gray-400 text-[11px] mt-0.5">{{ $area }}</p>
            </div>
            @endforeach
        </div>

    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════════════
     ORGANIGRAMA SIMPLIFICADO
════════════════════════════════════════════════════════════════════════ --}}
<section id="organigrama" class="py-16" style="background:#f8fafb;">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-10">
            <p class="text-xs font-semibold uppercase tracking-widest mb-2"
               style="color:#1B8C3E;">Jerarquía</p>
            <h2 class="font-extrabold text-gray-900 text-2xl">Organigrama SISGEDI</h2>
        </div>

        {{-- Árbol visual --}}
        <div class="flex flex-col items-center gap-0">

            {{-- Nivel 1 --}}
            <div class="px-8 py-3 rounded-2xl text-center text-white font-bold text-sm"
                 style="background:#1B8C3E;box-shadow:0 4px 14px rgba(27,140,62,.35);min-width:200px;">
                <i class="fas fa-crown mr-2"></i> Gerente General
            </div>
            <div class="w-px h-6" style="background:#1B8C3E;"></div>

            {{-- Nivel 2 --}}
            <div class="flex items-start gap-12">
                @foreach([['fas fa-briefcase','Gerente Admin.'],['fas fa-hard-hat','Gerente Prod.']] as [$ic,$nm])
                <div class="flex flex-col items-center gap-0">
                    <div class="px-6 py-2.5 rounded-xl text-white font-semibold text-xs text-center"
                         style="background:#0D3320;min-width:140px;">
                        <i class="{{ $ic }} mr-1"></i>{{ $nm }}
                    </div>
                    <div class="w-px h-5" style="background:#0D3320;"></div>
                    <div class="flex items-start gap-6">
                        @foreach([['fas fa-chalkboard-teacher','Instructor'],['fas fa-user-graduate','Aprendiz']] as [$si,$sn])
                        <div class="flex flex-col items-center gap-0">
                            <div class="px-4 py-2 rounded-xl text-xs font-medium text-center border"
                                 style="background:#fff;border-color:#1B8C3E33;color:#1B8C3E;min-width:110px;">
                                <i class="{{ $si }} mr-1 text-sena-green"></i>{{ $sn }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

        </div>

    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════════════
     AYUDA — primeros pasos
════════════════════════════════════════════════════════════════════════ --}}
<section id="ayuda" class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-10">
            <p class="text-xs font-semibold uppercase tracking-widest mb-2"
               style="color:#1B8C3E;">Guía rápida</p>
            <h2 class="font-extrabold text-gray-900 text-2xl">Primeros Pasos</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @php
            $pasos = [
                ['1', 'fas fa-sign-in-alt',    'Inicia sesión',         'Usa tus credenciales institucionales del SENA para acceder al sistema.'],
                ['2', 'fas fa-search',          'Explora el módulo',     'Navega por el menú principal y conoce las secciones disponibles según tu rol.'],
                ['3', 'fas fa-file-plus',       'Registra un documento', 'Haz clic en "Nuevo Documento", completa el formulario y guarda el registro.'],
                ['4', 'fas fa-check-double',    'Gestiona y da seguimiento','Consulta el estado, edita o archiva documentos desde el listado general.'],
            ];
            @endphp
            @foreach($pasos as [$num, $icon, $title, $desc])
            <div class="relative feat-card p-6 rounded-2xl border border-gray-100 bg-white"
                 style="box-shadow:0 2px 12px rgba(0,0,0,.05);">
                <span class="absolute -top-3 -left-1 w-8 h-8 rounded-full flex items-center
                              justify-center text-white font-extrabold text-sm"
                      style="background:#1B8C3E;box-shadow:0 3px 10px rgba(27,140,62,.4);">
                    {{ $num }}
                </span>
                <div class="w-11 h-11 rounded-xl flex items-center justify-center mb-4 mt-2"
                     style="background:#E8F5ED;">
                    <i class="{{ $icon }}" style="color:#1B8C3E;font-size:1.1rem;"></i>
                </div>
                <h3 class="font-bold text-gray-800 text-sm mb-2">{{ $title }}</h3>
                <p class="text-gray-500 text-xs leading-relaxed">{{ $desc }}</p>
            </div>
            @endforeach
        </div>

    </div>
</section>

@endsection

@section('scripts')
<script>
    function togglePassword() {
        const f = document.getElementById('passwordField');
        const e = document.getElementById('eyeIcon');
        if (f.type === 'password') {
            f.type = 'text';
            e.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            f.type = 'password';
            e.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
</script>
@endsection
