@extends('layouts.guest')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">
                <div class="icon-fallback">
                    <i class="fas fa-lock-open"></i>
                </div>
            </div>
            <h1>Nouveau mot de passe</h1>
            <p>Créez un nouveau mot de passe</p>
        </div>

        <div class="auth-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    Veuillez corriger les erreurs ci-dessous.
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope"></i>
                        Adresse email
                    </label>
                    <input id="email" type="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           name="email" value="{{ $email ?? old('email') }}" 
                           required autocomplete="email" autofocus>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i>
                        Nouveau mot de passe
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
                    <i class="fas fa-check"></i>
                    Réinitialiser le mot de passe
                </button>
            </form>
        </div>

        <div class="auth-footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.</p>
        </div>
    </div>
</div>
@endsection
