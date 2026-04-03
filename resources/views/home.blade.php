@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">
                <i class="fas fa-home"></i>
                Bienvenue
            </h1>
            <p class="page-subtitle">Vous êtes connecté à {{ config('app.name') }}</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body text-center py-5">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('status') }}
                        </div>
                    @endif

                    <i class="fas fa-check-circle text-success fa-4x mb-4"></i>
                    <h3 class="mb-3">Connexion réussie !</h3>
                    <p class="text-muted mb-4">
                        Vous êtes maintenant connecté. Accédez au tableau de bord pour commencer.
                    </p>
                    <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-tachometer-alt me-2"></i>Aller au Tableau de Bord
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
