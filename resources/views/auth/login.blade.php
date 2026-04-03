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
                        <i class="fas fa-bus"></i>
                    </div>
                @endif
            </div>
            <h1>{{ config('app.name', 'Logistique BE') }}</h1>
            <p>Connectez-vous à votre espace</p>
        </div>

        <div class="auth-body">
            @if(session('status'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    Identifiants incorrects. Veuillez réessayer.
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope"></i>
                        Adresse email
                    </label>
                    <input id="email" type="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           name="email" value="{{ old('email') }}" 
                           placeholder="votre@email.com"
                           required autocomplete="email" autofocus>
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
                           required autocomplete="current-password">
                </div>

                <div class="form-options">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">
                            Se souvenir de moi
                        </label>
                    </div>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot-link">
                            Mot de passe oublié ?
                        </a>
                    @endif
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i>
                    Se connecter
                </button>
            </form>
        </div>

        <div class="auth-footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.</p>
        </div>
    </div>
</div>
@endsection
