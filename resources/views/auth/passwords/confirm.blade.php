@extends('layouts.guest')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">
                <div class="icon-fallback">
                    <i class="fas fa-shield-alt"></i>
                </div>
            </div>
            <h1>Confirmation</h1>
            <p>Confirmez votre mot de passe</p>
        </div>

        <div class="auth-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    Mot de passe incorrect.
                </div>
            @endif

            <div style="text-align: center; color: #64748b; margin-bottom: 25px;">
                <p>Veuillez confirmer votre mot de passe avant de continuer.</p>
            </div>

            <form method="POST" action="{{ route('password.confirm') }}">
                @csrf

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
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-check"></i>
                    Confirmer
                </button>

                @if (Route::has('password.request'))
                    <div style="text-align: center; margin-top: 20px;">
                        <a href="{{ route('password.request') }}" class="forgot-link">
                            Mot de passe oublié ?
                        </a>
                    </div>
                @endif
            </form>
        </div>

        <div class="auth-footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.</p>
        </div>
    </div>
</div>
@endsection
