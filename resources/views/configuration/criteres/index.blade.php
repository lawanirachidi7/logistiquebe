@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">
                <i class="fas fa-sliders-h"></i>
                Critères de Programmation
            </h1>
            <p class="page-subtitle">Configurez les règles et paramètres de génération automatique</p>
        </div>
        <div class="page-header-actions">
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fas fa-upload"></i> Importer
            </button>
            <a href="{{ route('configuration.criteres.export') }}" class="btn btn-info">
                <i class="fas fa-download"></i> Exporter
            </a>
            <form action="{{ route('configuration.criteres.reset') }}" method="POST" class="d-inline" 
                  onsubmit="return confirm('Réinitialiser TOUS les critères aux valeurs par défaut ?');">
                @csrf
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-undo"></i> Réinitialiser tout
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Info Box -->
    <div class="alert alert-info mb-4">
        <i class="fas fa-info-circle me-2"></i>
        <strong>À propos des critères :</strong> Ces paramètres contrôlent le comportement de la génération automatique des voyages.
        Les critères désactivés seront ignorés lors de la programmation.
    </div>

    <form action="{{ route('configuration.criteres.update') }}" method="POST">
        @csrf
        @method('PUT')

        @foreach($criteres as $categorie => $listeCriteres)
        <div class="card mb-4">
            <div class="card-header bg-dark">
                <i class="fas {{ $categoriesIcons[$categorie] ?? 'fa-cog' }} me-2"></i>
                {{ $categoriesLabels[$categorie] ?? ucfirst($categorie) }}
                <span class="badge bg-secondary ms-2">{{ count($listeCriteres) }} critère(s)</span>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($listeCriteres as $critere)
                    <div class="col-lg-6 mb-4">
                        <div class="card h-100 {{ !$critere['actif'] ? 'border-secondary opacity-50' : 'border-primary' }}">
                            <div class="card-header d-flex justify-content-between align-items-center py-2">
                                <span class="fw-bold">
                                    @if(!$critere['actif'])
                                        <i class="fas fa-ban text-danger me-1" title="Désactivé"></i>
                                    @endif
                                    {{ $critere['libelle'] }}
                                </span>
                                <form action="{{ route('configuration.criteres.toggle', $critere['id']) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $critere['actif'] ? 'btn-outline-danger' : 'btn-outline-success' }}" 
                                            title="{{ $critere['actif'] ? 'Désactiver' : 'Activer' }}">
                                        <i class="fas {{ $critere['actif'] ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                    </button>
                                </form>
                            </div>
                            <div class="card-body">
                                @if($critere['description'])
                                    <p class="text-muted small mb-3">
                                        <i class="fas fa-info-circle me-1"></i>{{ $critere['description'] }}
                                    </p>
                                @endif

                                @if($critere['type'] === 'boolean')
                                    <div class="form-check form-switch form-switch-lg">
                                        <input class="form-check-input" type="checkbox" 
                                               id="{{ $critere['cle'] }}" name="{{ $critere['cle'] }}" 
                                               value="1" {{ $critere['valeur'] ? 'checked' : '' }}
                                               {{ !$critere['actif'] ? 'disabled' : '' }}>
                                        <label class="form-check-label" for="{{ $critere['cle'] }}">
                                            {{ $critere['valeur'] ? 'Activé' : 'Désactivé' }}
                                        </label>
                                    </div>
                                @elseif($critere['type'] === 'integer')
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                        <input type="number" class="form-control" 
                                               id="{{ $critere['cle'] }}" name="{{ $critere['cle'] }}" 
                                               value="{{ $critere['valeur'] }}" min="0" max="100"
                                               {{ !$critere['actif'] ? 'disabled' : '' }}>
                                    </div>
                                @elseif($critere['type'] === 'time')
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                        <input type="time" class="form-control" 
                                               id="{{ $critere['cle'] }}" name="{{ $critere['cle'] }}" 
                                               value="{{ $critere['valeur'] }}"
                                               {{ !$critere['actif'] ? 'disabled' : '' }}>
                                    </div>
                                @else
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-font"></i></span>
                                        <input type="text" class="form-control" 
                                               id="{{ $critere['cle'] }}" name="{{ $critere['cle'] }}" 
                                               value="{{ $critere['valeur'] }}"
                                               {{ !$critere['actif'] ? 'disabled' : '' }}>
                                    </div>
                                @endif

                                <div class="mt-2 d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="fas fa-code me-1"></i><code>{{ $critere['cle'] }}</code>
                                    </small>
                                    <small class="text-muted">
                                        Défaut: <strong>{{ $critere['valeur_defaut'] }}</strong>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach

        <div class="d-flex gap-2 mb-4">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save me-1"></i> Enregistrer les critères
            </button>
            <a href="{{ route('voyages.generer.form') }}" class="btn btn-outline-success btn-lg">
                <i class="fas fa-magic me-1"></i> Tester la génération
            </a>
        </div>
    </form>
</div>

<!-- Modal Import -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-upload me-2"></i>Importer des critères</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('configuration.criteres.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="fichier" class="form-label">Fichier JSON</label>
                        <input type="file" class="form-control" name="fichier" id="fichier" accept=".json" required>
                        <small class="text-muted">Sélectionnez un fichier JSON exporté précédemment</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-upload me-1"></i> Importer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.form-switch-lg .form-check-input {
    width: 3em;
    height: 1.5em;
}
</style>
@endsection
