@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Importer des conducteurs</div>

                <div class="card-body">
                    <div class="alert alert-info">
                        <h5>Format du fichier CSV</h5>
                        <p>Le fichier doit être au format CSV avec le séparateur <strong>point-virgule (;)</strong></p>
                        <p>Colonnes attendues (en-tête obligatoire) :</p>
                        <ul>
                            <li><strong>nom</strong> - Nom du conducteur (obligatoire)</li>
                            <li><strong>prenom</strong> - Prénom du conducteur (obligatoire)</li>
                            <li><strong>ville_actuelle</strong> - Ville actuelle (défaut: Parakou)</li>
                            <li><strong>famille_hors_parakou</strong> - Oui/Non</li>
                            <li><strong>specialiste_nuit</strong> - Oui/Non</li>
                            <li><strong>remplacant_nuit</strong> - Oui/Non</li>
                            <li><strong>actif</strong> - Oui/Non (défaut: Oui)</li>
                        </ul>
                    </div>

                    <div class="alert alert-secondary">
                        <h6>Exemple de fichier CSV :</h6>
                        <code>
                            nom;prenom;ville_actuelle;famille_hors_parakou;specialiste_nuit;remplacant_nuit;actif<br>
                            Dupont;Jean;Parakou;Non;Oui;Non;Oui<br>
                            Martin;Pierre;Cotonou;Oui;Non;Non;Oui
                        </code>
                    </div>

                    <form action="{{ route('conducteurs.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="file" class="form-label">Fichier CSV</label>
                            <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file" accept=".csv,.txt" required>
                            @error('file')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                            <div class="form-text">Fichier CSV avec séparateur point-virgule (max 2 Mo)</div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="delete_existing" name="delete_existing" value="1">
                                <label class="form-check-label text-danger" for="delete_existing">
                                    <strong>Supprimer tous les conducteurs existants avant l'importation</strong>
                                </label>
                                <div class="form-text text-danger">
                                    Attention : Cette option supprimera aussi les voyages, repos et indisponibilités liés !
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('conducteurs.index') }}" class="btn btn-secondary">Annuler</a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-upload"></i> Importer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
