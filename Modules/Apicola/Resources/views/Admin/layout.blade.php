<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel de Administración • SENA APÍCOLA')</title>
    <link rel="icon" type="image/png" href="{{ asset('modules/apicola/img/logo.png') }}">

    <!-- Google Fonts: Caveat (Slogan artesanal) + Outfit + Plus Jakarta Sans + Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@600;700&family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,500;1,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Leaflet CSS (Mapas interactivos de apiarios) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    @yield('styles')

    <style>
        :root {
            /* Tipografía de Lujo y Elegancia */
            --ap-font-main: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            --ap-font-display: 'Outfit', 'Plus Jakarta Sans', sans-serif;

            /* Paleta Premium Apícola & SENA */
            --ap-primary-dark: #071e18;       /* Medianoche bosque profundo */
            --ap-primary-forest: #0b2e23;     /* Bosque esmeralda oscuro */
            --ap-primary: #047857;            /* Verde SENA refinado */
            --ap-primary-light: #10b981;      /* Esmeralda vibrante */
            --ap-emerald-soft: #ecfdf5;       /* Menta suave */
            
            /* Tonos Miel, Polen y Ámbar (Esencia Apícola) */
            --ap-gold: #f59e0b;               /* Oro miel cálido */
            --ap-gold-light: #fbbf24;         /* Polen dorado */
            --ap-gold-dark: #b45309;          /* Ámbar profundo */
            --ap-gold-soft: #fffbeb;          /* Crema de miel sutil */

            /* Acentos Secundarios */
            --ap-teal: #0d9488;
            --ap-teal-soft: #f0fdfa;
            --ap-sky: #0284c7;
            --ap-sky-soft: #f0f9ff;
            --ap-rose: #e11d48;
            --ap-rose-soft: #fff1f2;

            /* Superficies y Neutros */
            --ap-bg: #f8fafc;                 /* Lienzo ultra limpio */
            --ap-card-bg: #ffffff;
            --ap-border: #e2e8f0;
            --ap-border-subtle: #f1f5f9;
            --ap-text-main: #0f172a;          /* Pizarra 900 contrastado */
            --ap-text-muted: #64748b;         /* Pizarra 500 elegante */
            --ap-text-subtle: #94a3b8;

            /* Sombras & Elevaciones */
            --ap-shadow-xs: 0 1px 2px rgba(15, 23, 42, 0.04);
            --ap-shadow-sm: 0 2px 5px rgba(15, 23, 42, 0.04), 0 1px 2px rgba(15, 23, 42, 0.02);
            --ap-shadow-md: 0 8px 20px -3px rgba(15, 23, 42, 0.07), 0 3px 8px -3px rgba(15, 23, 42, 0.03);
            --ap-shadow-lg: 0 18px 36px -6px rgba(15, 23, 42, 0.10), 0 8px 16px -6px rgba(15, 23, 42, 0.04);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--ap-font-main);
            background-color: var(--ap-bg);
            color: var(--ap-text-main);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
        }

        h1, h2, h3, h4, .ap-view-main-title, .ap-modal-title, .ap-brand-title {
            font-family: var(--ap-font-display);
            letter-spacing: -0.025em;
        }

        /* ================= BARRA LATERAL (SIDEBAR) ================= */
        .ap-sidebar {
            width: 270px;
            height: 100vh;
            background: linear-gradient(180deg, #071e18 0%, #0c2b22 50%, #081d17 100%);
            border-right: 1px solid rgba(255, 255, 255, 0.08);
            color: #ffffff;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 1000;
            box-shadow: 4px 0 28px rgba(0, 0, 0, 0.25);
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            user-select: none;
        }

        .ap-sidebar-header {
            padding: 1.5rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(0, 0, 0, 0.15);
            flex-shrink: 0;
        }

        .ap-logo-hex {
            width: 48px;
            height: 48px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: #ffffff;
            padding: 3px;
            border: 2px solid #f59e0b;
            box-shadow: 0 4px 14px rgba(245, 158, 11, 0.25);
            overflow: hidden;
        }

        .ap-logo-img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        .ap-brand-title {
            font-size: 1.12rem;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -0.2px;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .ap-brand-title span:last-child {
            color: #fbbf24;
        }

        .ap-brand-sub {
            font-size: 0.68rem;
            color: rgba(255, 255, 255, 0.65);
            letter-spacing: 1px;
            margin-top: 3px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .ap-nav-list {
            list-style: none;
            padding: 1.25rem 0.85rem;
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .ap-nav-item {
            border-radius: 10px;
            transition: all 0.2s ease;
        }

        .ap-nav-link {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.75);
            font-size: 0.88rem;
            font-weight: 500;
            text-align: left;
            cursor: pointer;
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .ap-nav-link:hover {
            background: rgba(255, 255, 255, 0.08);
            color: #ffffff;
            transform: translateX(3px);
        }

        .ap-nav-item.active > .ap-nav-link {
            background: linear-gradient(90deg, rgba(245, 158, 11, 0.18) 0%, rgba(16, 185, 129, 0.10) 100%);
            color: #ffffff;
            font-weight: 700;
            border-left: 3px solid #f59e0b;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
        }

        .ap-nav-icon {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: #fbbf24;
            flex-shrink: 0;
            transition: transform 0.2s ease;
        }

        .ap-nav-link:hover .ap-nav-icon {
            transform: scale(1.1);
        }

        .ap-sub-list {
            list-style: none;
            padding: 3px 0 4px 0;
            margin: 3px 0 4px 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 2px;
            border-left: 2px solid rgba(245, 158, 11, 0.28);
            padding-left: 0.75rem;
        }

        .ap-sub-link {
            font-size: 0.82rem;
            color: rgba(255, 255, 255, 0.75);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 7px 12px;
            border-radius: 8px;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            font-weight: 500;
        }

        .ap-sub-link:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.08);
            transform: translateX(3px);
        }

        .ap-sub-link.active-sub {
            color: #fbbf24;
            font-weight: 700;
            background: rgba(245, 158, 11, 0.16);
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.12);
        }

        .ap-sub-link.active-sub i {
            color: #fbbf24;
        }

        .ap-sidebar-user {
            padding: 1.1rem 1.25rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(0, 0, 0, 0.28);
            flex-shrink: 0;
        }

        .ap-user-left {
            display: flex;
            align-items: center;
            gap: 10px;
            overflow: hidden;
        }

        .ap-avatar-circle {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            border: 1.5px solid rgba(255, 255, 255, 0.25);
            color: #ffffff;
            font-weight: 800;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        }

        .ap-user-details {
            overflow: hidden;
        }

        .ap-user-title {
            font-size: 0.84rem;
            font-weight: 700;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #ffffff;
        }

        .ap-user-sub {
            font-size: 0.7rem;
            color: rgba(255, 255, 255, 0.65);
            margin-top: 2px;
        }

        .ap-btn-exit {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.85);
            width: 34px;
            height: 34px;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .ap-btn-exit:hover {
            background: #ef4444;
            border-color: #ef4444;
            color: #ffffff;
            transform: scale(1.05);
        }

        /* ================= CONTENIDO PRINCIPAL ================= */
        .ap-main-area {
            margin-left: 270px;
            flex: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            width: calc(100% - 270px);
            background: var(--ap-bg);
        }

        .ap-top-nav {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border-bottom: 1px solid var(--ap-border);
            padding: 0.85rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
        }

        .ap-top-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .ap-toggle-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.25rem;
            color: var(--ap-text-main);
            cursor: pointer;
        }

        .ap-bread {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.84rem;
            color: var(--ap-text-muted);
            font-weight: 500;
            flex-wrap: wrap;
        }

        .ap-bread a {
            color: var(--ap-text-muted);
            text-decoration: none;
            transition: color 0.15s ease;
        }

        .ap-bread a:hover {
            color: var(--ap-primary);
        }

        .ap-bread .active-page {
            color: var(--ap-primary);
            font-weight: 700;
        }

        .ap-top-right {
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }

        .ap-status-tag {
            background: #fffbeb;
            color: #92400e;
            border: 1px solid #fde68a;
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 1px 2px rgba(245, 158, 11, 0.08);
        }

        .ap-status-tag .dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background-color: #f59e0b;
            box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.35);
            animation: apPulseDot 2s infinite ease-in-out;
        }

        @keyframes apPulseDot {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.3); opacity: 0.7; }
        }

        .ap-link-portal {
            background: #ffffff;
            color: #334155;
            border: 1px solid #cbd5e1;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.82rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .ap-link-portal:hover {
            background: #f8fafc;
            color: var(--ap-primary);
            border-color: var(--ap-primary-light);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
            transform: translateY(-1px);
        }

        /* Barra superior estilo ApiGestión */
        .ap-nav-search-wrap {
            position: relative;
            min-width: 260px;
            max-width: 380px;
            width: 100%;
        }

        .ap-nav-search-wrap input {
            width: 100%;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            padding: 8px 16px 8px 38px;
            font-size: 0.88rem;
            color: #1e293b;
            outline: none;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .ap-nav-search-wrap input:focus {
            background: #ffffff;
            border-color: #059669;
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.12);
        }

        .ap-nav-search-wrap i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 0.85rem;
        }

        .ap-nav-bell-btn {
            position: relative;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #475569;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .ap-nav-bell-btn:hover {
            background: #f8fafc;
            color: #059669;
            border-color: #cbd5e1;
        }

        .ap-nav-bell-badge {
            position: absolute;
            top: 7px;
            right: 7px;
            width: 8px;
            height: 8px;
            background: #10b981;
            border: 1.5px solid #ffffff;
            border-radius: 50%;
        }

        .ap-nav-profile-pill {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 4px 12px 4px 6px;
            border-radius: 30px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            color: inherit;
        }

        .ap-nav-profile-pill:hover {
            border-color: #cbd5e1;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
        }

        .ap-nav-profile-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #1e3d2f;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem;
            flex-shrink: 0;
            overflow: hidden;
        }

        .ap-nav-profile-meta {
            display: flex;
            flex-direction: column;
            text-align: left;
            line-height: 1.15;
        }

        .ap-nav-profile-name {
            font-size: 0.85rem;
            font-weight: 700;
            color: #0f172a;
        }

        .ap-nav-profile-role {
            font-size: 0.72rem;
            color: #64748b;
        }

        /* Slogan de apicultura sostenible en el pie del sidebar */
        .ap-sidebar-slogan-box {
            padding: 1.5rem 1.25rem;
            margin-top: auto;
            position: relative;
            text-align: center;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            background: rgba(0, 0, 0, 0.12);
        }

        .ap-slogan-handwritten {
            font-family: 'Caveat', cursive;
            font-size: 1.18rem;
            font-weight: 600;
            color: #fef08a;
            display: block;
            line-height: 1.25;
            transform: rotate(-3deg);
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
        }

        .ap-slogan-bee-deco {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 6px;
            color: #fbbf24;
            font-size: 0.9rem;
            opacity: 0.75;
        }

        .ap-inner-content {
            padding: 2.25rem 2.5rem;
            flex: 1;
            max-width: 1440px;
            width: 100%;
            margin: 0 auto;
            animation: apModuleEntrance 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        @keyframes apModuleEntrance {
            0% {
                opacity: 0;
                transform: translateY(16px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ================= ESTILOS COMPARTIDOS ================= */
        .ap-view-header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1.25rem;
        }

        .ap-view-subtitle-tag {
            font-size: 0.72rem;
            font-weight: 800;
            color: #047857;
            background: rgba(4, 120, 87, 0.08);
            padding: 4px 10px;
            border-radius: 6px;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            display: inline-block;
            margin-bottom: 6px;
        }

        .ap-view-main-title {
            font-size: 2rem;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: -0.6px;
            line-height: 1.15;
        }

        .ap-user-chip {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #ffffff;
            padding: 6px 16px 6px 8px;
            border-radius: 50px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
        }

        .ap-user-chip-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #065f46 0%, #047857 100%);
            border: 2px solid #ffffff;
            color: #ffffff;
            font-size: 0.88rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 6px rgba(4, 120, 87, 0.25);
        }

        .ap-user-chip-info {
            display: flex;
            flex-direction: column;
        }

        .ap-user-chip-name {
            font-size: 0.88rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.2;
        }

        .ap-user-chip-role {
            font-size: 0.74rem;
            color: #64748b;
        }

        /* Tarjetas de Métricas Ejecutivas */
        .ap-metrics-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.35rem;
            margin-bottom: 2.25rem;
        }

        .ap-metrics-grid.cols-3 {
            grid-template-columns: repeat(3, 1fr);
        }

        .ap-metric-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 1.4rem 1.5rem;
            box-shadow: 0 2px 5px rgba(15, 23, 42, 0.03);
            position: relative;
            overflow: hidden;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .ap-metric-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            opacity: 0.95;
            transition: height 0.2s ease;
        }

        .ap-metric-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 26px -4px rgba(15, 23, 42, 0.08), 0 4px 10px -2px rgba(15, 23, 42, 0.03);
            border-color: #cbd5e1;
        }

        .ap-metric-card:hover::before {
            height: 4px;
        }

        .ap-metric-card.border-green::before { background: linear-gradient(90deg, #059669, #34d399); }
        .ap-metric-card.border-teal::before { background: linear-gradient(90deg, #0d9488, #2dd4bf); }
        .ap-metric-card.border-amber::before { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
        .ap-metric-card.border-red::before { background: linear-gradient(90deg, #ef4444, #f87171); }

        .ap-metric-card-title {
            font-size: 0.72rem;
            font-weight: 800;
            color: #64748b;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        .ap-metric-card-num {
            font-size: 2.25rem;
            font-weight: 900;
            color: #0f172a;
            line-height: 1.05;
            margin-bottom: 0.45rem;
            letter-spacing: -0.02em;
        }

        .ap-metric-card-sub {
            font-size: 0.8rem;
            color: #94a3b8;
            font-weight: 500;
        }

        /* Tarjetas de Módulos / Features */
        .ap-feature-card {
            background: #ffffff;
            border: 1px solid var(--ap-border);
            border-radius: 18px;
            padding: 1.6rem 1.6rem 1.4rem;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
            transition: all 0.28s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .ap-feature-card::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: transparent;
            transition: all 0.25s ease;
        }

        .ap-feature-card:hover {
            transform: translateY(-4px);
            border-color: #cbd5e1;
            box-shadow: 0 16px 32px -4px rgba(15, 23, 42, 0.08), 0 6px 12px -2px rgba(15, 23, 42, 0.03);
        }

        .ap-feature-card.card-emerald:hover::after { background: linear-gradient(90deg, #059669, #34d399); }
        .ap-feature-card.card-amber:hover::after { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
        .ap-feature-card.card-sky:hover::after { background: linear-gradient(90deg, #0284c7, #38bdf8); }
        .ap-feature-card.card-teal:hover::after { background: linear-gradient(90deg, #0d9488, #2dd4bf); }
        .ap-feature-card.card-purple:hover::after { background: linear-gradient(90deg, #7c3aed, #a78bfa); }

        .ap-feature-card-icon-box {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .ap-feature-card:hover .ap-feature-card-icon-box {
            transform: scale(1.08) rotate(3deg);
        }

        .ap-feature-chip {
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.8px;
            padding: 4px 10px;
            border-radius: 6px;
            text-transform: uppercase;
        }

        .ap-feature-btn-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 700;
            font-size: 0.88rem;
            margin-top: 1.25rem;
            text-decoration: none;
            transition: gap 0.2s ease;
        }

        .ap-feature-card:hover .ap-feature-btn-link i {
            transform: translateX(4px);
        }

        .ap-feature-btn-link i {
            transition: transform 0.2s ease;
        }

        /* Sección del Listado */
        .ap-list-top-bar {
            margin-bottom: 1rem;
        }

        .ap-list-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 2px;
        }

        .ap-list-desc {
            font-size: 0.84rem;
            color: #64748b;
        }

        .ap-controls-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .ap-controls-left {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
            flex-wrap: wrap;
        }

        .ap-search-wrapper {
            position: relative;
            flex: 1;
            min-width: 200px;
        }

        .ap-search-wrapper input {
            width: 100%;
            padding: 8px 14px 8px 36px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            font-size: 0.85rem;
            color: #0f172a;
            outline: none;
            transition: all 0.2s;
        }

        .ap-search-wrapper input:focus {
            border-color: #059669;
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.15);
        }

        .ap-search-wrapper i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 0.85rem;
        }

        .ap-filter-select {
            padding: 8px 14px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            font-size: 0.85rem;
            color: #334155;
            outline: none;
            cursor: pointer;
        }

        .ap-filter-select:focus {
            border-color: #059669;
        }

        .ap-date-input {
            padding: 7px 12px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            font-size: 0.85rem;
            color: #334155;
            outline: none;
        }

        .ap-date-input:focus {
            border-color: #059669;
        }

        .ap-controls-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .ap-btn-outline-green {
            background: #ffffff;
            color: #047857;
            border: 1.5px solid #059669;
            padding: 8px 18px;
            border-radius: 10px;
            font-size: 0.86rem;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            text-decoration: none;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        }

        .ap-btn-outline-green:hover {
            background: #ecfdf5;
            border-color: #047857;
            color: #064e3b;
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(4, 120, 87, 0.12);
        }

        .ap-btn-solid-green {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: #ffffff;
            border: none;
            padding: 9px 20px;
            border-radius: 10px;
            font-size: 0.86rem;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            text-decoration: none;
        }

        .ap-btn-solid-green:hover {
            background: linear-gradient(135deg, #047857 0%, #064e3b 100%);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(5, 150, 105, 0.35);
        }

        /* Tablas */
        .ap-table-card {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .ap-table-scroll {
            width: 100%;
            overflow-x: auto;
        }

        .ap-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.88rem;
        }

        .ap-table thead {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }

        .ap-table th {
            padding: 1rem 1.25rem;
            font-size: 0.74rem;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .ap-table td {
            padding: 1.1rem 1.25rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            color: #334155;
        }

        .ap-table tbody tr {
            transition: background-color 0.15s ease;
        }

        .ap-table tbody tr:hover {
            background-color: #f8fafc;
        }

        .ap-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .ap-status-badge.activo, .ap-status-badge.activa, .ap-status-badge.bueno {
            background: #ecfdf5;
            color: #059669;
        }

        .ap-status-badge.revision, .ap-status-badge.en-revision, .ap-status-badge.en-revisión, .ap-status-badge.regular {
            background: #fef3c7;
            color: #d97706;
        }

        .ap-status-badge.inactivo, .ap-status-badge.inactiva, .ap-status-badge.malo, .ap-status-badge.alerta {
            background: #fee2e2;
            color: #dc2626;
        }

        .ap-actions-group {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .ap-action-btn {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 0.84rem;
            font-weight: 600;
            padding: 0;
            text-decoration: none;
            transition: opacity 0.2s;
        }

        .ap-action-btn:hover {
            text-decoration: underline;
        }

        .ap-action-btn.view {
            color: #059669;
        }

        .ap-action-btn.edit {
            color: #475569;
        }

        .ap-action-btn.deactivate {
            color: #ef4444;
        }

        .ap-action-btn.activate {
            color: #059669;
        }

        .ap-table-footer-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.25rem;
            font-size: 0.8rem;
            color: #94a3b8;
            border-top: 1px solid #f1f5f9;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        /* Modales */
        .ap-modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(4px);
            z-index: 2000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .ap-modal-backdrop.is-open {
            display: flex;
        }

        .ap-modal-window {
            background: #ffffff;
            border-radius: 20px;
            width: 100%;
            max-width: 520px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            max-height: 92vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            animation: apModalFadeIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .ap-modal-window.large {
            max-width: 780px;
        }

        @keyframes apModalFadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        .ap-modal-body {
            padding: 1.75rem 2rem 1.25rem;
            overflow-y: auto;
            flex: 1;
        }

        .ap-modal-title {
            font-size: 1.5rem;
            font-weight: 900;
            color: #0f172a;
            margin-bottom: 1.25rem;
        }

        .ap-modal-close-btn {
            background: transparent;
            border: none;
            font-size: 1.35rem;
            color: #94a3b8;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 8px;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s ease;
        }

        .ap-modal-close-btn:hover {
            color: #ef4444;
            background: #fee2e2;
        }

        .ap-form-section-title {
            font-size: 0.8rem;
            font-weight: 800;
            color: #059669;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin-top: 1.2rem;
            margin-bottom: 0.8rem;
            padding-bottom: 4px;
            border-bottom: 1px solid #f1f5f9;
        }

        .ap-form-field {
            margin-bottom: 1.2rem;
        }

        .ap-field-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 800;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin-bottom: 0.4rem;
        }

        .ap-field-hint {
            display: block;
            font-size: 0.78rem;
            color: #94a3b8;
            margin-top: 5px;
            line-height: 1.35;
        }

        .ap-field-input {
            width: 100%;
            padding: 10px 14px;
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            font-size: 0.9rem;
            color: #0f172a;
            outline: none;
            transition: all 0.2s;
        }

        .ap-field-input:focus {
            border-color: #059669;
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.15);
        }

        .ap-coords-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .ap-coords-row.cols-3 {
            grid-template-columns: repeat(3, 1fr);
        }

        /* Selector de 3 Estados (Pills) */
        .ap-status-pill-group {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .ap-status-pill-btn {
            padding: 10px 8px;
            border-radius: 10px;
            border: 1.5px solid #cbd5e1;
            background: #ffffff;
            color: #64748b;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            transition: all 0.2s;
        }

        .ap-status-pill-btn:hover {
            border-color: #94a3b8;
        }

        .ap-status-pill-btn.selected-activa, .ap-status-pill-btn.selected-bueno {
            background: #e8f5e9;
            border-color: #2e7d32;
            color: #1b5e20;
            font-weight: 700;
        }

        .ap-status-pill-btn.selected-revision, .ap-status-pill-btn.selected-regular {
            background: #fef3c7;
            border-color: #d97706;
            color: #b45309;
            font-weight: 700;
        }

        .ap-status-pill-btn.selected-inactiva, .ap-status-pill-btn.selected-malo {
            background: #fee2e2;
            border-color: #dc2626;
            color: #b91c1c;
            font-weight: 700;
        }

        /* Checkbox Cards para Reina, Enfermedades y Labores */
        .ap-checkbox-card-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .ap-checkbox-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 10px;
            border: 1.5px solid #cbd5e1;
            background: #ffffff;
            color: #334155;
            font-size: 0.88rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.15s;
            user-select: none;
        }

        .ap-checkbox-card:hover {
            border-color: #94a3b8;
        }

        .ap-checkbox-card.is-checked {
            background: #e8f5e9;
            border-color: #2e7d32;
            color: #1b5e20;
            font-weight: 700;
        }

        .ap-checkbox-card input[type="checkbox"] {
            accent-color: #059669;
            width: 17px;
            height: 17px;
            cursor: pointer;
        }

        .ap-btn-add-mini {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            background: #059669;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s ease, transform 0.1s ease;
            white-space: nowrap;
        }

        .ap-btn-add-mini:hover {
            background: #047857;
            transform: translateY(-1px);
        }

        .ap-btn-remove-card {
            background: transparent;
            border: none;
            color: #94a3b8;
            font-size: 1.15rem;
            line-height: 1;
            cursor: pointer;
            padding: 2px 6px;
            border-radius: 4px;
            transition: color 0.15s, background 0.15s;
            margin-left: auto;
        }

        .ap-btn-remove-card:hover {
            color: #ef4444;
            background: #fee2e2;
        }

        .ap-add-item-row {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .ap-modal-footer {
            padding: 1rem 2rem 1.5rem;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            background: #ffffff;
            border-top: 1px solid #f1f5f9;
        }

        .ap-btn-modal-cancel {
            background: #f1f5f9;
            color: #475569;
            border: none;
            padding: 10px 22px;
            border-radius: 10px;
            font-size: 0.88rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
        }

        .ap-btn-modal-cancel:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        .ap-btn-modal-save {
            background: #0e7452;
            color: #ffffff;
            border: none;
            padding: 10px 24px;
            border-radius: 10px;
            font-size: 0.88rem;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(14, 116, 82, 0.3);
            transition: all 0.2s;
        }

        .ap-btn-modal-save:hover {
            background: #08563e;
        }

        /* Toast Alert */
        .ap-toast-alert {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: #065f46;
            color: #ffffff;
            padding: 12px 20px;
            border-radius: 12px;
            font-size: 0.88rem;
            font-weight: 600;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 3000;
            opacity: 0;
            transform: translateY(20px);
            pointer-events: none;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .ap-toast-alert.show {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }

        @media (max-width: 992px) {
            .ap-sidebar {
                transform: translateX(-100%);
            }
            .ap-sidebar.open-sidebar {
                transform: translateX(0);
            }
            .ap-main-area {
                margin-left: 0;
                width: 100%;
            }
            .ap-toggle-btn {
                display: block;
            }
            .ap-inner-content {
                padding: 1.25rem;
            }
            .ap-metrics-grid, .ap-metrics-grid.cols-3 {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 640px) {
            .ap-metrics-grid, .ap-metrics-grid.cols-3 {
                grid-template-columns: 1fr;
            }
            .ap-coords-row, .ap-coords-row.cols-3, .ap-checkbox-card-group {
                grid-template-columns: 1fr;
            }
        }
    </style>

    @yield('styles')
</head>
<body>

    <!-- ================= BARRA LATERAL (SIDEBAR) ================= -->
    <aside class="ap-sidebar" id="apSidebar">
        
        <!-- Encabezado del Logo SENA APÍCOLA -->
        <div class="ap-sidebar-header" style="gap: 14px; padding: 1.35rem 1.25rem;">
            <div class="ap-logo-hex" style="border-radius: 14px; border: 2px solid #fbbf24; background: #ffffff;">
                <img src="{{ asset('modules/apicola/img/bee_mascot.jpg') }}" 
                     alt="SENA Apícola" 
                     class="ap-logo-img"
                     onerror="this.onerror=null; this.src='{{ asset('modules/apicola/img/logo.png') }}';">
            </div>
            <div class="ap-brand-info">
                <div class="ap-brand-title" style="font-size: 1.18rem; font-weight: 800; letter-spacing: -0.3px;">
                    <span style="color: #ffffff;">SENA </span><span style="color: #fbbf24;">APÍCOLA</span>
                </div>
                <div class="ap-brand-sub" style="font-size: 0.72rem; color: #a7f3d0; letter-spacing: 0.5px; font-weight: 600;">SENA - LA ANGOSTURA</div>
            </div>
        </div>

        <!-- Menú de Navegación -->
        <ul class="ap-nav-list" id="apNavList">

            <!-- 1. Inicio -->
            <li class="ap-nav-item {{ request()->routeIs('apicola.admin.dashboard') || request()->routeIs('apicola.dashboard') ? 'active' : '' }}">
                <a href="{{ route('apicola.admin.dashboard') }}" class="ap-nav-link">
                    <span class="ap-nav-icon">
                        <i class="fas fa-house"></i>
                    </span>
                    <span>Inicio</span>
                </a>
            </li>

            <!-- 2. Usuarios -->
            <li class="ap-nav-item {{ request()->routeIs('apicola.admin.usuarios.*') ? 'active' : '' }}">
                <a href="{{ route('apicola.admin.usuarios.index') }}" class="ap-nav-link">
                    <span class="ap-nav-icon">
                        <i class="far fa-user"></i>
                    </span>
                    <span>Usuarios</span>
                </a>
            </li>

            <!-- 3. Apiarios -->
            <li class="ap-nav-item {{ request()->routeIs('apicola.admin.apiarios.*') ? 'active' : '' }}">
                <a href="{{ route('apicola.admin.apiarios.index') }}" class="ap-nav-link">
                    <span class="ap-nav-icon">
                        <i class="fas fa-location-dot"></i>
                    </span>
                    <span>Apiarios</span>
                </a>
            </li>

            @php
                $isInspeccionRoute = request()->routeIs('apicola.admin.inspecciones.*') 
                    || request()->routeIs('apicola.admin.colmenas.inspecciones')
                    || request()->is('*inspeccion*');
                $isColmenaDirect = (request()->routeIs('apicola.colmenas.*') || request()->routeIs('apicola.admin.colmenas.*') || request()->is('apicola/colmenas*')) && !$isInspeccionRoute;
            @endphp
            <!-- 4. Módulo: Colmenas -->
            <li class="ap-nav-item {{ $isColmenaDirect ? 'active' : '' }}">
                <a href="{{ route('apicola.admin.colmenas.index') }}" class="ap-nav-link">
                    <span class="ap-nav-icon">
                        <i class="fas fa-boxes-stacked"></i>
                    </span>
                    <span>Colmenas</span>
                </a>
                <!-- Submódulo: Producción e inspección -->
                <ul class="ap-sub-list">
                    <li>
                        <a href="{{ route('apicola.admin.inspecciones.index') }}" 
                           class="ap-sub-link {{ $isInspeccionRoute ? 'active-sub' : '' }}">
                            <i class="fas fa-clipboard-check" style="font-size: 0.78rem;"></i>
                            <span>Producción e inspección</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- 5. Inventario -->
            <li class="ap-nav-item {{ request()->routeIs('apicola.admin.inventario.*') ? 'active' : '' }}">
                <a href="{{ route('apicola.admin.inventario.index') }}" class="ap-nav-link">
                    <span class="ap-nav-icon">
                        <i class="fas fa-box-archive"></i>
                    </span>
                    <span>Inventario</span>
                </a>
            </li>

            <!-- 6. Calendario Floral -->
            <li class="ap-nav-item {{ request()->routeIs('apicola.admin.calendario.*') ? 'active' : '' }}">
                <a href="{{ route('apicola.admin.calendario.index') }}" class="ap-nav-link">
                    <span class="ap-nav-icon">
                        <i class="fas fa-calendar-days"></i>
                    </span>
                    <span>Calendario floral</span>
                </a>
            </li>

            <!-- 7. Reportes -->
            <li class="ap-nav-item {{ request()->routeIs('apicola.admin.reportes.*') ? 'active' : '' }}">
                <a href="{{ route('apicola.admin.reportes.index') }}" class="ap-nav-link">
                    <span class="ap-nav-icon">
                        <i class="fas fa-chart-column"></i>
                    </span>
                    <span>Reportes</span>
                </a>
            </li>
        </ul>

        <!-- Slogan Sostenible Artístico en el pie del Sidebar -->
        <div class="ap-sidebar-slogan-box">
            <span class="ap-slogan-handwritten">Juntos por una apicultura sostenible</span>
            <div class="ap-slogan-bee-deco">
                <i class="fas fa-seedling"></i>
                <span style="font-size: 0.75rem; letter-spacing: 1px;">SENA CEFA</span>
                <i class="fas fa-feather-pointed"></i>
            </div>
        </div>

        <!-- Pie de Usuario (Conectado al fondo) -->
        <div class="ap-sidebar-user">
            <div class="ap-user-left">
                <div class="ap-avatar-circle">
                    {{ Auth::user()?->initials ?? strtoupper(substr(Auth::user()?->nickname ?? 'A', 0, 2)) }}
                </div>
                <div class="ap-user-details">
                    <div class="ap-user-title" title="{{ Auth::user()?->full_name ?? (Auth::user()?->nickname ?? 'Administrador') }}">
                        {{ Auth::user()?->full_name ?? (Auth::user()?->nickname ?? 'Karen Michelle Plazas') }}
                    </div>
                    <div class="ap-user-sub">
                        <i class="fas fa-shield-alt" style="color: #34d399;"></i> {{ Auth::user()?->primary_role ?? 'Administrador Apícola' }}
                    </div>
                </div>
            </div>
            <a href="{{ route('logout', ['redirect' => route('apicola.welcome')]) }}" class="ap-btn-exit" title="Cerrar sesión">
                <i class="fas fa-arrow-right-from-bracket"></i>
            </a>
        </div>
    </aside>

    <!-- ================= CONTENIDO PRINCIPAL ================= -->
    <div class="ap-main-area">

        <!-- Barra Superior ApiGestión -->
        <header class="ap-top-nav">
            <div class="ap-top-left" style="gap: 1.25rem;">
                <button class="ap-toggle-btn" onclick="toggleSidebarMenu()" style="display:inline-flex;">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="ap-nav-search-wrap">
                    <i class="fas fa-magnifying-glass"></i>
                    <input type="text" id="apGlobalSearch" placeholder="Buscar apiarios, colmenas, registros...">
                </div>
            </div>

            <div class="ap-top-right" style="gap: 1.15rem;">
                <!-- Perfil del Administrador -->
                <div class="ap-nav-profile-pill" title="Cuenta activa">
                    <div class="ap-nav-profile-avatar">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="ap-nav-profile-meta">
                        <span class="ap-nav-profile-name">
                            {{ Auth::user()?->full_name ?? (Auth::user()?->nickname ?? 'Karen Michelle Plazas') }}
                        </span>
                        <span class="ap-nav-profile-role">
                            {{ Auth::user()?->primary_role ?? 'Administrador' }}
                        </span>
                    </div>
                    <i class="fas fa-chevron-down" style="font-size: 0.72rem; color: #94a3b8; margin-left: 2px;"></i>
                </div>
            </div>
        </header>

        <!-- Contenido Específico de cada Vista -->
        <main class="ap-inner-content">
            @yield('content')
        </main>
    </div>

    <!-- Modales inyectados por cada submódulo -->
    @yield('modals')

    <!-- Toast Alert Flotante -->
    <div class="ap-toast-alert" id="apToast">
        <i class="fas fa-check-circle" style="color: #34d399; font-size: 1.15rem;"></i>
        <span id="apToastText">Acción completada con éxito</span>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- Scripts Globales -->
    <script>
        function showToast(message) {
            const toast = document.getElementById('apToast');
            const toastText = document.getElementById('apToastText');
            if (!toast || !toastText) return;
            toastText.textContent = message;
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        function toggleSidebarMenu() {
            document.getElementById('apSidebar').classList.toggle('open-sidebar');
        }

        function toggleSidebarSubmenu(id, el) {
            const parent = el.closest('.ap-nav-item');
            if (!parent) return;
            parent.classList.toggle('open');
        }
    </script>

    @yield('scripts')
</body>
</html>
