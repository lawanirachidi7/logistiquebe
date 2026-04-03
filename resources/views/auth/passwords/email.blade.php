@extends('layouts.guest')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">
                <div class="icon-fallback">
                    <i class="fas fa-key"></i>
                </div>
            </div>
            <h1>Mot de passe oublié</h1>
            <p>Réinitialisez votre mot de passe</p>
        </div>

        <div class="auth-body">
            @if (session('status'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    Veuillez corriger les erreurs ci-dessous.
                </div>
            @endif

            <div style="text-align: center; color: #64748b; margin-bottom: 25px;">
                <p>Entrez votre adresse email et nous vous enverrons un lien pour réinitialiser votre mot de passe.</p>
            </div>

            <form method="POST" action="{{ route('password.email') }}">
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
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-paper-plane"></i>
                    Envoyer le lien de réinitialisation
                </button>

                <div style="text-align: center; margin-top: 20px;">
                    <a href="{{ route('login') }}" class="forgot-link">
                        <i class="fas fa-arrow-left"></i> Retour à la connexion
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
