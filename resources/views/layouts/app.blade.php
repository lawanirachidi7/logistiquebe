<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ \App\Models\Setting::get('app_name', 'BAOBAB Express') }}</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ file_exists(public_path('images/favicon.png')) ? asset('images/favicon.png') : asset('images/logo.png') }}?v={{ time() }}">
    <link rel="shortcut icon" href="{{ file_exists(public_path('images/favicon.png')) ? asset('images/favicon.png') : asset('images/logo.png') }}?v={{ time() }}">
    <link rel="apple-touch-icon" href="{{ file_exists(public_path('images/favicon.png')) ? asset('images/favicon.png') : asset('images/logo.png') }}?v={{ time() }}">

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    @stack('styles')
    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-bg: #1e293b;
            --sidebar-hover: #334155;
            --sidebar-active: #3b82f6;
            --topbar-height: 60px;
        }
        
        body {
            overflow-x: hidden;
            background-color: #f1f5f9;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            z-index: 1000;
            transition: all 0.3s ease;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 20px;
            background: rgba(0,0,0,0.1);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-brand {
            color: #fff;
            font-size: 1.5rem;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .sidebar-brand:hover {
            color: #fff;
        }
        
        .sidebar-brand i {
            font-size: 1.8rem;
            color: var(--sidebar-active);
        }
        
        .sidebar-menu {
            padding: 15px 0;
        }
        
        .sidebar-menu-title {
            color: rgba(255,255,255,0.4);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 10px 20px;
            margin-top: 10px;
        }
        
        .sidebar-nav {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-nav-item {
            margin: 2px 10px;
        }
        
        .sidebar-nav-link {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s ease;
            gap: 12px;
        }
        
        .sidebar-nav-link:hover {
            background: var(--sidebar-hover);
            color: #fff;
        }
        
        .sidebar-nav-link.active {
            background: var(--sidebar-active);
            color: #fff;
        }
        
        .sidebar-nav-link i {
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }
        
        .sidebar-nav-link .badge {
            margin-left: auto;
        }
        
        /* Submenu */
        .sidebar-submenu {
            list-style: none;
            padding: 0;
            margin: 0;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        
        .sidebar-submenu.show {
            max-height: 500px;
        }
        
        .sidebar-submenu .sidebar-nav-link {
            padding-left: 52px;
            font-size: 0.9rem;
        }
        
        .sidebar-nav-link[data-bs-toggle="collapse"] .chevron {
            margin-left: auto;
            transition: transform 0.3s ease;
        }
        
        .sidebar-nav-link[data-bs-toggle="collapse"]:not(.collapsed) .chevron {
            transform: rotate(90deg);
        }
        
        /* Main Content */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }
        
        /* Topbar */
        .topbar {
            position: sticky;
            top: 0;
            height: var(--topbar-height);
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            z-index: 999;
            display: flex;
            align-items: center;
            padding: 0 20px;
        }
        
        .topbar-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #64748b;
            cursor: pointer;
            padding: 5px 10px;
            margin-right: 15px;
        }
        
        .topbar-search {
            flex: 1;
            max-width: 400px;
        }
        
        .topbar-search .form-control {
            background: #f1f5f9;
            border: none;
            border-radius: 8px;
            padding: 10px 15px 10px 40px;
        }
        
        .topbar-search .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }
        
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-left: auto;
        }
        
        .topbar-icon-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #f1f5f9;
            border: none;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: relative;
            transition: all 0.2s ease;
        }
        
        .topbar-icon-btn:hover {
            background: #e2e8f0;
            color: #1e293b;
        }
        
        .topbar-icon-btn .badge {
            position: absolute;
            top: -2px;
            right: -2px;
            font-size: 0.65rem;
        }
        
        .user-dropdown {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 5px 10px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s ease;
        }
        
        .user-dropdown:hover {
            background: #f1f5f9;
        }
        
        .user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: var(--sidebar-active);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
        
        .user-info {
            line-height: 1.2;
        }
        
        .user-name {
            font-weight: 600;
            color: #1e293b;
            font-size: 0.9rem;
        }
        
        .user-role {
            font-size: 0.75rem;
            color: #94a3b8;
        }
        
        /* Content */
        .main-content {
            padding: 25px;
        }
        
        /* Cards */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        
        .card-header {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 15px 20px;
            font-weight: 600;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #64748b;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 20px;
        }
        
        .empty-state h4 {
            color: #475569;
            margin-bottom: 10px;
        }
        
        /* Loading Spinner */
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(59, 130, 246, 0.3);
            border-radius: 50%;
            border-top-color: #3b82f6;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                left: calc(-1 * var(--sidebar-width));
            }
            
            .sidebar.show {
                left: 0;
            }
            
            .main-wrapper {
                margin-left: 0;
            }
            
            .topbar-toggle {
                display: block;
            }
            
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                z-index: 999;
            }
            
            .sidebar-overlay.show {
                display: block;
            }
            
            .main-content {
                padding: 15px;
            }
            
            .page-header {
                padding: 20px 15px;
                margin: -15px -15px 15px -15px;
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .page-header h1,
            .page-header .page-title {
                font-size: 1.4rem;
            }
            
            .page-header-actions {
                width: 100%;
            }
            
            .page-header-actions .btn {
                width: 100%;
                justify-content: center;
            }
        }
        
        @media (max-width: 576px) {
            .main-content {
                padding: 10px;
            }
            
            .page-header {
                padding: 15px 12px;
                margin: -10px -10px 15px -10px;
            }
            
            .page-header h1,
            .page-header .page-title {
                font-size: 1.25rem;
            }
            
            .page-header .page-title i {
                font-size: 1.1rem;
            }
            
            .page-header .page-subtitle {
                font-size: 0.8rem;
            }
            
            .card {
                border-radius: 10px;
            }
            
            .card-header {
                padding: 12px 15px;
                font-size: 0.9rem;
            }
            
            .card-body {
                padding: 15px;
            }
            
            .btn {
                padding: 10px 16px;
                font-size: 0.9rem;
            }
            
            .btn-sm {
                padding: 6px 10px;
                font-size: 0.8rem;
            }
            
            /* Mobile table improvements */
            .table-responsive {
                margin: 2.5rem;
                padding: 0;
                border-radius: 0;
                overflow: visible;
            }
            
            .table {
                font-size: 0.85rem;
            }
            
            .table-dark, .table-dark th, .table-dark td {
                --bs-table-border-color: transparent;
            }
            
            .table thead th {
                padding: 10px 8px;
                font-size: 0.75rem;
            }
            
            .table tbody td {
                padding: 10px 8px;
            }
            
            .badge {
                padding: 4px 8px;
                font-size: 0.7rem;
            }
            
            .empty-state {
                padding: 40px 15px;
            }
            
            .empty-state i {
                font-size: 3rem;
            }
            
            .empty-state h4 {
                font-size: 1.1rem;
            }
        }
        
        /* Stat Cards - Desktop & Mobile */
        .stat-card {
            border-radius: 16px;
            padding: 24px;
            color: #fff;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 200%;
            background: rgba(255, 255, 255, 0.1);
            transform: rotate(45deg);
            pointer-events: none;
        }
        
        .stat-card.primary { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); }
        .stat-card.success { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .stat-card.warning { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
        .stat-card.info { background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); }
        .stat-card.danger { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
        
        .stat-card .stat-icon {
            font-size: 2.5rem;
            opacity: 0.3;
            position: absolute;
            right: 20px;
            top: 20px;
        }
        
        .stat-card .stat-label {
            font-size: 0.85rem;
            opacity: 0.9;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        
        .stat-card .stat-value {
            font-size: 2.2rem;
            font-weight: 800;
            line-height: 1.2;
        }
        
        .stat-card .stat-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 15px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: color 0.2s, gap 0.2s;
        }
        
        .stat-card .stat-link:hover {
            color: #fff;
            gap: 10px;
        }
        
        @media (max-width: 576px) {
            .stat-card {
                padding: 20px;
                border-radius: 12px;
            }
            
            .stat-card .stat-icon {
                font-size: 2rem;
                right: 15px;
                top: 15px;
            }
            
            .stat-card .stat-label {
                font-size: 0.8rem;
            }
            
            .stat-card .stat-value {
                font-size: 1.8rem;
            }
            
            .stat-card .stat-link {
                font-size: 0.8rem;
                margin-top: 12px;
            }
        }
        
        /* Quick Actions */
        .quick-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }
        
        .quick-action-btn {
            flex: 1;
            min-width: 180px;
            padding: 18px 20px;
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            transition: all 0.3s ease;
            color: #fff;
            font-weight: 600;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .quick-action-btn i {
            font-size: 1.5rem;
        }
        
        .quick-action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
            color: #fff;
        }
        
        .quick-action-btn.primary { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); }
        .quick-action-btn.success { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .quick-action-btn.warning { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
        .quick-action-btn.info { background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); }
        .quick-action-btn.secondary { background: linear-gradient(135deg, #64748b 0%, #475569 100%); }
        .quick-action-btn.danger { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
        
        @media (max-width: 768px) {
            .quick-actions {
                gap: 10px;
            }
            
            .quick-action-btn {
                min-width: calc(50% - 5px);
                flex: 0 0 calc(50% - 5px);
                padding: 15px;
            }
            
            .quick-action-btn i {
                font-size: 1.3rem;
            }
            
            .quick-action-btn span {
                font-size: 0.85rem;
            }
        }
        
        @media (max-width: 400px) {
            .quick-action-btn {
                min-width: 100%;
                flex: 0 0 100%;
                flex-direction: row;
                justify-content: center;
                padding: 14px 20px;
            }
        }
        
        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            padding: 24px;
            margin: -25px -25px 25px -25px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .page-header h1, 
        .page-header .page-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .page-header .page-title i {
            color: #3b82f6;
            font-size: 1.4rem;
        }
        
        .page-header .page-subtitle {
            color: #64748b;
            font-size: 0.9rem;
            margin-top: 4px;
        }
        
        .page-header-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        /* Table Enhancements */
        .table-container {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            overflow: hidden;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-bottom: 2px solid #e2e8f0;
            color: #475569;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 14px 16px;
            white-space: nowrap;
        }
        
        .table tbody td {
            padding: 14px 16px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }
        
        .table tbody tr {
            transition: background-color 0.15s ease;
        }
        
        .table tbody tr:hover {
            background-color: #f8fafc;
        }
        
        .table-striped > tbody > tr:nth-of-type(odd) > * {
            background-color: rgba(241, 245, 249, 0.5);
        }
        
        /* Action buttons in table */
        .btn-group-actions {
            display: flex;
            gap: 6px;
            flex-wrap: nowrap;
        }
        
        .btn-group-actions .btn {
            padding: 6px 10px;
            font-size: 0.8rem;
        }
        
        @media (max-width: 768px) {
            .btn-group-actions {
                flex-wrap: wrap;
                gap: 4px;
            }
            
            .btn-group-actions .btn {
                padding: 5px 8px;
                font-size: 0.75rem;
            }
            
            .btn-group-actions .btn span {
                display: none;
            }
        }
        
        /* Enhanced Buttons */
        .btn {
            border-radius: 10px;
            font-weight: 600;
            padding: 10px 18px;
            transition: all 0.3s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            box-shadow: 0 4px 14px rgba(59, 130, 246, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3);
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            box-shadow: 0 4px 14px rgba(245, 158, 11, 0.3);
            color: #fff;
        }
        
        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4);
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #fff;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            box-shadow: 0 4px 14px rgba(239, 68, 68, 0.3);
        }
        
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }
        
        .btn-info {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            box-shadow: 0 4px 14px rgba(6, 182, 212, 0.3);
            color: #fff;
        }
        
        .btn-info:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(6, 182, 212, 0.4);
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            color: #fff;
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #64748b 0%, #475569 100%);
            box-shadow: 0 4px 14px rgba(100, 116, 139, 0.3);
        }
        
        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(100, 116, 139, 0.4);
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.85rem;
            border-radius: 8px;
        }
        
        .btn-lg {
            padding: 14px 24px;
            font-size: 1rem;
        }
        
        /* Enhanced Badges */
        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.75rem;
            letter-spacing: 0.3px;
        }
        
        .badge.bg-success { background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important; }
        .badge.bg-danger { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important; }
        .badge.bg-warning { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important; color: #fff !important; }
        .badge.bg-info { background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%) !important; }
        .badge.bg-primary { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important; }
        .badge.bg-secondary { background: linear-gradient(135deg, #64748b 0%, #475569 100%) !important; }
        
        /* Enhanced Cards */
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            overflow: hidden;
            transition: box-shadow 0.3s ease;
        }
        
        .card:hover {
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        }
        
        .card-header {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-bottom: 1px solid #e2e8f0;
            padding: 16px 20px;
            font-weight: 600;
            color: #334155;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .card-header i {
            color: #3b82f6;
        }
        
        .card-body {
            padding: 20px;
        }
        
        /* Enhanced Alerts */
        .alert {
            border: none;
            border-radius: 12px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
            border-left: 4px solid #10b981;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }
        
        .alert-warning {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
            border-left: 4px solid #f59e0b;
        }
        
        .alert-info {
            background: linear-gradient(135deg, #cffafe 0%, #a5f3fc 100%);
            color: #0e7490;
            border-left: 4px solid #06b6d4;
        }
        
        /* Enhanced Form Controls */
        .form-control, .form-select {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background-color: #fff;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
            outline: none;
        }
        
        .form-label {
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        
        @media (max-width: 576px) {
            .form-control, .form-select {
                padding: 10px 14px;
                font-size: 16px; /* Prevents zoom on iOS */
            }
            
            .form-label {
                font-size: 0.85rem;
            }
        }
        
        /* DataTables */
        .dt-buttons {
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .dt-buttons .btn {
            margin-right: 0;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 10px 18px;
            font-weight: 600;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .dt-buttons .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .dt-buttons .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
        }
        
        .dt-buttons .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border: none;
        }
        
        .dt-buttons .btn-info {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            border: none;
            color: #fff;
        }
        
        .dt-buttons .btn-secondary {
            background: linear-gradient(135deg, #64748b 0%, #475569 100%);
            border: none;
        }
        
        .dataTables_wrapper {
            padding: 0;
        }
        
        .dataTables_filter {
            margin-bottom: 20px;
        }
        
        .dataTables_filter input {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 18px;
            margin-left: 10px;
            min-width: 250px;
            transition: all 0.3s ease;
        }
        
        .dataTables_filter input:focus {
            border-color: #3b82f6;
            outline: none;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
        }
        
        .dataTables_length select {
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 14px;
        }
        
        .dataTables_info {
            color: #64748b;
            font-size: 0.85rem;
            padding-top: 15px;
        }
        
        .dataTables_paginate {
            padding-top: 15px;
        }
        
        .dataTables_paginate .paginate_button {
            border-radius: 8px !important;
            margin: 0 3px;
            padding: 6px 12px !important;
            border: 2px solid #e2e8f0 !important;
        }
        
        .dataTables_paginate .paginate_button.current {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;
            border-color: #3b82f6 !important;
            color: #fff !important;
        }
        
        .dataTables_paginate .paginate_button:hover {
            background: #f1f5f9 !important;
            border-color: #3b82f6 !important;
            color: #3b82f6 !important;
        }
        
        @media (max-width: 768px) {
            .dataTables_wrapper .row {
                flex-direction: column;
            }
            
            .dataTables_wrapper .row > div {
                width: 100%;
                text-align: left !important;
                margin-bottom: 10px;
            }
            
            .dt-buttons {
                justify-content: flex-start;
                width: 100%;
            }
            
            .dt-buttons .btn {
                flex: 1;
                justify-content: center;
                font-size: 0.8rem;
                padding: 8px 12px;
            }
            
            .dataTables_filter {
                width: 100%;
            }
            
            .dataTables_filter input {
                width: 100% !important;
                margin-left: 0;
                margin-top: 5px;
            }
            
            .dataTables_filter label {
                display: flex;
                flex-direction: column;
                width: 100%;
            }
            
            .dataTables_length {
                margin-bottom: 10px;
            }
            
            .dataTables_paginate {
                display: flex;
                flex-wrap: wrap;
                gap: 5px;
            }
            
            .dataTables_paginate .paginate_button {
                margin: 0 !important;
                padding: 8px 12px !important;
            }
        }
        
        /* Enhanced Table Action Buttons */
        .table .btn-group,
        .table .btn-group-actions {
            display: flex;
            gap: 8px;
            flex-wrap: nowrap;
        }
        
        .table .btn-sm {
            width: 36px;
            height: 36px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }
        
        .table .btn-sm:hover {
            transform: translateY(-2px);
        }
        
        .table .btn-info.btn-sm {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            border: none;
            color: #fff;
            box-shadow: 0 3px 10px rgba(6, 182, 212, 0.3);
        }
        
        .table .btn-warning.btn-sm {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border: none;
            color: #fff;
            box-shadow: 0 3px 10px rgba(245, 158, 11, 0.3);
        }
        
        .table .btn-danger.btn-sm {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border: none;
            box-shadow: 0 3px 10px rgba(239, 68, 68, 0.3);
        }
        
        .table .btn-primary.btn-sm {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border: none;
            box-shadow: 0 3px 10px rgba(59, 130, 246, 0.3);
        }
        
        .table .btn-success.btn-sm {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            box-shadow: 0 3px 10px rgba(16, 185, 129, 0.3);
        }
        
        /* Enhanced Badges in Tables */
        .table .badge {
            padding: 8px 14px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.75rem;
            letter-spacing: 0.3px;
            min-width: 55px;
            text-align: center;
            display: inline-block;
        }
        
        .badge-status-actif,
        .badge.bg-success-soft {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%) !important;
            color: #065f46 !important;
            border: 1px solid #10b981;
        }
        
        .badge-status-inactif,
        .badge.bg-danger-soft {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%) !important;
            color: #991b1b !important;
            border: 1px solid #ef4444;
        }
        
        .badge-non,
        .badge.bg-secondary {
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%) !important;
            color: #475569 !important;
            border: 1px solid #94a3b8;
        }
        
        .badge-oui,
        .badge.bg-warning {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%) !important;
            color: #92400e !important;
            border: 1px solid #f59e0b;
        }
        
        /* Table row hover improvement */
        .table > tbody > tr {
            transition: all 0.2s ease;
        }
        
        .table > tbody > tr:hover {
            background-color: #f8fafc !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        
        /* Table header improvement */
        .table > thead > tr > th {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-bottom: 2px solid #e2e8f0;
            color: #374151;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 16px;
            white-space: nowrap;
        }
        
        .table > tbody > tr > td {
            padding: 16px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }
        
        /* Page header buttons improvement */
        .page-header-actions .btn {
            padding: 12px 20px;
            font-weight: 600;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .page-header-actions .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.15);
        }
        
        /* Card body padding for tables */
        .card-body .table-responsive {
            margin: 2.5rem;
            margin-top: 0;
            overflow: visible;
        }
        
        .card-body > .table-responsive:first-child {
            margin-top: 2.5rem;
        }
        
        .card-body .table-responsive .table {
            margin-bottom: 0;
        }
        
        /* Scrollbar */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.2);
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div id="app">
        <!-- Sidebar Overlay (mobile) -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="{{ url('/') }}" class="sidebar-brand">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" style="height: 40px; margin-right: 8px;">
                    <span>Logistique BE</span>
                </a>
            </div>
            
            <div class="sidebar-menu">
                <ul class="sidebar-nav">
                    <li class="sidebar-nav-item">
                        <a href="{{ route('dashboard') }}" class="sidebar-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Tableau de bord</span>
                        </a>
                    </li>
                </ul>
                
                <div class="sidebar-menu-title">Gestion</div>
                <ul class="sidebar-nav">
                    <li class="sidebar-nav-item">
                        <a href="{{ route('conducteurs.index') }}" class="sidebar-nav-link {{ request()->routeIs('conducteurs.*') ? 'active' : '' }}">
                            <i class="fas fa-id-card"></i>
                            <span>Conducteurs</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('bus.index') }}" class="sidebar-nav-link {{ request()->routeIs('bus.*') ? 'active' : '' }}">
                            <i class="fas fa-bus"></i>
                            <span>Bus</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('lignes.index') }}" class="sidebar-nav-link {{ request()->routeIs('lignes.*') ? 'active' : '' }}">
                            <i class="fas fa-route"></i>
                            <span>Lignes</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('villes.index') }}" class="sidebar-nav-link {{ request()->routeIs('villes.*') ? 'active' : '' }}">
                            <i class="fas fa-city"></i>
                            <span>Villes</span>
                        </a>
                    </li>
                </ul>
                
                <div class="sidebar-menu-title">Programmation</div>
                <ul class="sidebar-nav">
                    <li class="sidebar-nav-item">
                        <a href="{{ route('voyages.historique') }}" class="sidebar-nav-link {{ request()->routeIs('voyages.historique') ? 'active' : '' }}">
                            <i class="fas fa-history"></i>
                            <span>Historique Voyages</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('voyages.generer.form') }}" class="sidebar-nav-link {{ request()->routeIs('voyages.generer.*') ? 'active' : '' }}">
                            <i class="fas fa-magic"></i>
                            <span>Générer Programmation</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('voyages.index') }}" class="sidebar-nav-link {{ request()->routeIs('voyages.index') || request()->routeIs('voyages.show') ? 'active' : '' }}">
                            <i class="fas fa-list"></i>
                            <span>Liste Voyages</span>
                        </a>
                    </li>
                </ul>
                
                <div class="sidebar-menu-title">Analyse</div>
                <ul class="sidebar-nav">
                    <li class="sidebar-nav-item">
                        <a href="{{ route('statistiques.index') }}" class="sidebar-nav-link {{ request()->routeIs('statistiques.index') ? 'active' : '' }}">
                            <i class="fas fa-chart-pie"></i>
                            <span>Vue d'ensemble</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('statistiques.conducteurs') }}" class="sidebar-nav-link {{ request()->routeIs('statistiques.conducteurs') || request()->routeIs('statistiques.conducteur.detail') ? 'active' : '' }}">
                            <i class="fas fa-user-chart"></i>
                            <span>Stats Conducteurs</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('statistiques.bus') }}" class="sidebar-nav-link {{ request()->routeIs('statistiques.bus') || request()->routeIs('statistiques.bus.detail') ? 'active' : '' }}">
                            <i class="fas fa-bus-simple"></i>
                            <span>Stats Bus</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('statistiques.lignes') }}" class="sidebar-nav-link {{ request()->routeIs('statistiques.lignes') ? 'active' : '' }}">
                            <i class="fas fa-road"></i>
                            <span>Stats Lignes</span>
                        </a>
                    </li>
                </ul>
                
                @auth
                @if(auth()->user()->canAccessSettings())
                <div class="sidebar-menu-title">Configuration</div>
                <ul class="sidebar-nav">
                    <li class="sidebar-nav-item">
                        <a href="{{ route('configuration.settings') }}" class="sidebar-nav-link {{ request()->routeIs('configuration.settings') || request()->routeIs('configuration.logo') ? 'active' : '' }}">
                            <i class="fas fa-cogs"></i>
                            <span>Paramètres & Logo</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('configuration.criteres.index') }}" class="sidebar-nav-link {{ request()->routeIs('configuration.criteres.*') ? 'active' : '' }}">
                            <i class="fas fa-sliders-h"></i>
                            <span>Critères Programmation</span>
                        </a>
                    </li>
                    @if(auth()->user()->canManageUsers())
                    <li class="sidebar-nav-item">
                        <a href="{{ route('configuration.users.index') }}" class="sidebar-nav-link {{ request()->routeIs('configuration.users.*') ? 'active' : '' }}">
                            <i class="fas fa-users-cog"></i>
                            <span>Utilisateurs</span>
                        </a>
                    </li>
                    @endif
                </ul>
                @endif
                @endauth
            </div>
        </aside>
        
        <!-- Main Wrapper -->
        <div class="main-wrapper">
            <!-- Topbar -->
            <header class="topbar">
                <button class="topbar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="topbar-search position-relative d-none d-md-block">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="form-control" placeholder="Rechercher...">
                </div>
                
                <div class="topbar-right">
                    <button class="topbar-icon-btn" title="Notifications">
                        <i class="fas fa-bell"></i>
                        <span class="badge bg-danger">3</span>
                    </button>
                    
                    @guest
                        <a href="{{ route('login') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-sign-in-alt me-1"></i> Connexion
                        </a>
                    @else
                        <div class="dropdown">
                            <div class="user-dropdown" data-bs-toggle="dropdown">
                                <div class="user-avatar">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </div>
                                <div class="user-info d-none d-lg-block">
                                    <div class="user-name">{{ Auth::user()->name }}</div>
                                    <div class="user-role">Administrateur</div>
                                </div>
                                <i class="fas fa-chevron-down ms-2 text-muted d-none d-lg-block"></i>
                            </div>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i> Mon profil</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i> Paramètres</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @endguest
                </div>
            </header>
            
            <!-- Main Content -->
            <main class="main-content">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- jQuery (required for DataTables) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <!-- DataTables Buttons -->
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/fr.js"></script>
    
    <script>
        // Sidebar Toggle
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        });
        
        document.getElementById('sidebarOverlay').addEventListener('click', function() {
            document.getElementById('sidebar').classList.remove('show');
            this.classList.remove('show');
        });
        
        // Initialiser Select2 sur tous les selects avec la classe select2
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap-5',
                language: 'fr',
                allowClear: true,
                placeholder: 'Rechercher...',
                width: '100%'
            });
        });
    </script>
    
    <script>
        $(document).ready(function() {
            var table = $('.datatable').DataTable({
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json'
                },
                pageLength: 50,
                lengthMenu: [[50, 25, 100, -1], [50, 25, 100, "Tout"]],
                order: [],
                dom: '<"row"<"col-md-6"B><"col-md-6"f>>rt<"row"<"col-md-6"l><"col-md-6"p>>i',
                buttons: [
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-success btn-sm',
                        exportOptions: {
                            columns: ':not(.no-export)'
                        }
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger btn-sm',
                        exportOptions: {
                            columns: ':not(.no-export)'
                        }
                    },
                    {
                        extend: 'csv',
                        text: '<i class="fas fa-file-csv"></i> CSV',
                        className: 'btn btn-info btn-sm',
                        exportOptions: {
                            columns: ':not(.no-export)'
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Imprimer',
                        className: 'btn btn-secondary btn-sm',
                        exportOptions: {
                            columns: ':not(.no-export)'
                        }
                    }
                ],
                columnDefs: [
                    { orderable: false, targets: 'no-sort' },
                    { orderable: false, searchable: false, targets: 'row-num' }
                ],
                drawCallback: function(settings) {
                    var api = this.api();
                    api.column('.row-num', {search: 'applied', order: 'applied'}).nodes().each(function(cell, i) {
                        cell.innerHTML = api.page.info().start + i + 1;
                    });
                }
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
