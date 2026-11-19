{{-- Breadcrumb --}}
<div class="flex items-center gap-2 text-xs text-white/50 mb-5">
    <a href="{{ route('sisgedi.index') }}"
       class="hover:text-sena-lime transition-colors font-semibold text-sena-green">
        SISGEDI
    </a>
    <i class="fas fa-chevron-right text-[9px] text-white/25"></i>
    <span class="text-white/70 font-medium">{{ $title ?? 'Inicio' }}</span>
</div>

@if(session('success'))
    <div id="alert-ok"
         class="flex items-center gap-3 bg-sena-green/15 border border-sena-green/40
                text-emerald-200 rounded-xl px-4 py-3 mb-5 text-sm">
        <i class="fas fa-check-circle text-sena-green flex-shrink-0"></i>
        <span class="flex-1">{{ session('success') }}</span>
        <button onclick="document.getElementById('alert-ok').remove()"
                class="text-white/40 hover:text-white ml-auto transition-colors">
            <i class="fas fa-times"></i>
        </button>
    </div>
@endif

@if(session('error'))
    <div id="alert-err"
         class="flex items-center gap-3 bg-red-500/15 border border-red-500/40
                text-red-300 rounded-xl px-4 py-3 mb-5 text-sm">
        <i class="fas fa-exclamation-circle text-red-400 flex-shrink-0"></i>
        <span class="flex-1">{{ session('error') }}</span>
        <button onclick="document.getElementById('alert-err').remove()"
                class="text-white/40 hover:text-white ml-auto transition-colors">
            <i class="fas fa-times"></i>
        </button>
    </div>
@endif

@if($errors->any())
    <div class="bg-amber-500/15 border border-amber-500/40 text-amber-200
                rounded-xl px-4 py-3 mb-5 text-sm">
        <div class="flex items-center gap-2 font-semibold mb-1">
            <i class="fas fa-exclamation-triangle text-amber-400"></i>
            Corrige los siguientes errores:
        </div>
        <ul class="list-disc list-inside space-y-0.5 text-amber-100/80">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
