@extends('layouts.guest')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">
                @if(file_exists(public_path('images/logo.png')))
                    <img src="{{ asset('images/logo.png') }}" alt="Logo">
                @else
                    <div class="icon-fallback">
                        <i class="fas fa-user-plus"></i>
                    </div>
                @endif
            </div>
            <h1>{{ config('app.name', 'Logistique BE') }}</h1>
            <p>Créer un nouveau compte</p>
        </div>

        <div class="auth-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    Veuillez corriger les erreurs ci-dessous.
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="form-group">
                    <label for="name" class="form-label">
                        <i class="fas fa-user"></i>
                        Nom complet
                    </label>
                    <input id="name" type="text" 
                           class="form-control @error('name') is-invalid @enderror" 
                           name="name" value="{{ old('name') }}" 
                           placeholder="Votre nom"
                           required autocomplete="name" autofocus>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope"></i>
                        Adresse email
                    </label>
                    <input id="email" type="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           name="email" value="{{ old('email') }}" 
                           placeholder="votre@email.com"
                           required autocomplete="email">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i>
                        Mot de passe
                    </label>
                    <input id="password" type="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           name="password" 
                           placeholder="••••••••"
                           required autocomplete="new-password">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password-confirm" class="form-label">
                        <i class="fas fa-lock"></i>
                        Confirmer le mot de passe
                    </label>
                    <input id="password-confirm" type="password" 
                           class="form-control" 
                           name="password_confirmation" 
                           placeholder="••••••••"
                           required autocomplete="new-password">
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-user-plus"></i>
                    S'inscrire
                </button>

                <div style="text-align: center; margin-top: 20px;">
                    <a href="{{ route('login') }}" class="forgot-link">
                        <i class="fas fa-arrow-left"></i> Déjà inscrit ? Se connecter
                    </a>
                </div>
            </form>
        </div>

        <div class="auth-footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.</p>
        </div>
    </div>
</div>
@endsection
