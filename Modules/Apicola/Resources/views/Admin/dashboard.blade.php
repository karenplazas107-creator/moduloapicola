@extends('apicola::Admin.layout')

@section('title', 'SENA APÍCOLA • Panel de Administración')

@section('breadcrumb')
    <span class="active-page">Inicio</span>
@endsection

@section('styles')
    <!-- Estilos modulares del Dashboard Apícola -->
    <link rel="stylesheet" href="{{ asset('modules/apicola/css/dashboard.css') }}">
@endsection

@section('content')
<div class="ap-dashboard-container">

    <!-- 1. HERO BANNER DE BIENVENIDA CON ABEJA Y FOTOGRAFÍA -->
    <div class="ap-hero-card">
        <div class="ap-hero-left">
            <div class="ap-hero-mascot-box">
                <img src="{{ asset('modules/apicola/img/bee_mascot.jpg') }}" alt="Mascota Apícola">
            </div>
            <div class="ap-hero-texts">
                <h1>¡Hola, {{ Auth::user()?->person?->first_name ?? (Auth::user()?->name ?? 'Karen Michelle') }}!</h1>
                <h2>Bienvenida al sistema de gestión apícola.</h2>
                <p>Aquí puedes consultar y administrar la información de los apiarios, colmenas, producción e inventario.</p>
            </div>
        </div>

        <div class="ap-hero-right">
            <!-- Hexágono decorativo -->
            <svg class="ap-hero-deco-hex" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <polygon points="20,2 35,11 35,29 20,38 5,29 5,11" stroke="#f59e0b" stroke-width="2" fill="none"/>
            </svg>
            <div class="ap-hero-photo-wrap">
                <img src="{{ asset('modules/apicola/img/bee_hero.jpg') }}" alt="Abeja polinizando">
            </div>
        </div>
    </div>

    <!-- 2. TARJETAS DE MÉTRICAS (KPIs) -->
    <div class="ap-kpi-row">
        <!-- KPI 1: Apiarios -->
        <div class="ap-kpi-card">
            <div class="ap-kpi-top">
                <div class="ap-kpi-icon kpi-icon-green">
                    <i class="fas fa-boxes-packing"></i>
                </div>
                <div class="ap-kpi-info">
                    <span class="ap-kpi-title">Apiarios</span>
                    <span class="ap-kpi-val">{{ $totalApiarios ?? 5 }}</span>
                </div>
            </div>
            <div class="ap-kpi-bottom">
                <span class="ap-kpi-sub-text">Activos</span>
                <span class="ap-kpi-badge up">
                    <i class="fas fa-arrow-up"></i> 1 este mes
                </span>
            </div>
        </div>

        <!-- KPI 2: Colmenas -->
        <div class="ap-kpi-card">
            <div class="ap-kpi-top">
                <div class="ap-kpi-icon kpi-icon-amber">
                    <i class="fas fa-cubes-stacked"></i>
                </div>
                <div class="ap-kpi-info">
                    <span class="ap-kpi-title">Colmenas</span>
                    <span class="ap-kpi-val">{{ $totalColmenas ?? 48 }}</span>
                </div>
            </div>
            <div class="ap-kpi-bottom">
                <span class="ap-kpi-sub-text">En producción</span>
                <span class="ap-kpi-badge up">
                    <i class="fas fa-arrow-up"></i> 3 este mes
                </span>
            </div>
        </div>

        <!-- KPI 3: Producción de miel -->
        <div class="ap-kpi-card">
            <div class="ap-kpi-top">
                <div class="ap-kpi-icon kpi-icon-gold">
                    <i class="fas fa-jar"></i>
                </div>
                <div class="ap-kpi-info">
                    <span class="ap-kpi-title">Producción de miel</span>
                    <span class="ap-kpi-val">{{ $mielMes ?? 125 }} kg</span>
                </div>
            </div>
            <div class="ap-kpi-bottom">
                <span class="ap-kpi-sub-text">este mes</span>
                <span class="ap-kpi-badge up">
                    <i class="fas fa-arrow-up"></i> 12%
                </span>
            </div>
        </div>

        <!-- KPI 4: Inventario -->
        <div class="ap-kpi-card">
            <div class="ap-kpi-top">
                <div class="ap-kpi-icon kpi-icon-emerald">
                    <i class="fas fa-box-archive"></i>
                </div>
                <div class="ap-kpi-info">
                    <span class="ap-kpi-title">Inventario</span>
                    <span class="ap-kpi-val">{{ $stockBajo ?? 8 }}</span>
                </div>
            </div>
            <div class="ap-kpi-bottom">
                <span class="ap-kpi-sub-text">Productos con stock bajo</span>
                <span class="ap-kpi-badge down">
                    <i class="fas fa-arrow-down"></i> 2 este mes
                </span>
            </div>
        </div>
    </div>

    <!-- 3. CUERPO PRINCIPAL: GRÁFICA + ACCIONES RÁPIDAS Y ACTIVIDAD RECIENTE -->
    <div class="ap-dashboard-grid">

        <!-- Columna Izquierda: Gráfica + Acciones Rápidas -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">

            <!-- Gráfica de Producción de Miel -->
            <div class="ap-dash-card">
                <div class="ap-dash-card-header">
                    <div class="ap-dash-card-title">
                        <i class="fas fa-chart-column"></i>
                        <span>Producción de miel</span>
                    </div>
                    <a href="{{ route('apicola.admin.inspecciones.index') }}" class="ap-dash-header-link">
                        Últimos 6 meses <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <div style="position: relative; width: 100%; height: 210px;">
                    <canvas id="honeyChart" 
                            data-labels="{{ json_encode($chartLabels ?? ['Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep']) }}" 
                            data-values="{{ json_encode($chartData ?? [60, 84, 98, 115, 96, 135]) }}">
                    </canvas>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="ap-dash-card">
                <div class="ap-dash-card-header" style="margin-bottom: 1rem;">
                    <div class="ap-dash-card-title">
                        <i class="fas fa-leaf" style="color: #059669;"></i>
                        <span>Acciones rápidas</span>
                    </div>
                </div>

                <div class="ap-quick-actions-row">
                    <!-- 1. Registrar Apiario -->
                    <a href="{{ route('apicola.admin.apiarios.index') }}" class="ap-quick-action-btn">
                        <div class="ap-quick-action-icon">
                            <i class="fas fa-boxes-packing"></i>
                        </div>
                        <span class="ap-quick-action-label">Registrar apiario</span>
                    </a>

                    <!-- 2. Registrar Colmena -->
                    <a href="{{ route('apicola.admin.colmenas.index') }}" class="ap-quick-action-btn">
                        <div class="ap-quick-action-icon">
                            <i class="fas fa-cubes-stacked"></i>
                        </div>
                        <span class="ap-quick-action-label">Registrar colmena</span>
                    </a>

                    <!-- 3. Producción e Inspección -->
                    <a href="{{ route('apicola.admin.inspecciones.index') }}" class="ap-quick-action-btn">
                        <div class="ap-quick-action-icon">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <span class="ap-quick-action-label">Producción e inspección</span>
                    </a>

                    <!-- 4. Ver Inventario -->
                    <a href="{{ route('apicola.admin.inventario.index') }}" class="ap-quick-action-btn">
                        <div class="ap-quick-action-icon">
                            <i class="fas fa-box-archive"></i>
                        </div>
                        <span class="ap-quick-action-label">Ver inventario</span>
                    </a>
                </div>
            </div>

        </div>

        <!-- Columna Derecha: Actividad Reciente -->
        <div>
            <div class="ap-dash-card" style="height: 100%;">
                <div class="ap-dash-card-header">
                    <div class="ap-dash-card-title">
                        <i class="fas fa-file-lines" style="color: #059669;"></i>
                        <span>Actividad reciente</span>
                    </div>
                </div>

                <div class="ap-activity-list">
                    @forelse($actividades as $act)
                        <div class="ap-activity-item">
                            <div class="ap-activity-left">
                                <div class="ap-activity-badge" style="background: {{ $act['bg'] }}; color: {{ $act['color'] }};">
                                    <i class="{{ $act['icono'] }}"></i>
                                </div>
                                <div class="ap-activity-texts">
                                    <span class="ap-activity-title">{{ $act['titulo'] }}</span>
                                    <span class="ap-activity-detail">{{ $act['detalle'] }}</span>
                                </div>
                            </div>
                            <span class="ap-activity-time">{{ $act['tiempo'] }}</span>
                        </div>
                    @empty
                        <div style="text-align: center; color: #94a3b8; padding: 2rem 0;">
                            <i class="fas fa-clock-rotate-left" style="font-size: 2rem; margin-bottom: 0.5rem; opacity: 0.5;"></i>
                            <p style="font-size: 0.85rem;">No hay actividad reciente registrada.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

</div>
@endsection

@section('scripts')
<!-- Chart.js CDN + Script modular del Dashboard -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('modules/apicola/js/dashboard.js') }}"></script>
@endsection