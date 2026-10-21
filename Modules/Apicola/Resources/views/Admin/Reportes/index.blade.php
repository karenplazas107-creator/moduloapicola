@extends('apicola::Admin.layout')

@section('title', 'Generador de Reportes • SENA APÍCOLA')

@section('breadcrumb')
    <a href="{{ route('apicola.admin.dashboard') }}">Inicio</a>
    <span>/</span>
    <span class="active-page">Generador de reportes</span>
@endsection

@section('styles')
<style>
    /* ================= ESTILOS GENERADOR DE REPORTES ================= */
    .rep-container {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        animation: repFadeIn 0.35s ease-out;
    }

    @keyframes repFadeIn {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* 1. Header principal con chip de usuario */
    .rep-header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.5rem;
        flex-wrap: wrap;
    }

    .rep-header-tag {
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 1.5px;
        color: #059669;
        text-transform: uppercase;
        margin-bottom: 2px;
        display: block;
    }

    .rep-header-title {
        font-size: 1.75rem;
        font-weight: 900;
        color: #0f172a;
        margin: 0;
        letter-spacing: -0.5px;
    }

    .rep-user-chip {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: #ffffff;
        padding: 6px 14px 6px 8px;
        border-radius: 9999px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }

    .rep-user-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #047857;
        color: #ffffff;
        font-weight: 800;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .rep-user-info {
        display: flex;
        flex-direction: column;
        line-height: 1.2;
    }

    .rep-user-name {
        font-size: 0.88rem;
        font-weight: 700;
        color: #0f172a;
    }

    .rep-user-role {
        font-size: 0.72rem;
        color: #64748b;
        font-weight: 500;
    }

    /* 2. Layout principal de dos columnas */
    .rep-grid-layout {
        display: grid;
        grid-template-columns: 360px 1fr;
        gap: 1.5rem;
        align-items: start;
    }

    @media (max-width: 1080px) {
        .rep-grid-layout {
            grid-template-columns: 1fr;
        }
    }

    /* 3. Columna Izquierda: Tarjeta de Filtros y Configuración */
    .rep-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
    }

    .rep-card-title {
        font-size: 1.25rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 4px 0;
    }

    .rep-card-desc {
        font-size: 0.84rem;
        color: #64748b;
        margin: 0 0 1.25rem 0;
    }

    .rep-section-label {
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.8px;
        color: #64748b;
        text-transform: uppercase;
        margin-bottom: 0.65rem;
        display: block;
    }

    /* Selector de Tipo de Reporte (3 Tarjetas interactivas) */
    .rep-type-options {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        margin-bottom: 1.35rem;
    }

    .rep-type-card {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 0.95rem 1rem;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        background: #ffffff;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        user-select: none;
    }

    .rep-type-card:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
        transform: translateY(-1px);
    }

    .rep-type-card.active {
        border-color: #10b981;
        background: #f0fdf4;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.12);
    }

    .rep-type-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
        color: #64748b;
        background: #f1f5f9;
        transition: all 0.2s ease;
    }

    .rep-type-card.active .rep-type-icon {
        color: #059669;
        background: #dcfce7;
    }

    .rep-type-body {
        display: flex;
        flex-direction: column;
        line-height: 1.25;
    }

    .rep-type-name {
        font-size: 0.92rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 3px;
    }

    .rep-type-card.active .rep-type-name {
        color: #065f46;
    }

    .rep-type-desc {
        font-size: 0.76rem;
        color: #64748b;
    }

    .rep-type-card.active .rep-type-desc {
        color: #047857;
    }

    /* Formularios de Filtro */
    .rep-form-group {
        margin-bottom: 1.1rem;
    }

    .rep-form-label {
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.8px;
        color: #64748b;
        text-transform: uppercase;
        margin-bottom: 0.45rem;
        display: block;
    }

    .rep-select, .rep-input {
        width: 100%;
        padding: 0.65rem 0.85rem;
        border: 1px solid #cbd5e1;
        border-radius: 9px;
        font-size: 0.88rem;
        color: #1e293b;
        background-color: #ffffff;
        outline: none;
        transition: all 0.2s ease;
    }

    .rep-select:focus, .rep-input:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
    }

    .rep-date-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
    }

    .rep-date-wrap {
        position: relative;
    }

    .rep-date-wrap input {
        width: 100%;
        padding: 0.65rem 0.75rem;
        border: 1px solid #cbd5e1;
        border-radius: 9px;
        font-size: 0.85rem;
        color: #1e293b;
        background: #ffffff;
        outline: none;
    }

    .rep-date-wrap input:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
    }

    /* Botón Principal Generar / Vista Previa */
    .rep-btn-submit {
        width: 100%;
        padding: 0.82rem 1.25rem;
        background: #059669;
        color: #ffffff;
        font-size: 0.95rem;
        font-weight: 700;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25);
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        margin-top: 0.5rem;
    }

    .rep-btn-submit:hover {
        background: #047857;
        box-shadow: 0 6px 16px rgba(5, 150, 105, 0.32);
        transform: translateY(-1px);
    }

    .rep-btn-submit:active {
        transform: translateY(0);
    }

    /* 4. Columna Derecha: Métricas Superiores */
    .rep-right-column {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .rep-metrics-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.25rem;
    }

    @media (max-width: 820px) {
        .rep-metrics-row {
            grid-template-columns: 1fr;
        }
    }

    .rep-metric-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 1.25rem 1.35rem;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
        position: relative;
        overflow: hidden;
        transition: all 0.2s ease;
    }

    .rep-metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.06);
    }

    .rep-metric-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        width: 4.5px;
    }

    .rep-metric-card.border-amber::before {
        background: #f59e0b;
    }

    .rep-metric-card.border-emerald::before {
        background: #10b981;
    }

    .rep-metric-card.border-darkgreen::before {
        background: #047857;
    }

    .rep-metric-label {
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.8px;
        color: #64748b;
        text-transform: uppercase;
        margin-bottom: 0.4rem;
    }

    .rep-metric-value {
        font-size: 2.1rem;
        font-weight: 900;
        color: #0f172a;
        line-height: 1.1;
        letter-spacing: -0.5px;
        margin-bottom: 0.35rem;
    }

    .rep-metric-sub {
        font-size: 0.78rem;
        color: #64748b;
        font-weight: 500;
    }

    /* 5. Tarjeta de Detalle del Reporte y Chips de Columnas */
    .rep-detail-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.65rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
    }

    .rep-detail-header {
        margin-bottom: 1.5rem;
    }

    .rep-detail-title {
        font-size: 1.45rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 4px 0;
    }

    .rep-detail-subtitle {
        font-size: 0.88rem;
        color: #64748b;
        margin: 0;
    }

    /* Chips de Columnas Incluidas */
    .rep-columns-box {
        margin-bottom: 2rem;
    }

    .rep-chips-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 0.5rem;
    }

    .rep-column-chip {
        display: inline-flex;
        align-items: center;
        background: #dcfce7;
        color: #15803d;
        font-size: 0.8rem;
        font-weight: 700;
        padding: 4px 12px;
        border-radius: 9999px;
        border: 1px solid #bbf7d0;
        letter-spacing: 0.2px;
    }

    /* Lista de Últimos Reportes */
    .rep-history-box {
        margin-top: 1rem;
    }

    .rep-history-list {
        display: flex;
        flex-direction: column;
        border-top: 1px solid #f1f5f9;
        margin-top: 0.75rem;
    }

    .rep-history-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 0;
        border-bottom: 1px solid #f1f5f9;
        gap: 1rem;
        transition: background 0.15s ease;
    }

    .rep-history-item:hover {
        background: #f8fafc;
        border-radius: 8px;
        padding-left: 8px;
        padding-right: 8px;
    }

    .rep-history-left {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .rep-history-item-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1e293b;
    }

    .rep-history-item-meta {
        font-size: 0.78rem;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .rep-history-badge {
        font-size: 0.7rem;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 4px;
        text-transform: uppercase;
    }

    .rep-history-badge.pdf {
        background: #fee2e2;
        color: #dc2626;
    }

    .rep-history-badge.excel {
        background: #dcfce7;
        color: #16a34a;
    }

    .rep-history-download-link {
        font-size: 0.85rem;
        font-weight: 700;
        color: #059669;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: color 0.15s ease;
        padding: 6px 12px;
        border-radius: 6px;
    }

    .rep-history-download-link:hover {
        color: #047857;
        background: #ecfdf5;
        text-decoration: underline;
    }

    /* 6. Sección de Vista Previa de Datos en Vivo */
    .rep-preview-section {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
        margin-top: 0.5rem;
    }

    .rep-preview-top-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1.25rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .rep-preview-top-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .rep-preview-title {
        font-size: 1.15rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
    }

    .rep-records-pill {
        background: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
        font-size: 0.76rem;
        font-weight: 800;
        padding: 3px 10px;
        border-radius: 9999px;
    }

    .rep-preview-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .rep-btn-excel {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        background: #ffffff;
        color: #059669;
        border: 1.5px solid #10b981;
        font-size: 0.85rem;
        font-weight: 700;
        padding: 7px 16px;
        border-radius: 8px;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .rep-btn-excel:hover {
        background: #ecfdf5;
        border-color: #059669;
        transform: translateY(-1px);
    }

    .rep-btn-pdf {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        background: #059669;
        color: #ffffff;
        border: none;
        font-size: 0.85rem;
        font-weight: 700;
        padding: 8px 18px;
        border-radius: 8px;
        cursor: pointer;
        text-decoration: none;
        box-shadow: 0 3px 10px rgba(5, 150, 105, 0.2);
        transition: all 0.2s ease;
    }

    .rep-btn-pdf:hover {
        background: #047857;
        color: #ffffff;
        transform: translateY(-1px);
    }

    /* Tabla de Datos */
    .rep-table-wrapper {
        overflow-x: auto;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
    }

    .rep-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 0.84rem;
    }

    .rep-table th {
        background: #f8fafc;
        color: #475569;
        font-weight: 700;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        padding: 10px 14px;
        border-bottom: 1.5px solid #e2e8f0;
        white-space: nowrap !important;
    }

    .rep-table td {
        padding: 10px 14px;
        border-bottom: 1px solid #f1f5f9;
        color: #1e293b;
        vertical-align: middle;
        white-space: nowrap !important;
    }

    .rep-table tr:hover td {
        background: #f8fafc;
    }

    .rep-colmena-badge {
        display: inline-block;
        white-space: nowrap !important;
        font-weight: 800;
        color: #047857;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 0.8rem;
        letter-spacing: 0.2px;
    }

    .rep-status-badge {
        display: inline-block;
        white-space: nowrap !important;
        font-size: 0.72rem;
        font-weight: 700;
        padding: 2px 9px;
        border-radius: 9999px;
    }

    .rep-status-badge.activa, .rep-status-badge.activo {
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
    }

    .rep-status-badge.revision {
        background: #fffbeb;
        color: #b45309;
        border: 1px solid #fde68a;
    }

    .rep-status-badge.inactiva, .rep-status-badge.inactivo, .rep-status-badge.malo {
        background: #fef2f2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }

    /* Indicador de carga */
    .rep-loading-spinner {
        display: none;
        text-align: center;
        padding: 2.5rem 1rem;
        color: #047857;
        font-weight: 600;
    }

    /* ================= MODAL FLOTANTE DE REPORTE OFICIAL ================= */
    .rep-modal-window {
        max-width: 900px !important;
        width: 96% !important;
        max-height: 94vh !important;
        border-radius: 16px !important;
        background: #0f172a !important;
        box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.6) !important;
        border: 1px solid #334155 !important;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .rep-modal-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 22px;
        background: #0f172a;
        border-bottom: 1px solid #1e293b;
        color: #ffffff;
        flex-wrap: wrap;
        gap: 12px;
    }

    .rep-modal-topbar-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .rep-modal-topbar-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: rgba(16, 185, 129, 0.12);
        border: 1px solid rgba(16, 185, 129, 0.25);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        color: #34d399;
    }

    .rep-modal-topbar-title {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 1.02rem;
        font-weight: 800;
        color: #f8fafc;
        margin: 0;
        letter-spacing: -0.2px;
    }

    .rep-modal-topbar-sub {
        font-size: 0.74rem;
        color: #94a3b8;
    }

    .rep-modal-topbar-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .rep-modal-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 7px 15px;
        border-radius: 8px;
        font-size: 0.82rem;
        font-weight: 700;
        cursor: pointer;
        border: none;
        transition: all 0.2s ease;
    }

    .rep-modal-btn.print {
        background: #047857;
        color: #ffffff;
        border: 1px solid #059669;
        box-shadow: 0 2px 6px rgba(4, 120, 87, 0.35);
    }

    .rep-modal-btn.print:hover {
        background: #065f46;
        color: #ffffff;
        transform: translateY(-1px);
    }

    .rep-modal-btn.excel {
        background: rgba(255, 255, 255, 0.08);
        color: #e2e8f0;
        border: 1px solid rgba(255, 255, 255, 0.18);
    }

    .rep-modal-btn.excel:hover {
        background: rgba(255, 255, 255, 0.16);
        color: #ffffff;
    }

    .rep-modal-btn.close {
        background: rgba(255, 255, 255, 0.08);
        color: #cbd5e1;
        border: 1px solid rgba(255, 255, 255, 0.12);
        font-size: 1rem;
        padding: 6px 12px;
        border-radius: 8px;
    }

    .rep-modal-btn.close:hover {
        background: #ef4444;
        color: #ffffff;
        border-color: #ef4444;
    }

    .rep-modal-body {
        padding: 24px;
        overflow-y: auto;
        background: #0f172a;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        flex: 1;
    }

    /* Hoja Oficial en Pantalla Flotante */
    .rep-float-sheet {
        width: 100%;
        max-width: 820px;
        background: #ffffff;
        padding: 26px 30px;
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        color: #0f172a;
        font-family: 'Inter', sans-serif;
        font-size: 11px;
        line-height: 1.35;
        box-sizing: border-box;
        overflow: hidden;
    }

    .rep-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 2px solid #047857;
        padding-bottom: 12px;
        margin-bottom: 12px;
    }

    .rep-header-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .rep-logo {
        width: 44px;
        height: 44px;
        object-fit: contain;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        padding: 2px;
        background: #ffffff;
    }

    .rep-inst-sena {
        font-size: 0.7rem;
        font-weight: 800;
        color: #047857;
        letter-spacing: 0.6px;
        text-transform: uppercase;
    }

    .rep-inst-cefa {
        font-size: 0.65rem;
        color: #64748b;
        font-weight: 500;
    }

    .rep-app-brand {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 1.15rem;
        font-weight: 800;
        color: #0f172a;
        margin-top: 1px;
        line-height: 1.1;
    }

    .rep-app-brand span {
        color: #d97706;
    }

    .rep-header-right {
        text-align: right;
        font-size: 0.72rem;
        color: #475569;
        line-height: 1.35;
    }

    .rep-header-right strong {
        color: #0f172a;
    }

    /* Banner institucional ejecutivo (en lugar de bloque verde chillón) */
    .rep-title-strip {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-left: 4px solid #047857;
        color: #0f172a;
        padding: 9px 14px;
        border-radius: 6px;
        margin-bottom: 12px;
    }

    .rep-title-text {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.92rem;
        font-weight: 800;
        color: #0f172a;
        text-transform: uppercase;
        letter-spacing: -0.2px;
    }

    .rep-badge-periodo {
        font-size: 0.7rem;
        font-weight: 700;
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
        padding: 3px 10px;
        border-radius: 12px;
        letter-spacing: 0.2px;
    }

    .rep-kpis-bar {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        margin-bottom: 12px;
    }

    .kpi-mini-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-left: 3.5px solid #047857;
        border-radius: 6px;
        padding: 7px 11px;
        display: flex;
        flex-direction: column;
    }

    .kpi-mini-card.amber {
        border-left-color: #f59e0b;
    }

    .kpi-mini-label {
        font-size: 0.62rem;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .kpi-mini-value {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 1.05rem;
        font-weight: 800;
        color: #0f172a;
        margin-top: 1px;
    }

    .kpi-mini-value.amber {
        color: #b45309;
    }

    .kpi-mini-value.green {
        color: #047857;
    }

    /* Reglas estrictas para la tabla del reporte dentro del modal flotante */
    .rep-float-sheet .rep-table {
        width: 100% !important;
        table-layout: fixed !important;
        border-collapse: collapse !important;
        font-size: 0.73rem !important;
        margin-bottom: 12px !important;
        box-sizing: border-box !important;
    }

    .rep-float-sheet .rep-table th {
        background: #0b2e23 !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        font-size: 0.65rem !important;
        text-transform: uppercase !important;
        letter-spacing: 0.4px !important;
        padding: 7px 8px !important;
        border: 1px solid #0b2e23 !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        box-sizing: border-box !important;
    }

    .rep-float-sheet .rep-table td {
        padding: 6px 8px !important;
        border: 1px solid #e2e8f0 !important;
        color: #1e293b !important;
        vertical-align: middle !important;
        font-size: 0.72rem !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        box-sizing: border-box !important;
    }

    .rep-float-sheet .rep-table tr:nth-child(even) td {
        background: #f8fafc !important;
    }

    .rep-float-sheet .rep-table tr.total-row td {
        background: #f0fdf4 !important;
        font-weight: 800 !important;
        border-top: 1.5px solid #047857 !important;
        color: #0f172a !important;
    }

    .rep-float-sheet .badge-colmena-print {
        display: inline-block !important;
        font-weight: 800 !important;
        color: #047857 !important;
        background: #f0fdf4 !important;
        border: 1px solid #bbf7d0 !important;
        padding: 1px 6px !important;
        border-radius: 4px !important;
        font-size: 0.68rem !important;
        white-space: nowrap !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }

    .rep-float-sheet .badge-status-print {
        display: inline-block !important;
        font-size: 0.62rem !important;
        font-weight: 700 !important;
        padding: 2px 7px !important;
        border-radius: 4px !important;
        border: 1px solid #cbd5e1 !important;
        background: #f8fafc !important;
        color: #334155 !important;
        white-space: nowrap !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }

    .rep-float-sheet .badge-status-print.activa {
        background: #ecfdf5 !important;
        color: #047857 !important;
        border-color: #a7f3d0 !important;
    }

    .rep-float-sheet .badge-status-print.revision {
        background: #fffbeb !important;
        color: #b45309 !important;
        border-color: #fde68a !important;
    }

    .rep-float-sheet .badge-status-print.inactiva {
        background: #fef2f2 !important;
        color: #b91c1c !important;
        border-color: #fecaca !important;
    }

    .badge-colmena-print {
        display: inline-block;
        font-weight: 800;
        color: #047857;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        padding: 1px 6px;
        border-radius: 4px;
        font-size: 0.7rem;
        white-space: nowrap;
    }

    .badge-status-print {
        display: inline-block;
        font-size: 0.62rem;
        font-weight: 700;
        padding: 1px 6px;
        border-radius: 4px;
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        color: #334155;
        white-space: nowrap;
    }

    .badge-status-print.activa {
        background: #ecfdf5;
        color: #047857;
        border-color: #a7f3d0;
    }

    .badge-status-print.revision {
        background: #fffbeb;
        color: #b45309;
        border-color: #fde68a;
    }

    .badge-status-print.inactiva {
        background: #fef2f2;
        color: #b91c1c;
        border-color: #fecaca;
    }

    .rep-signatures {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2.5rem;
        margin-top: 1.5rem;
        padding-top: 0.5rem;
    }

    .rep-sign-box {
        text-align: center;
        border-top: 1px solid #94a3b8;
        padding-top: 4px;
    }

    .sign-name {
        font-size: 0.76rem;
        font-weight: 700;
        color: #0f172a;
    }

    .sign-charge {
        font-size: 0.65rem;
        color: #64748b;
    }

    .rep-footer {
        margin-top: 1.25rem;
        padding-top: 5px;
        border-top: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 0.62rem;
        color: #94a3b8;
    }
</style>
@endsection

@section('content')
<div class="rep-container">

    <!-- 1. Encabezado principal con título y perfil de usuario -->
    <div class="rep-header-row">
        <div>
            <span class="rep-header-tag">REPORTES</span>
            <h1 class="rep-header-title">Generador de reportes</h1>
        </div>

        <div class="rep-user-chip">
            <div class="rep-user-avatar">
                {{ auth()->check() ? strtoupper(substr(auth()->user()->nickname ?? 'AC', 0, 2)) : 'AC' }}
            </div>
            <div class="rep-user-info">
                <span class="rep-user-name">
                    {{ auth()->check() && auth()->user()->person ? trim(auth()->user()->person->first_name . ' ' . auth()->user()->person->first_last_name) : (auth()->check() ? auth()->user()->nickname : 'Andrés Felipe Cuéllar') }}
                </span>
                <span class="rep-user-role">
                    Rol: {{ auth()->check() && auth()->user()->roles->first() ? auth()->user()->roles->first()->name : 'Administrador' }} <i class="fas fa-chevron-down" style="font-size: 0.65rem; margin-left: 2px;"></i>
                </span>
            </div>
        </div>
    </div>

    <!-- 2. Grid Principal (2 Columnas: Izquierda formulario, Derecha métricas e info) -->
    <div class="rep-grid-layout">

        <!-- ================= COLUMNA IZQUIERDA: FORMULARIO GENERAR REPORTE ================= -->
        <div class="rep-card">
            <h2 class="rep-card-title">Generar reporte</h2>
            <p class="rep-card-desc">Elige el tipo y ajusta los filtros disponibles.</p>

            <form id="formFiltroReportes" onsubmit="event.preventDefault(); aplicarFiltros();">
                <input type="hidden" name="tipo" id="inputTipoReporte" value="{{ $tipoReporte }}">

                <!-- Selector: Tipo de Reporte -->
                <div class="rep-form-group">
                    <span class="rep-section-label">TIPO DE REPORTE</span>
                    <div class="rep-type-options">
                        
                        <!-- Opción 1: Producción de miel -->
                        <div class="rep-type-card {{ $tipoReporte === 'miel' ? 'active' : '' }}" onclick="seleccionarTipoReporte('miel', this)">
                            <div class="rep-type-icon">
                                <i class="fas fa-droplet"></i>
                            </div>
                            <div class="rep-type-body">
                                <span class="rep-type-name">Producción de miel</span>
                                <span class="rep-type-desc">Kilogramos por apiario, colmena y período.</span>
                            </div>
                        </div>

                        <!-- Opción 2: Estado de colmenas -->
                        <div class="rep-type-card {{ $tipoReporte === 'colmenas' ? 'active' : '' }}" onclick="seleccionarTipoReporte('colmenas', this)">
                            <div class="rep-type-icon">
                                <i class="fas fa-border-all"></i>
                            </div>
                            <div class="rep-type-body">
                                <span class="rep-type-name">Estado de colmenas</span>
                                <span class="rep-type-desc">Estado sanitario y última visita de cada colmena.</span>
                            </div>
                        </div>

                        <!-- Opción 3: Inventario -->
                        <div class="rep-type-card {{ $tipoReporte === 'inventario' ? 'active' : '' }}" onclick="seleccionarTipoReporte('inventario', this)">
                            <div class="rep-type-icon">
                                <i class="fas fa-box-archive"></i>
                            </div>
                            <div class="rep-type-body">
                                <span class="rep-type-name">Inventario</span>
                                <span class="rep-type-desc">Existencias, stock mínimo y movimientos.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtro: Apiario / Bodega -->
                <div class="rep-form-group">
                    <label class="rep-form-label" id="labelApiarioFiltro">APIARIO</label>
                    <select name="apiario" id="selectApiario" class="rep-select" onchange="aplicarFiltros()">
                        <option value="all">Todos los apiarios</option>
                        @foreach($apiarios as $ap)
                            <option value="{{ $ap->nombre_apiario }}" {{ $apiarioFiltro == $ap->nombre_apiario ? 'selected' : '' }}>
                                {{ $ap->nombre_apiario }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro: Rango de Fechas (Desde / Hasta) -->
                <div class="rep-form-group" id="groupFechas">
                    <div class="rep-date-row">
                        <div class="rep-date-wrap">
                            <label class="rep-form-label">DESDE</label>
                            <input type="date" name="desde" id="inputDesde" value="{{ $desde }}" onchange="aplicarFiltros()">
                        </div>
                        <div class="rep-date-wrap">
                            <label class="rep-form-label">HASTA</label>
                            <input type="date" name="hasta" id="inputHasta" value="{{ $hasta }}" onchange="aplicarFiltros()">
                        </div>
                    </div>
                </div>

                <!-- Filtro: Estado -->
                <div class="rep-form-group">
                    <label class="rep-form-label" id="labelEstadoFiltro">ESTADO DE LA COLMENA</label>
                    <select name="estado" id="selectEstado" class="rep-select" onchange="aplicarFiltros()">
                        <option value="all">Todos los estados</option>
                        <option value="Activa">Activa</option>
                        <option value="En revisión">En revisión</option>
                        <option value="Inactiva">Inactiva</option>
                    </select>
                </div>

                <!-- Botón: Reporte -->
                <button type="button" class="rep-btn-submit" onclick="abrirModalReporteFlotante()">
                    <i class="fas fa-file-invoice"></i> Reporte
                </button>
            </form>
        </div>

        <!-- ================= COLUMNA DERECHA: MÉTRICAS Y DETALLES ================= -->
        <div class="rep-right-column">

            <!-- Fila Superior de 3 Métricas -->
            <div class="rep-metrics-row">
                <!-- 1. Miel del Período -->
                <div class="rep-metric-card border-amber">
                    <div class="rep-metric-label">MIEL DEL PERÍODO</div>
                    <div class="rep-metric-value" id="valMielPeriodo">{{ $metrics['miel_kg'] }} kg</div>
                    <div class="rep-metric-sub" id="subMielPeriodo">{{ $metrics['periodo_label'] }}</div>
                </div>

                <!-- 2. Visitas del Período -->
                <div class="rep-metric-card border-emerald">
                    <div class="rep-metric-label">VISITAS DEL PERÍODO</div>
                    <div class="rep-metric-value" id="valVisitasPeriodo">{{ $metrics['visitas_total'] }}</div>
                    <div class="rep-metric-sub" id="subVisitasPeriodo">En {{ $metrics['apiarios_activos'] }} apiarios activos</div>
                </div>

                <!-- 3. Reportes Generados -->
                <div class="rep-metric-card border-darkgreen">
                    <div class="rep-metric-label">REPORTES GENERADOS</div>
                    <div class="rep-metric-value" id="valReportesGenerados">{{ $metrics['reportes_generados'] }}</div>
                    <div class="rep-metric-sub">Últimos tres meses</div>
                </div>
            </div>

            <!-- Tarjeta Central de Detalle del Reporte -->
            <div class="rep-detail-card">
                <div class="rep-detail-header">
                    <h2 class="rep-detail-title" id="infoReporteTitulo">Producción de miel</h2>
                    <p class="rep-detail-subtitle" id="infoReporteDesc">
                        Suma los kilogramos registrados en las visitas vigentes. Las visitas anuladas no se cuentan.
                    </p>
                </div>

                <!-- Columnas Incluidas -->
                <div class="rep-columns-box">
                    <span class="rep-section-label">COLUMNAS INCLUIDAS</span>
                    <div class="rep-chips-wrap" id="chipsColumnas">
                        <!-- Chips dinámicos según el tipo de reporte -->
                        <span class="rep-column-chip">Apiario</span>
                        <span class="rep-column-chip">Colmena</span>
                        <span class="rep-column-chip">Última visita</span>
                        <span class="rep-column-chip">Visitas</span>
                        <span class="rep-column-chip">Miel (kg)</span>
                        <span class="rep-column-chip">Responsable</span>
                        <span class="rep-column-chip">Estado</span>
                    </div>
                </div>

                <!-- Últimos Reportes de este Tipo -->
                <div class="rep-history-box">
                    <span class="rep-section-label">ÚLTIMOS REPORTES DE ESTE TIPO</span>
                    <div class="rep-history-list" id="listaUltimosReportes">
                        @foreach($ultimosReportes[$tipoReporte] ?? [] as $rep)
                            <div class="rep-history-item">
                                <div class="rep-history-left">
                                    <span class="rep-history-item-title">{{ $rep['titulo'] }}</span>
                                    <div class="rep-history-item-meta">
                                        <span class="rep-history-badge {{ strtolower($rep['formato']) }}">{{ $rep['formato'] }}</span>
                                        <span>·</span>
                                        <span>{{ $rep['registros'] }} registros</span>
                                        <span>·</span>
                                        <span>{{ $rep['autor'] }}</span>
                                    </div>
                                </div>
                                <div>
                                    <a href="javascript:void(0)" onclick="descargarHistorico('{{ $rep['formato'] }}', '{{ $rep['tipo'] }}')" class="rep-history-download-link">
                                        <i class="fas fa-download"></i> Descargar
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- ================= SECCIÓN DE VISTA PREVIA INTERACTIVA ================= -->
            <div class="rep-preview-section" id="seccionVistaPrevia">
                <div class="rep-preview-top-bar">
                    <div class="rep-preview-top-left">
                        <h3 class="rep-preview-title">Vista previa de datos</h3>
                        <span class="rep-records-pill" id="badgeTotalRegistros">{{ count($previewData) }} registros</span>
                    </div>

                    <div class="rep-preview-actions">
                        <!-- Botón Reporte -->
                        <button type="button" class="rep-btn-pdf" onclick="abrirModalReporteFlotante()">
                            <i class="fas fa-file-invoice"></i> Reporte
                        </button>

                        <!-- Botón Descargar Excel -->
                        <button type="button" class="rep-btn-excel" onclick="exportarExcel()">
                            <i class="far fa-file-excel"></i> Exportar Excel
                        </button>
                    </div>
                </div>

                <!-- Spinner de Carga -->
                <div id="repLoadingSpinner" class="rep-loading-spinner">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p style="margin-top: 8px; font-size: 0.88rem;">Generando vista previa con datos en tiempo real...</p>
                </div>

                <!-- Contenedor de la Tabla -->
                <div class="rep-table-wrapper" id="repTableWrapper">
                    <table class="rep-table" id="tablaPreview">
                        <thead id="tablaPreviewHead">
                            <!-- Inyectado por JS según tipo -->
                        </thead>
                        <tbody id="tablaPreviewBody">
                            <!-- Filas dinámicas -->
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ================= MODAL FLOTANTE DE REPORTE OFICIAL ================= -->
<div class="ap-modal-backdrop" id="modalReporteFlotanteBackdrop" onclick="if(event.target === this) cerrarModalReporteFlotante()">
    <div class="ap-modal-window rep-modal-window">
        
        <!-- Barra Superior del Modal Flotante -->
        <div class="rep-modal-topbar">
            <div class="rep-modal-topbar-left">
                <div class="rep-modal-topbar-icon">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <div>
                    <h3 class="rep-modal-topbar-title" id="modalFloatingReportTitle">Reporte Oficial • SENA APÍCOLA</h3>
                    <span class="rep-modal-topbar-sub">Vista previa flotante · Formato ejecutivo optimizado para 1 página</span>
                </div>
            </div>

            <div class="rep-modal-topbar-actions">
                <button type="button" class="rep-modal-btn print" onclick="imprimirReporteAhiMismo()">
                    <i class="fas fa-print"></i> Imprimir / Guardar en PDF
                </button>
                <button type="button" class="rep-modal-btn excel" onclick="exportarExcel()">
                    <i class="far fa-file-excel"></i> Exportar Excel
                </button>
                <button type="button" class="rep-modal-btn close" onclick="cerrarModalReporteFlotante()" title="Cerrar ventana">
                    ✕
                </button>
            </div>
        </div>

        <!-- Cuerpo del Modal: Hoja Oficial Estilizada -->
        <div class="rep-modal-body">
            <div class="rep-float-sheet" id="repFloatSheet">
                <!-- Contenido inyectado dinámicamente -->
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
    // Datos en memoria iniciales
    let currentTipo = '{{ $tipoReporte }}';
    let currentData = @json($previewData);
    const ultimosReportesData = @json($ultimosReportes);

    // Definición de metadatos por tipo de reporte
    const configReportes = {
        miel: {
            titulo: 'Producción de miel',
            desc: 'Suma los kilogramos registrados en las visitas vigentes. Las visitas anuladas no se cuentan.',
            labelApiario: 'APIARIO',
            labelEstado: 'ESTADO DE LA COLMENA',
            opcionesEstado: [
                { val: 'all', txt: 'Todos los estados' },
                { val: 'Activa', txt: 'Activa' },
                { val: 'En revisión', txt: 'En revisión' },
                { val: 'Inactiva', txt: 'Inactiva' }
            ],
            columnas: ['Apiario', 'Colmena', 'Última visita', 'Visitas', 'Miel (kg)', 'Responsable', 'Estado'],
            headersTh: ['Apiario', 'Colmena', 'Última visita', 'Visitas', 'Miel (kg)', 'Responsable', 'Estado'],
            renderRow: function(item) {
                const statusLower = (item.estado || '').toLowerCase();
                const statusClass = statusLower.includes('rev') ? 'revision' : (statusLower.includes('inact') || statusLower.includes('malo') ? 'inactiva' : 'activa');
                return `
                    <tr>
                        <td style="font-weight: 600; color: #0f172a; white-space: nowrap;">${item.apiario || '-'}</td>
                        <td style="white-space: nowrap;"><span class="rep-colmena-badge">${item.colmena || '-'}</span></td>
                        <td style="white-space: nowrap;">${item.ultima_visita || '-'}</td>
                        <td style="white-space: nowrap;"><strong style="font-weight: 700;">${item.visitas || 1}</strong> <span style="color:#64748b; font-size:0.75rem;">visitas</span></td>
                        <td style="font-weight: 800; color: #d97706; white-space: nowrap;">${parseFloat(item.miel_kg || 0).toFixed(2)} kg</td>
                        <td style="white-space: nowrap;">${item.responsable || '-'}</td>
                        <td style="white-space: nowrap;"><span class="rep-status-badge ${statusClass}">${item.estado || 'Activa'}</span></td>
                    </tr>
                `;
            }
        },
        colmenas: {
            titulo: 'Estado de colmenas',
            desc: 'Estado sanitario, registro de reinas, especies y última visita registrada en cada colmena.',
            labelApiario: 'APIARIO',
            labelEstado: 'ESTADO SANITARIO',
            opcionesEstado: [
                { val: 'all', txt: 'Todos los estados' },
                { val: 'Activa', txt: 'Activa' },
                { val: 'En revisión', txt: 'En revisión' },
                { val: 'Inactiva', txt: 'Inactiva' }
            ],
            columnas: ['Código', 'Apiario', 'Tipo de abeja', 'Instalación', 'Última visita', 'Estado', 'Responsable'],
            headersTh: ['Código', 'Apiario', 'Especie / Tipo', 'Instalación', 'Última visita', 'Estado', 'Responsable'],
            renderRow: function(item) {
                const statusLower = (item.estado || '').toLowerCase();
                const statusClass = statusLower.includes('rev') ? 'revision' : (statusLower.includes('inact') ? 'inactiva' : 'activa');
                return `
                    <tr>
                        <td style="white-space: nowrap;"><span class="rep-colmena-badge">${item.codigo || '-'}</span></td>
                        <td style="font-weight: 600; color: #0f172a; white-space: nowrap;">${item.apiario || '-'}</td>
                        <td style="white-space: nowrap;"><em>${item.tipo_abeja || 'Apis mellifera'}</em></td>
                        <td style="white-space: nowrap;">${item.instalacion || '-'}</td>
                        <td style="white-space: nowrap;">${item.ultima_visita || '-'}</td>
                        <td style="white-space: nowrap;"><span class="rep-status-badge ${statusClass}">${item.estado || 'Activa'}</span></td>
                        <td style="white-space: nowrap;">${item.responsable || '-'}</td>
                    </tr>
                `;
            }
        },
        inventario: {
            titulo: 'Inventario',
            desc: 'Existencias, control de stock mínimo, herramientas, EPP y material biológico del apiario.',
            labelApiario: 'BODEGA / UBICACIÓN',
            labelEstado: 'ESTADO DEL ÍTEM',
            opcionesEstado: [
                { val: 'all', txt: 'Todos los estados' },
                { val: 'Activo', txt: 'Activo' },
                { val: 'Inactivo', txt: 'Inactivo' }
            ],
            columnas: ['Código', 'Elemento', 'Categoría', 'Bodega', 'Stock actual', 'Stock mín', 'Estado', 'Responsable'],
            headersTh: ['Código', 'Elemento', 'Categoría', 'Bodega', 'Stock actual', 'Stock mín', 'Estado', 'Responsable'],
            renderRow: function(item) {
                const statusLower = (item.estado || '').toLowerCase();
                const statusClass = statusLower.includes('inact') ? 'inactivo' : 'activo';
                const bajoStock = item.cantidad <= item.stock_minimo;
                return `
                    <tr>
                        <td style="white-space: nowrap;"><span class="rep-colmena-badge">${item.codigo || '-'}</span></td>
                        <td style="font-weight: 600; color: #0f172a; white-space: nowrap;">${item.nombre || '-'}</td>
                        <td style="white-space: nowrap;"><span style="font-size: 0.76rem; background: #f1f5f9; padding: 2px 7px; border-radius: 4px; font-weight:600; color:#475569;">${item.categoria || '-'}</span></td>
                        <td style="white-space: nowrap;">${item.bodega || '-'}</td>
                        <td style="white-space: nowrap;">
                            <strong style="color: ${bajoStock ? '#dc2626' : '#0f172a'}; font-size: 0.95rem;">${item.cantidad}</strong>
                            <small style="color: #64748b;"> ${item.unidad || 'U'}</small>
                            ${bajoStock ? '<span style="color:#dc2626; font-size:0.68rem; font-weight:700; margin-left:4px;">⚠️ Bajo</span>' : ''}
                        </td>
                        <td style="color: #64748b; white-space: nowrap;">${item.stock_minimo}</td>
                        <td style="white-space: nowrap;"><span class="rep-status-badge ${statusClass}">${item.estado || 'Activo'}</span></td>
                        <td style="white-space: nowrap;">${item.responsable || '-'}</td>
                    </tr>
                `;
            }
        }
    };

    // Al cargar la página, dibujar tabla inicial
    document.addEventListener('DOMContentLoaded', function() {
        renderTabla(currentTipo, currentData);
    });

    // Cambiar tipo de reporte (Miel, Colmenas, Inventario)
    function seleccionarTipoReporte(tipo, element) {
        if (currentTipo === tipo) return;
        currentTipo = tipo;
        document.getElementById('inputTipoReporte').value = tipo;

        // Actualizar tarjetas activas
        document.querySelectorAll('.rep-type-card').forEach(el => el.classList.remove('active'));
        if (element) element.classList.add('active');

        // Actualizar textos e información
        const conf = configReportes[tipo];
        document.getElementById('infoReporteTitulo').textContent = conf.titulo;
        document.getElementById('infoReporteDesc').textContent = conf.desc;
        document.getElementById('labelApiarioFiltro').textContent = conf.labelApiario;
        document.getElementById('labelEstadoFiltro').textContent = conf.labelEstado;

        // Mostrar / ocultar fechas si es inventario
        const groupFechas = document.getElementById('groupFechas');
        if (tipo === 'inventario') {
            groupFechas.style.opacity = '0.45';
        } else {
            groupFechas.style.opacity = '1';
        }

        // Actualizar chips de columnas
        const chipsContainer = document.getElementById('chipsColumnas');
        chipsContainer.innerHTML = conf.columnas.map(c => `<span class="rep-column-chip">${c}</span>`).join('');

        // Actualizar opciones del select de estados
        const selectEstado = document.getElementById('selectEstado');
        selectEstado.innerHTML = conf.opcionesEstado.map(o => `<option value="${o.val}">${o.txt}</option>`).join('');

        // Actualizar lista de últimos reportes
        renderUltimosReportes(tipo);

        // Disparar carga de datos
        aplicarFiltros();
    }

    // Renderizar historial de reportes
    function renderUltimosReportes(tipo) {
        const listContainer = document.getElementById('listaUltimosReportes');
        const reportes = ultimosReportesData[tipo] || [];
        
        if (reportes.length === 0) {
            listContainer.innerHTML = `<div style="padding: 1rem 0; color: #94a3b8; font-size: 0.85rem;">No hay reportes recientes registrados para esta sección.</div>`;
            return;
        }

        listContainer.innerHTML = reportes.map(rep => `
            <div class="rep-history-item">
                <div class="rep-history-left">
                    <span class="rep-history-item-title">${rep.titulo}</span>
                    <div class="rep-history-item-meta">
                        <span class="rep-history-badge ${rep.formato.toLowerCase()}">${rep.formato}</span>
                        <span>·</span>
                        <span>${rep.registros} registros</span>
                        <span>·</span>
                        <span>${rep.autor}</span>
                    </div>
                </div>
                <div>
                    <a href="javascript:void(0)" onclick="descargarHistorico('${rep.formato}', '${rep.tipo}')" class="rep-history-download-link">
                        <i class="fas fa-download"></i> Descargar
                    </a>
                </div>
            </div>
        `).join('');
    }

    // Aplicar filtros vía AJAX a la API
    function aplicarFiltros() {
        const spinner = document.getElementById('repLoadingSpinner');
        const tableWrapper = document.getElementById('repTableWrapper');
        
        spinner.style.display = 'block';
        tableWrapper.style.opacity = '0.3';

        const apiario = document.getElementById('selectApiario').value;
        const desde = document.getElementById('inputDesde').value;
        const hasta = document.getElementById('inputHasta').value;
        const estado = document.getElementById('selectEstado').value;

        const params = new URLSearchParams({
            tipo: currentTipo,
            apiario: apiario,
            desde: desde,
            hasta: hasta,
            estado: estado
        });

        fetch(`{{ route('apicola.admin.reportes.preview') }}?${params.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(response => {
            spinner.style.display = 'none';
            tableWrapper.style.opacity = '1';

            if (response.success) {
                currentData = response.data;
                renderTabla(currentTipo, currentData);
            }
        })
        .catch(err => {
            console.error('Error al consultar datos:', err);
            spinner.style.display = 'none';
            tableWrapper.style.opacity = '1';
        });
    }

    // Dibujar la tabla con el arreglo de datos
    function renderTabla(tipo, items) {
        const conf = configReportes[tipo];
        const thead = document.getElementById('tablaPreviewHead');
        const tbody = document.getElementById('tablaPreviewBody');
        const badge = document.getElementById('badgeTotalRegistros');

        badge.textContent = `${items.length} registros`;

        // Encabezados
        thead.innerHTML = `<tr>${conf.headersTh.map(h => `<th>${h}</th>`).join('')}</tr>`;

        // Cuerpo
        if (items.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="${conf.headersTh.length}" style="text-align: center; padding: 2.5rem 1rem; color: #94a3b8;">
                        <i class="fas fa-search" style="font-size: 1.5rem; margin-bottom: 8px; opacity: 0.5;"></i>
                        <p style="margin: 0; font-size: 0.9rem;">No se encontraron registros con los filtros seleccionados.</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = items.map(conf.renderRow).join('');
    }

    // Desplazarse suavemente a la vista previa
    function scrollToPreview() {
        const el = document.getElementById('seccionVistaPrevia');
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    // Exportar a Excel (CSV con formato Excel y BOM)
    function exportarExcel() {
        const apiario = document.getElementById('selectApiario').value;
        const desde = document.getElementById('inputDesde').value;
        const hasta = document.getElementById('inputHasta').value;
        const estado = document.getElementById('selectEstado').value;

        const url = `{{ route('apicola.admin.reportes.excel') }}?tipo=${currentTipo}&apiario=${encodeURIComponent(apiario)}&desde=${desde}&hasta=${hasta}&estado=${encodeURIComponent(estado)}`;
        window.location.href = url;
    }

    // Exportar a PDF / Vista de Impresión Oficial SENA
    function exportarPdf() {
        const apiario = document.getElementById('selectApiario').value;
        const desde = document.getElementById('inputDesde').value;
        const hasta = document.getElementById('inputHasta').value;
        const estado = document.getElementById('selectEstado').value;

        const url = `{{ route('apicola.admin.reportes.pdf') }}?tipo=${currentTipo}&apiario=${encodeURIComponent(apiario)}&desde=${desde}&hasta=${hasta}&estado=${encodeURIComponent(estado)}`;
        window.open(url, '_blank');
    }

    // Descargar desde el historial
    function descargarHistorico(formato, tipo) {
        if (formato.toLowerCase() === 'excel') {
            const url = `{{ route('apicola.admin.reportes.excel') }}?tipo=${tipo}`;
            window.location.href = url;
        } else {
            abrirModalReporteFlotante(tipo);
        }
    }

    // ================= FUNCIONES DEL MODAL FLOTANTE =================
    function abrirModalReporteFlotante(tipoOverride) {
        const tipo = tipoOverride || currentTipo;
        const modal = document.getElementById('modalReporteFlotanteBackdrop');
        const container = document.getElementById('repFloatSheet');
        const desdeVal = document.getElementById('inputDesde').value;
        const hastaVal = document.getElementById('inputHasta').value;

        // Formato amigable de fechas
        const dObj = new Date(desdeVal + 'T00:00:00');
        const hObj = new Date(hastaVal + 'T00:00:00');
        const dStr = (!isNaN(dObj.getTime())) ? dObj.toLocaleDateString('es-CO') : desdeVal;
        const hStr = (!isNaN(hObj.getTime())) ? hObj.toLocaleDateString('es-CO') : hastaVal;

        let tituloRep = 'Reporte de Producción y Cosecha de Miel';
        if (tipo === 'colmenas') tituloRep = 'Censo y Estado Sanitario de Colmenas';
        if (tipo === 'inventario') tituloRep = 'Inventario de Insumos, EPP y Material Apícola';

        let totalMiel = 0;
        let totalVisitas = 0;
        let totalItems = 0;

        let theadHtml = '';
        let tbodyHtml = '';

        if (tipo === 'miel') {
            theadHtml = `
                <tr>
                    <th style="width: 25%; text-align: left;">Apiario</th>
                    <th style="width: 12%; text-align: center;">Colmena</th>
                    <th style="width: 14%; text-align: center;">Última Visita</th>
                    <th style="width: 10%; text-align: center;">Visitas</th>
                    <th style="width: 12%; text-align: right;">Miel (kg)</th>
                    <th style="width: 15%; text-align: left;">Responsable</th>
                    <th style="width: 12%; text-align: center;">Estado</th>
                </tr>
            `;
            if (currentData.length === 0) {
                tbodyHtml = `<tr><td colspan="7" style="text-align:center; padding:1.5rem; color:#94a3b8;">No se encontraron registros en la base de datos para los criterios seleccionados.</td></tr>`;
            } else {
                tbodyHtml = currentData.map(r => {
                    totalMiel += parseFloat(r.miel_kg || 0);
                    totalVisitas += parseInt(r.visitas || 1);
                    const st = r.estado || 'Activa';
                    const stLower = st.toLowerCase();
                    const stClass = stLower.includes('rev') ? 'revision' : (stLower.includes('inact') ? 'inactiva' : 'activa');
                    return `
                        <tr>
                            <td style="text-align: left;" title="${r.apiario || '-'}"><strong>${r.apiario || '-'}</strong></td>
                            <td style="text-align: center;"><span class="badge-colmena-print">${r.colmena || '-'}</span></td>
                            <td style="text-align: center;">${r.ultima_visita || '-'}</td>
                            <td style="text-align: center;">${r.visitas || 1}</td>
                            <td style="text-align: right;"><strong style="color:#d97706;">${parseFloat(r.miel_kg || 0).toFixed(2)} kg</strong></td>
                            <td style="text-align: left;" title="${r.responsable || '-'}">${r.responsable || '-'}</td>
                            <td style="text-align: center;"><span class="badge-status-print ${stClass}">${st}</span></td>
                        </tr>
                    `;
                }).join('');

                tbodyHtml += `
                    <tr class="total-row">
                        <td colspan="4" style="text-align:right; text-transform:uppercase;">Total Consolidado Miel:</td>
                        <td style="text-align:right; color:#d97706; font-weight:800;">${totalMiel.toFixed(2)} kg</td>
                        <td colspan="2"></td>
                    </tr>
                `;
            }
        } else if (tipo === 'colmenas') {
            theadHtml = `
                <tr>
                    <th style="width: 12%; text-align: center;">Código</th>
                    <th style="width: 24%; text-align: left;">Apiario</th>
                    <th style="width: 18%; text-align: left;">Especie / Tipo</th>
                    <th style="width: 12%; text-align: center;">Instalación</th>
                    <th style="width: 12%; text-align: center;">Última Visita</th>
                    <th style="width: 10%; text-align: center;">Estado</th>
                    <th style="width: 12%; text-align: left;">Responsable</th>
                </tr>
            `;
            if (currentData.length === 0) {
                tbodyHtml = `<tr><td colspan="7" style="text-align:center; padding:1.5rem; color:#94a3b8;">No hay colmenas registradas en la base de datos.</td></tr>`;
            } else {
                tbodyHtml = currentData.map(r => {
                    const st = r.estado || 'Activa';
                    const stLower = st.toLowerCase();
                    const stClass = stLower.includes('rev') ? 'revision' : (stLower.includes('inact') ? 'inactiva' : 'activa');
                    return `
                        <tr>
                            <td style="text-align: center;"><span class="badge-colmena-print">${r.codigo || '-'}</span></td>
                            <td style="text-align: left;" title="${r.apiario || '-'}"><strong>${r.apiario || '-'}</strong></td>
                            <td style="text-align: left;"><em>${r.tipo_abeja || 'Apis mellifera'}</em></td>
                            <td style="text-align: center;">${r.instalacion || '-'}</td>
                            <td style="text-align: center;">${r.ultima_visita || '-'}</td>
                            <td style="text-align: center;"><span class="badge-status-print ${stClass}">${st}</span></td>
                            <td style="text-align: left;" title="${r.responsable || '-'}">${r.responsable || '-'}</td>
                        </tr>
                    `;
                }).join('');
            }
        } else { // inventario
            theadHtml = `
                <tr>
                    <th style="width: 11%; text-align: center;">Código</th>
                    <th style="width: 24%; text-align: left;">Elemento</th>
                    <th style="width: 14%; text-align: left;">Categoría</th>
                    <th style="width: 13%; text-align: left;">Bodega</th>
                    <th style="width: 13%; text-align: right;">Stock Actual</th>
                    <th style="width: 8%; text-align: center;">Stock Mín</th>
                    <th style="width: 8%; text-align: center;">Estado</th>
                    <th style="width: 9%; text-align: left;">Responsable</th>
                </tr>
            `;
            if (currentData.length === 0) {
                tbodyHtml = `<tr><td colspan="8" style="text-align:center; padding:1.5rem; color:#94a3b8;">No hay elementos de inventario registrados.</td></tr>`;
            } else {
                tbodyHtml = currentData.map(r => {
                    totalItems += parseInt(r.cantidad || 0);
                    const st = r.estado || 'Activo';
                    const stLower = st.toLowerCase();
                    const stClass = stLower.includes('inact') ? 'inactiva' : 'activa';
                    return `
                        <tr>
                            <td style="text-align: center;"><span class="badge-colmena-print">${r.codigo || '-'}</span></td>
                            <td style="text-align: left;" title="${r.nombre || '-'}"><strong>${r.nombre || '-'}</strong></td>
                            <td style="text-align: left;">${r.categoria || '-'}</td>
                            <td style="text-align: left;">${r.bodega || '-'}</td>
                            <td style="text-align: right;"><strong>${r.cantidad || 0} ${r.unidad || ''}</strong></td>
                            <td style="text-align: center;">${r.stock_minimo || 0}</td>
                            <td style="text-align: center;"><span class="badge-status-print ${stClass}">${st}</span></td>
                            <td style="text-align: left;" title="${r.responsable || '-'}">${r.responsable || '-'}</td>
                        </tr>
                    `;
                }).join('');
            }
        }

        // Mini KPIs
        let kpisHtml = '';
        if (tipo === 'miel') {
            kpisHtml = `
                <div class="kpi-mini-card amber">
                    <span class="kpi-mini-label">Miel Cosechada</span>
                    <span class="kpi-mini-value amber">${totalMiel.toFixed(2)} kg</span>
                </div>
                <div class="kpi-mini-card">
                    <span class="kpi-mini-label">Total Visitas</span>
                    <span class="kpi-mini-value green">${totalVisitas}</span>
                </div>
                <div class="kpi-mini-card">
                    <span class="kpi-mini-label">Colmenas Evaluadas</span>
                    <span class="kpi-mini-value">${currentData.length}</span>
                </div>
            `;
        } else if (tipo === 'colmenas') {
            kpisHtml = `
                <div class="kpi-mini-card">
                    <span class="kpi-mini-label">Total Colmenas</span>
                    <span class="kpi-mini-value green">${currentData.length}</span>
                </div>
                <div class="kpi-mini-card">
                    <span class="kpi-mini-label">Apiarios Involucrados</span>
                    <span class="kpi-mini-value">${new Set(currentData.map(d => d.apiario)).size}</span>
                </div>
                <div class="kpi-mini-card">
                    <span class="kpi-mini-label">Censo</span>
                    <span class="kpi-mini-value">Actualizado</span>
                </div>
            `;
        } else {
            kpisHtml = `
                <div class="kpi-mini-card">
                    <span class="kpi-mini-label">Total en Stock</span>
                    <span class="kpi-mini-value green">${totalItems} unidades</span>
                </div>
                <div class="kpi-mini-card">
                    <span class="kpi-mini-label">Ítems Registrados</span>
                    <span class="kpi-mini-value">${currentData.length}</span>
                </div>
                <div class="kpi-mini-card">
                    <span class="kpi-mini-label">Estado Bodega</span>
                    <span class="kpi-mini-value">Activo</span>
                </div>
            `;
        }

        const userName = '{{ Auth::user()?->full_name ?? (Auth::user()?->nickname ?? 'Administrador') }}';
        const nowStr = new Date().toLocaleString('es-CO');

        container.innerHTML = `
            <div class="rep-header">
                <div class="rep-header-left">
                    <img src="{{ asset('modules/apicola/img/logo.png') }}" 
                         alt="SENA Logo" 
                         class="rep-logo"
                         onerror="this.onerror=null; this.src='{{ asset('apicola-assets/assets/img/logo-apicola-bee-original.png') }}';">
                    <div>
                        <div class="rep-inst-sena">Servicio Nacional de Aprendizaje • SENA</div>
                        <div class="rep-inst-cefa">Centro de Formación Agroindustrial La Angostura</div>
                        <div class="rep-app-brand">SENA <span>APÍCOLA</span></div>
                    </div>
                </div>
                <div class="rep-header-right">
                    <div><strong>Fecha de emisión:</strong> ${nowStr}</div>
                    <div><strong>Generado por:</strong> ${userName}</div>
                    <div><strong>Rol:</strong> Administrador Apícola</div>
                </div>
            </div>

            <div class="rep-title-strip">
                <div class="rep-title-text">${tituloRep}</div>
                <div class="rep-badge-periodo">Período: ${dStr} al ${hStr}</div>
            </div>

            <div class="rep-kpis-bar">
                ${kpisHtml}
            </div>

            <table class="rep-table">
                <thead>
                    ${theadHtml}
                </thead>
                <tbody>
                    ${tbodyHtml}
                </tbody>
            </table>

            <div class="rep-signatures">
                <div class="rep-sign-box">
                    <div class="sign-name">${userName}</div>
                    <div class="sign-charge">Administrador Unidad Apícola · SENA CEFA</div>
                </div>
                <div class="rep-sign-box">
                    <div class="sign-name">Sergio Barrera</div>
                    <div class="sign-charge">Instructor / Apicultor Técnico Responsable</div>
                </div>
            </div>

            <footer class="rep-footer">
                <div>SENA Centro de Formación Agroindustrial La Angostura • Campoalegre, Huila</div>
                <div>Sistema de Información ERP SENA Empresa • SENA Apícola</div>
            </footer>
        `;

        modal.classList.add('is-open');
    }

    function cerrarModalReporteFlotante() {
        const modal = document.getElementById('modalReporteFlotanteBackdrop');
        if (modal) modal.classList.remove('is-open');
    }

    // Imprimir "ahí mismo" en la misma pantalla sin abrir pestañas y garantizando 1 sola página
    function imprimirReporteAhiMismo() {
        const sheet = document.getElementById('repFloatSheet');
        if (!sheet) return;

        let printFrame = document.getElementById('repPrintIframe');
        if (!printFrame) {
            printFrame = document.createElement('iframe');
            printFrame.id = 'repPrintIframe';
            printFrame.style.position = 'fixed';
            printFrame.style.right = '0';
            printFrame.style.bottom = '0';
            printFrame.style.width = '0';
            printFrame.style.height = '0';
            printFrame.style.border = '0';
            document.body.appendChild(printFrame);
        }

        const doc = printFrame.contentWindow.document;
        doc.open();
        doc.write(`
            <!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <title>Reporte SENA APÍCOLA</title>
                <link rel="preconnect" href="https://fonts.googleapis.com">
                <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
                <style>
                    @page {
                        size: letter portrait;
                        margin: 6mm 8mm 6mm 8mm;
                    }
                    * {
                        box-sizing: border-box;
                        margin: 0;
                        padding: 0;
                    }
                    html, body {
                        background: #ffffff !important;
                        color: #0f172a;
                        font-family: 'Inter', sans-serif;
                        font-size: 10px;
                        line-height: 1.25;
                        height: 100% !important;
                        overflow: hidden !important;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }
                    .rep-float-sheet {
                        width: 100% !important;
                        max-width: 100% !important;
                        padding: 0 !important;
                        margin: 0 !important;
                        box-shadow: none !important;
                        border: none !important;
                        page-break-inside: avoid !important;
                        page-break-after: avoid !important;
                        overflow: hidden !important;
                    }
                    .rep-header {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        border-bottom: 2px solid #047857 !important;
                        padding-bottom: 8px;
                        margin-bottom: 8px;
                    }
                    .rep-header-left {
                        display: flex;
                        align-items: center;
                        gap: 10px;
                    }
                    .rep-logo {
                        width: 40px;
                        height: 40px;
                        object-fit: contain;
                        border-radius: 6px;
                        border: 1px solid #e2e8f0;
                        padding: 2px;
                    }
                    .rep-inst-sena {
                        font-size: 0.65rem;
                        font-weight: 800;
                        color: #047857 !important;
                        text-transform: uppercase;
                        letter-spacing: 0.5px;
                    }
                    .rep-inst-cefa {
                        font-size: 0.62rem;
                        color: #64748b;
                    }
                    .rep-app-brand {
                        font-family: 'Plus Jakarta Sans', sans-serif;
                        font-size: 1.1rem;
                        font-weight: 800;
                        color: #0f172a;
                        margin-top: 1px;
                        line-height: 1.1;
                    }
                    .rep-app-brand span {
                        color: #d97706;
                    }
                    .rep-header-right {
                        text-align: right;
                        font-size: 0.7rem;
                        color: #475569;
                        line-height: 1.35;
                    }
                    .rep-header-right strong {
                        color: #0f172a;
                    }
                    .rep-title-strip {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        background: #f8fafc !important;
                        border: 1px solid #e2e8f0 !important;
                        border-left: 4px solid #047857 !important;
                        color: #0f172a !important;
                        padding: 6px 12px;
                        border-radius: 5px;
                        margin-bottom: 8px;
                    }
                    .rep-title-text {
                        font-family: 'Plus Jakarta Sans', sans-serif;
                        font-size: 0.88rem;
                        font-weight: 800;
                        text-transform: uppercase;
                        color: #0f172a !important;
                    }
                    .rep-badge-periodo {
                        font-size: 0.65rem;
                        font-weight: 700;
                        background: #ecfdf5 !important;
                        color: #047857 !important;
                        border: 1px solid #a7f3d0 !important;
                        padding: 2px 8px;
                        border-radius: 10px;
                    }
                    .rep-kpis-bar {
                        display: grid;
                        grid-template-columns: repeat(3, 1fr);
                        gap: 6px;
                        margin-bottom: 8px;
                    }
                    .kpi-mini-card {
                        background: #f8fafc !important;
                        border: 1px solid #e2e8f0;
                        border-left: 3px solid #047857 !important;
                        border-radius: 5px;
                        padding: 5px 8px;
                    }
                    .kpi-mini-card.amber {
                        border-left-color: #f59e0b !important;
                    }
                    .kpi-mini-label {
                        font-size: 0.6rem;
                        font-weight: 800;
                        color: #64748b;
                        text-transform: uppercase;
                    }
                    .kpi-mini-value {
                        font-family: 'Plus Jakarta Sans', sans-serif;
                        font-size: 0.98rem;
                        font-weight: 800;
                        color: #0f172a;
                        margin-top: 1px;
                    }
                    .kpi-mini-value.amber {
                        color: #b45309;
                    }
                    .kpi-mini-value.green {
                        color: #047857 !important;
                    }
                    .rep-table {
                        width: 100% !important;
                        table-layout: fixed !important;
                        border-collapse: collapse !important;
                        font-size: 0.68rem !important;
                        margin-bottom: 8px !important;
                    }
                    .rep-table th {
                        background: #0b2e23 !important;
                        color: #ffffff !important;
                        font-weight: 700;
                        font-size: 0.62rem;
                        text-transform: uppercase;
                        padding: 5px 6px !important;
                        border: 1px solid #0b2e23 !important;
                        white-space: nowrap !important;
                        overflow: hidden !important;
                        text-overflow: ellipsis !important;
                    }
                    .rep-table td {
                        padding: 4.5px 6px !important;
                        border: 1px solid #e2e8f0 !important;
                        color: #1e293b;
                        vertical-align: middle;
                        white-space: nowrap !important;
                        overflow: hidden !important;
                        text-overflow: ellipsis !important;
                    }
                    .rep-table tr:nth-child(even) td {
                        background: #f8fafc;
                    }
                    .rep-table tr.total-row td {
                        background: #f0fdf4 !important;
                        font-weight: 800;
                        border-top: 1.5px solid #047857 !important;
                        color: #0f172a;
                    }
                    .badge-colmena-print {
                        display: inline-block;
                        font-weight: 800;
                        color: #047857 !important;
                        background: #f0fdf4 !important;
                        border: 1px solid #bbf7d0 !important;
                        padding: 1px 5px;
                        border-radius: 4px;
                        font-size: 0.68rem;
                        white-space: nowrap;
                    }
                    .badge-status-print {
                        display: inline-block;
                        font-size: 0.6rem;
                        font-weight: 700;
                        padding: 1px 5px;
                        border-radius: 4px;
                        border: 1px solid #cbd5e1;
                        background: #f8fafc;
                        color: #334155;
                        white-space: nowrap;
                    }
                    .badge-status-print.activa {
                        background: #ecfdf5 !important;
                        color: #047857 !important;
                        border-color: #a7f3d0 !important;
                    }
                    .badge-status-print.revision {
                        background: #fffbeb !important;
                        color: #b45309 !important;
                        border-color: #fde68a !important;
                    }
                    .badge-status-print.inactiva {
                        background: #fef2f2 !important;
                        color: #b91c1c !important;
                        border-color: #fecaca !important;
                    }
                    .rep-signatures {
                        display: grid;
                        grid-template-columns: 1fr 1fr;
                        gap: 2rem;
                        margin-top: 1.25rem;
                        padding-top: 4px;
                        page-break-inside: avoid !important;
                    }
                    .rep-sign-box {
                        text-align: center;
                        border-top: 1px solid #94a3b8;
                        padding-top: 3px;
                    }
                    .sign-name {
                        font-size: 0.72rem;
                        font-weight: 700;
                        color: #0f172a;
                    }
                    .sign-charge {
                        font-size: 0.62rem;
                        color: #64748b;
                    }
                    .rep-footer {
                        margin-top: 1rem;
                        padding-top: 4px;
                        border-top: 1px solid #e2e8f0;
                        display: flex;
                        justify-content: space-between;
                        font-size: 0.6rem;
                        color: #94a3b8;
                        page-break-inside: avoid !important;
                    }
                </style>
            </head>
            <body>
                ${sheet.outerHTML}
            </body>
            </html>
        `);
        doc.close();

        setTimeout(() => {
            printFrame.contentWindow.focus();
            printFrame.contentWindow.print();
        }, 300);
    }

    // Atajo de teclado: Cerrar con Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            cerrarModalReporteFlotante();
        }
    });
</script>
@endsection
