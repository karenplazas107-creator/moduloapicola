<!DOCTYPE html>
<html lang="es">
@include('sisgedi::layouts.partials.head')

<body class="bg-[#f0f4f8]">

    {{-- Navbar horizontal fija en top --}}
    @include('sisgedi::layouts.partials.navbar')

    {{-- Línea shimmer verde --}}
    <div class="shimmer-green"></div>

    {{-- Contenido --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    @include('sisgedi::layouts.partials.footer')

    @include('sisgedi::layouts.partials.scripts')
    @yield('script')

</body>
</html>
