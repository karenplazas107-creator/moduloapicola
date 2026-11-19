{{-- Navbar horizontal fija — estilo SENA Empresa --}}
<header id="sisgedi-header"
        class="fixed top-0 left-0 right-0 z-50 transition-all duration-300"
        style="background: rgba(0,26,41,0.97);
               backdrop-filter: blur(12px);
               -webkit-backdrop-filter: blur(12px);
               border-bottom: 3px solid #39A900;
               box-shadow: 0 4px 20px rgba(0,0,0,0.35);">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            {{-- ── LOGO / MARCA ── --}}
            <a href="{{ route('sisgedi.index') }}"
               class="flex items-center gap-3 group flex-shrink-0">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0
                            transition-transform duration-200 group-hover:scale-110"
                     style="background: #39A900;
                            box-shadow: 0 4px 12px rgba(57,169,0,0.45);">
                    <i class="fas fa-folder-open text-white text-sm"></i>
                </div>
                <div class="hidden sm:block">
                    <p class="text-white font-bold text-base leading-none tracking-wide">SISGEDI</p>
                    <p class="leading-none mt-0.5 text-[11px] font-medium"
                       style="color: #62E31D;">Gestión Documental</p>
                </div>
            </a>

            {{-- ── NAV LINKS (escritorio) ── --}}
            <nav class="hidden lg:flex items-center gap-1">

                <a href="{{ route('sisgedi.index') }}"
                   class="nav-link-top {{ Route::is('sisgedi.index') ? 'nav-active' : '' }}">
                    <i class="fas fa-home text-xs"></i> Inicio
                </a>

                {{-- Dropdown Documentos --}}
                <div class="relative group">
                    <button class="nav-link-top flex items-center gap-1
                                   {{ Route::is('sisgedi.create') || Route::is('sisgedi.edit') ? 'nav-active' : '' }}">
                        <i class="fas fa-file-alt text-xs"></i>
                        Documentos
                        <i class="fas fa-chevron-down text-[10px] ml-0.5
                                  transition-transform duration-200 group-hover:rotate-180"></i>
                    </button>
                    <div class="absolute top-full left-0 mt-1 w-52 rounded-xl py-1 opacity-0
                                invisible group-hover:opacity-100 group-hover:visible
                                transition-all duration-200 translate-y-1 group-hover:translate-y-0"
                         style="background: #002336;
                                border: 1px solid rgba(57,169,0,0.25);
                                box-shadow: 0 12px 32px rgba(0,0,0,0.45);">
                        <a href="{{ route('sisgedi.index') }}"
                           class="dropdown-link-top">
                            <i class="fas fa-list text-xs w-4"></i> Listado
                        </a>
                        <a href="{{ route('sisgedi.create') }}"
                           class="dropdown-link-top">
                            <i class="fas fa-plus text-xs w-4"></i> Nuevo Documento
                        </a>
                    </div>
                </div>

                <a href="{{ route('home') }}" class="nav-link-top">
                    <i class="fas fa-th-large text-xs"></i> SICEFA
                </a>

            </nav>

            {{-- ── ACCIONES DERECHA ── --}}
            <div class="flex items-center gap-3">

                {{-- Botón nuevo documento --}}
                <a href="{{ route('sisgedi.create') }}"
                   class="hidden md:inline-flex items-center gap-2 text-white text-sm
                          font-semibold px-4 py-2 rounded-full transition-all duration-200
                          hover:scale-105"
                   style="background: #39A900;
                          box-shadow: 0 4px 14px rgba(57,169,0,0.4);"
                   onmouseover="this.style.background='#2d8500'"
                   onmouseout="this.style.background='#39A900'">
                    <i class="fas fa-plus text-xs"></i>
                    Nuevo Documento
                </a>

                {{-- Usuario --}}
                @auth
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                            class="flex items-center gap-2 px-2 py-1.5 rounded-full
                                   transition-colors duration-150 hover:bg-white/10">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center
                                    text-white text-xs font-bold flex-shrink-0"
                             style="background: linear-gradient(135deg,#39A900,#002336);
                                    box-shadow: 0 2px 8px rgba(57,169,0,0.4);">
                            {{ strtoupper(substr(Auth::user()->nickname ?? Auth::user()->name, 0, 2)) }}
                        </div>
                        <div class="hidden md:block text-left">
                            <p class="text-white text-xs font-semibold leading-none">
                                {{ Str::limit(Auth::user()->nickname ?? Auth::user()->name, 14) }}
                            </p>
                            <p class="text-[10px] leading-none mt-0.5" style="color:#62E31D;">
                                {{ Auth::user()->roles->first()?->name ?? 'Sin rol' }}
                            </p>
                        </div>
                        <i class="fas fa-chevron-down text-white/40 text-[10px] hidden md:block
                                  transition-transform duration-200"
                           :class="open ? 'rotate-180' : ''"></i>
                    </button>

                    <div x-show="open" @click.outside="open = false"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="absolute right-0 mt-2 w-52 rounded-xl py-1 z-50"
                         style="background:#002336;
                                border:1px solid rgba(57,169,0,0.25);
                                box-shadow:0 12px 32px rgba(0,0,0,0.45);">
                        <div class="px-4 py-2.5 border-b" style="border-color:rgba(255,255,255,0.08);">
                            <p class="font-semibold text-white text-sm truncate">
                                {{ Auth::user()->person->first_name ?? Auth::user()->name }}
                            </p>
                            <p class="text-white/40 text-xs truncate mt-0.5">
                                {{ Auth::user()->email }}
                            </p>
                        </div>
                        <a href="{{ route('logout') }}"
                           onclick="event.preventDefault();
                                    document.getElementById('sisgedi-logout').submit();"
                           class="flex items-center gap-2 px-4 py-2.5 text-red-400 text-sm
                                  transition-colors rounded-b-xl hover:bg-red-500/10 hover:text-red-300">
                            <i class="fas fa-sign-out-alt text-xs"></i> Cerrar sesión
                        </a>
                    </div>
                </div>
                <form id="sisgedi-logout" action="{{ route('logout') }}"
                      method="POST" class="hidden">@csrf</form>

                @else
                <a href="{{ route('login') }}"
                   class="inline-flex items-center gap-1.5 text-sm font-semibold
                          transition-colors"
                   style="color:#39A900;"
                   onmouseover="this.style.color='#62E31D'"
                   onmouseout="this.style.color='#39A900'">
                    <i class="fas fa-sign-in-alt text-xs"></i> Iniciar sesión
                </a>
                @endauth

                {{-- Hamburger móvil --}}
                <button onclick="toggleMobileMenu()"
                        class="lg:hidden text-white/70 hover:text-white transition-colors p-1">
                    <i class="fas fa-bars text-lg"></i>
                </button>
            </div>

        </div>
    </div>

    {{-- ── MENÚ MÓVIL ── --}}
    <div id="mobileMenu" class="hidden lg:hidden"
         style="background:#001A29; border-top:1px solid rgba(57,169,0,0.2);">
        <div class="px-4 py-3 space-y-1">
            <a href="{{ route('sisgedi.index') }}" class="mobile-link-top">
                <i class="fas fa-home w-5"></i> Inicio
            </a>
            <a href="{{ route('sisgedi.create') }}" class="mobile-link-top">
                <i class="fas fa-plus w-5"></i> Nuevo Documento
            </a>
            <a href="{{ route('sisgedi.index') }}" class="mobile-link-top">
                <i class="fas fa-list w-5"></i> Listado de Documentos
            </a>
            <a href="{{ route('home') }}" class="mobile-link-top">
                <i class="fas fa-th-large w-5"></i> Volver a SICEFA
            </a>
            @auth
            <div class="pt-2 mt-2" style="border-top:1px solid rgba(255,255,255,0.08);">
                <a href="{{ route('logout') }}"
                   onclick="event.preventDefault();
                            document.getElementById('sisgedi-logout').submit();"
                   class="mobile-link-top text-red-400">
                    <i class="fas fa-sign-out-alt w-5"></i> Cerrar sesión
                </a>
            </div>
            @endauth
        </div>
    </div>

</header>

{{-- Espaciador para compensar el navbar fixed --}}
<div style="height: 67px;"></div>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
    function toggleMobileMenu() {
        const m = document.getElementById('mobileMenu');
        m.classList.toggle('hidden');
    }
</script>
