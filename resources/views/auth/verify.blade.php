@extends('layouts.guest')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">
                <div class="icon-fallback">
                    <i class="fas fa-envelope-open-text"></i>
                </div>
            </div>
            <h1>Vérification Email</h1>
            <p>Confirmez votre adresse email</p>
        </div>

        <div class="auth-body">
            @if (session('resent'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    Un nouveau lien de vérification a été envoyé à votre adresse email.
                </div>
            @endif

            <div style="text-align: center; color: #64748b; margin-bottom: 25px;">
                <p style="margin-bottom: 15px;">
                    <i class="fas fa-info-circle" style="color: #3b82f6; font-size: 1.2rem;"></i>
                </p>
                <p>Avant de continuer, veuillez vérifier votre email pour le lien de vérification.</p>
                <p style="margin-top: 15px;">Si vous n'avez pas reçu l'email :</p>
            </div>

            <form method="POST" action="{{ route('verification.resend') }}">
                @csrf
                <button type="submit" class="btn-login">
                    <i class="fas fa-paper-plane"></i>
                    Renvoyer l'email de vérification
                </button>
            </form>
        </div>

        <div class="auth-footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.</p>
        </div>
    </div>
</div>
@endsection
