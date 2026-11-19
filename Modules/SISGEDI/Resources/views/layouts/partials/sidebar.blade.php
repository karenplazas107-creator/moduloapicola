{{-- Overlay móvil --}}
<div id="sidebarOverlay"
     class="fixed inset-0 bg-black/60 z-20 hidden lg:hidden"
     onclick="toggleSidebar()"></div>

{{-- Sidebar — azul marino SENA --}}
<aside id="sidebar"
       class="w-64 flex-shrink-0 flex flex-col bg-[#001A29] h-screen z-30
              fixed lg:static -translate-x-full lg:translate-x-0
              transition-transform duration-300 ease-in-out
              border-r border-sena-green/20">

    {{-- Marca --}}
    <div class="flex items-center gap-3 px-5 py-5
                border-b border-sena-green/25" style="border-bottom: 2px solid #39A900;">
        <div class="w-10 h-10 rounded-xl bg-sena-green flex items-center justify-center flex-shrink-0
                    shadow-lg shadow-sena-green/40">
            <i class="fas fa-folder-open text-white"></i>
        </div>
        <div>
            <p class="text-white font-bold text-base leading-tight tracking-wide">SISGEDI</p>
            <p class="text-sena-lime text-[11px] leading-tight font-medium">Gestión Documental</p>
        </div>
    </div>

    {{-- Usuario --}}
    <div class="px-4 py-4 border-b border-white/10">
        <div class="flex items-center gap-3">
            <div class="relative">
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-sena-green to-[#00324D]
                            flex items-center justify-center text-white text-xs font-bold flex-shrink-0
                            ring-2 ring-sena-green/50">
                    @auth
                        {{ strtoupper(substr(Auth::user()->nickname ?? Auth::user()->name, 0, 2)) }}
                    @else
                        <i class="fas fa-user text-white text-xs"></i>
                    @endauth
                </div>
                <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full bg-sena-lime
                             border-2 border-[#001A29] pulse-green"></span>
            </div>
            <div class="min-w-0">
                @auth
                    <p class="text-white text-sm font-semibold truncate leading-tight">
                        {{ Str::limit(Auth::user()->nickname ?? Auth::user()->name, 18) }}
                    </p>
                    <p class="text-white/40 text-xs truncate leading-tight mt-0.5">
                        {{ Auth::user()->roles->first()?->name ?? 'Sin rol' }}
                    </p>
                @else
                    <a href="{{ route('login') }}" class="text-sena-green text-sm hover:text-sena-lime transition-colors">
                        Iniciar sesión
                    </a>
                @endauth
            </div>
        </div>
    </div>

    {{-- Navegación --}}
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">

        <p class="text-white/25 text-[10px] font-semibold uppercase tracking-widest px-3 pb-2 pt-1">
            Menú Principal
        </p>

        <a href="{{ route('sisgedi.index') }}"
           class="sidebar-link {{ Route::is('sisgedi.index') ? 'active' : '' }}">
            <span class="icon"><i class="fas fa-home"></i></span>
            Inicio
        </a>

        {{-- Documentos colapsable --}}
        <div x-data="{ open: {{ Route::is('sisgedi.create') || Route::is('sisgedi.edit') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="sidebar-link {{ Route::is('sisgedi.create') || Route::is('sisgedi.edit') ? 'active' : '' }}">
                <span class="icon"><i class="fas fa-file-alt"></i></span>
                <span class="flex-1">Documentos</span>
                <i class="fas fa-chevron-down text-xs transition-transform duration-200"
                   :class="open ? 'rotate-180' : ''"></i>
            </button>
            <div x-show="open" x-transition class="mt-1 space-y-0.5">
                <a href="{{ route('sisgedi.index') }}"
                   class="sub-link {{ Route::is('sisgedi.index') ? 'active' : '' }}">
                    <i class="fas fa-list text-[10px]"></i> Listado de Documentos
                </a>
                <a href="{{ route('sisgedi.create') }}"
                   class="sub-link {{ Route::is('sisgedi.create') ? 'active' : '' }}">
                    <i class="fas fa-plus text-[10px]"></i> Registrar Documento
                </a>
            </div>
        </div>

    </nav>

    {{-- Volver a SICEFA --}}
    <div class="px-3 pb-4 pt-3 border-t border-white/10">
        <a href="{{ route('home') }}" class="sidebar-link text-white/40 hover:text-sena-lime">
            <span class="icon"><i class="fas fa-arrow-left"></i></span>
            Volver a SICEFA
        </a>
    </div>
</aside>

{{-- Alpine.js --}}
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('-translate-x-full');
        document.getElementById('sidebarOverlay').classList.toggle('hidden');
    }
</script>
