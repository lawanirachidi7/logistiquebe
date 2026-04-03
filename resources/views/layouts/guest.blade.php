<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Connexion</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito:400,600,700,800" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        :root {
            --primary-color: #3b82f6;
            --primary-dark: #1d4ed8;
            --primary-gradient: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            --bg-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Nunito', sans-serif;
            min-height: 100vh;
            background: var(--bg-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .auth-container {
            width: 100%;
            max-width: 450px;
        }
        
        .auth-card {
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            animation: slideUp 0.5s ease;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .auth-header {
            background: var(--primary-gradient);
            padding: 50px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .auth-header::before {
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
        
        .auth-logo {
            position: relative;
            z-index: 1;
        }
        
        .auth-logo img {
            max-height: 80px;
            margin-bottom: 20px;
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
        }
        
        .auth-logo .icon-fallback {
            width: 90px;
            height: 90px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2.5rem;
            color: #fff;
        }
        
        .auth-header h1 {
            color: #fff;
            font-size: 1.75rem;
            font-weight: 800;
            margin: 0;
            position: relative;
            z-index: 1;
        }
        
        .auth-header p {
            color: rgba(255, 255, 255, 0.9);
            margin: 10px 0 0;
            font-size: 0.95rem;
            position: relative;
            z-index: 1;
        }
        
        .auth-body {
            padding: 40px;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }
        
        .form-label i {
            color: var(--primary-color);
            font-size: 0.85rem;
        }
        
        .form-control {
            width: 100%;
            padding: 14px 18px;
            font-size: 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            transition: all 0.3s ease;
            background: #f8fafc;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
        }
        
        .form-control.is-invalid {
            border-color: #ef4444;
        }
        
        .form-control.is-invalid:focus {
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.15);
        }
        
        .invalid-feedback {
            color: #ef4444;
            font-size: 0.85rem;
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .invalid-feedback::before {
            content: '\f06a';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
        }
        
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
        }
        
        .form-check {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-check-input {
            width: 20px;
            height: 20px;
            border: 2px solid #cbd5e1;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .form-check-label {
            color: #64748b;
            font-size: 0.9rem;
            cursor: pointer;
        }
        
        .forgot-link {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: color 0.2s ease;
        }
        
        .forgot-link:hover {
            color: var(--primary-dark);
        }
        
        .btn-login {
            width: 100%;
            padding: 16px;
            background: var(--primary-gradient);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 14px rgba(59, 130, 246, 0.4);
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.5);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .auth-footer {
            text-align: center;
            padding: 25px 40px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
        }
        
        .auth-footer p {
            color: #94a3b8;
            font-size: 0.85rem;
            margin: 0;
        }
        
        .auth-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }
        
        /* Alert styles */
        .alert {
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
        }
        
        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            .auth-header {
                padding: 40px 30px;
            }
            
            .auth-body {
                padding: 30px;
            }
            
            .auth-footer {
                padding: 20px 30px;
            }
            
            .form-options {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    @yield('content')
    
    @stack('scripts')
</body>
</html>
