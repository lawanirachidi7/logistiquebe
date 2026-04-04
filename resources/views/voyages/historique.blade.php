@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">
                <i class="fas fa-history"></i>
                Historique des Voyages
            </h1>
            <p class="page-subtitle">Consultez et gérez l'historique des voyages</p>
        </div>
        <div class="page-header-actions">
            @canaction
            <form action="{{ route('voyages.editByDate') }}" method="GET" class="d-flex gap-2">
                <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Modifier par date
                </button>
            </form>
            @endcanaction
            <form action="{{ route('voyages.pdf') }}" method="GET" class="d-flex gap-2">
                <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}">
                <button type="submit" class="btn btn-info">
                    <i class="fas fa-file-pdf"></i> Télécharger PDF
                </button>
            </form>
        </div>
    </div>
            
    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped datatable">
                    <thead>
                        <tr>
                            <th class="row-num">N°</th>
                            <th>Date départ</th>
                            <th>Conducteur</th>
                            <th>Bus</th>
                            <th>Ligne</th>
                            <th>Période</th>
                            <th>Sens</th>
                            <th>Statut</th>
                            <th class="no-sort no-export">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($voyages as $voyage)
                        <tr>
                            <td></td>
                            <td class="fw-semibold">{{ $voyage->date_depart }}</td>
                            <td>
                                {{ $voyage->conducteur->nom }} {{ $voyage->conducteur->prenom }}
                                <br><small class="text-muted">{{ $voyage->conducteur->ville_actuelle }}</small>
                                @if($voyage->conducteur2)
                                    <br><span class="badge bg-info">+ {{ $voyage->conducteur2->prenom }} {{ $voyage->conducteur2->nom }}</span>
                                    <br><small class="text-muted">{{ $voyage->conducteur2->ville_actuelle }}</small>
                                @endif
                            </td>
                            <td><span class="badge bg-secondary">{{ $voyage->bus->immatriculation }}</span></td>
                            <td>
                                {{ $voyage->ligne->nom }}
                                <br><small class="text-muted">{{ $voyage->ligne->ville_depart }} → {{ $voyage->ligne->ville_arrivee }}</small>
                            </td>
                            <td>
                                @if($voyage->periode === 'Nuit')
                                    <span class="badge bg-dark">Nuit</span>
                                @else
                                    <span class="badge badge-oui">Jour</span>
                                @endif
                            </td>
                            <td>{{ $voyage->sens }}</td>
                            <td>
                                @if($voyage->statut === 'Terminé')
                                    <span class="badge badge-status-actif">Terminé</span>
                                @elseif($voyage->statut === 'En cours')
                                    <span class="badge badge-oui">En cours</span>
                                @elseif($voyage->statut === 'Annulé')
                                    <span class="badge badge-status-inactif">Annulé</span>
                                @else
                                    <span class="badge badge-non">Planifié</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group-actions">
                                    <a href="{{ route('voyages.show', $voyage->id) }}" class="btn btn-sm btn-info" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @canaction
                                    <a href="{{ route('voyages.edit', $voyage->id) }}" class="btn btn-sm btn-warning" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($voyage->statut !== 'Terminé')
                                        <form action="{{ route('voyages.valider', $voyage->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Valider ce voyage ?')" title="Valider">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('voyages.destroy', $voyage->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce voyage ?')" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcanaction
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal de validation par période -->
<div class="modal fade" id="validatePeriodModal" tabindex="-1" aria-labelledby="validatePeriodModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="validatePeriodModalLabel">
                    <i class="fas fa-check-double"></i> Valider les voyages d'une période
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('voyages.validateByPeriod') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Cette action marquera tous les voyages <strong>Planifiés</strong> de la période sélectionnée comme <strong>Terminés</strong> et mettra à jour les positions des conducteurs et des bus.
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="validate_date_debut" class="form-label fw-bold">Date de début</label>
                            <input type="date" class="form-control" id="validate_date_debut" name="date_debut" 
                                   value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="validate_date_fin" class="form-label fw-bold">Date de fin</label>
                            <input type="date" class="form-control" id="validate_date_fin" name="date_fin" 
                                   value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Période (Jour/Nuit)</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="periode" id="val_jour" value="Jour">
                                <label class="form-check-label" for="val_jour">Jour uniquement</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="periode" id="val_nuit" value="Nuit">
                                <label class="form-check-label" for="val_nuit">Nuit uniquement</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="periode" id="val_tous" value="Les deux" checked>
                                <label class="form-check-label" for="val_tous">Tous</label>
                            </div>
                        </div>
                    </div>
                    <div id="previewValidation" class="border rounded p-3 bg-light" style="display: none;">
                        <strong><i class="fas fa-list"></i> Aperçu :</strong>
                        <p class="mb-0" id="previewText">Chargement...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Valider les voyages
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal d'invalidation par période -->
<div class="modal fade" id="invalidatePeriodModal" tabindex="-1" aria-labelledby="invalidatePeriodModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="invalidatePeriodModalLabel">
                    <i class="fas fa-undo"></i> Invalider les voyages d'une période
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('voyages.invalidateByPeriod') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Cette action remettra les voyages <strong>Terminés</strong> au statut <strong>Planifié</strong>.
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="invalidate_date_debut" class="form-label fw-bold">Date de début</label>
                            <input type="date" class="form-control" id="invalidate_date_debut" name="date_debut" 
                                   value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="invalidate_date_fin" class="form-label fw-bold">Date de fin</label>
                            <input type="date" class="form-control" id="invalidate_date_fin" name="date_fin" 
                                   value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Période (Jour/Nuit)</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="periode" id="inv_jour" value="Jour">
                                <label class="form-check-label" for="inv_jour">Jour uniquement</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="periode" id="inv_nuit" value="Nuit">
                                <label class="form-check-label" for="inv_nuit">Nuit uniquement</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="periode" id="inv_tous" value="Les deux" checked>
                                <label class="form-check-label" for="inv_tous">Tous</label>
                            </div>
                        </div>
                    </div>
                    <div id="previewInvalidation" class="border rounded p-3 bg-light" style="display: none;">
                        <strong><i class="fas fa-list"></i> Aperçu :</strong>
                        <p class="mb-0" id="previewInvalidateText">Chargement...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-undo"></i> Invalider les voyages
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de confirmation pour supprimer tous les voyages -->
<div class="modal fade" id="deleteAllVoyagesModal" tabindex="-1" aria-labelledby="deleteAllVoyagesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteAllVoyagesModalLabel">Confirmer la suppression</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <strong>Attention !</strong> Cette action est irréversible.
                </div>
                <p>Êtes-vous sûr de vouloir supprimer <strong>tous les voyages</strong> programmés ?</p>
                <p>Nombre de voyages à supprimer : <strong>{{ $voyages->count() }}</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form action="{{ route('voyages.deleteAll') }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Supprimer tout</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Aperçu du nombre de voyages à valider
    function updatePreview() {
        var dateDebut = $('#validate_date_debut').val();
        var dateFin = $('#validate_date_fin').val();
        var periode = $('input[name="periode"]:checked').val();
        
        if (dateDebut && dateFin) {
            $('#previewValidation').show();
            
            // Requête AJAX pour compter les voyages
            $.ajax({
                url: '{{ route("voyages.countPlanifies") }}',
                method: 'GET',
                data: {
                    date_debut: dateDebut,
                    date_fin: dateFin,
                    periode: periode
                },
                success: function(response) {
                    $('#previewText').html('<span class="text-success"><strong>' + response.count + '</strong> voyage(s) planifié(s) seront validés.</span>');
                },
                error: function() {
                    $('#previewText').html('<span class="text-muted">Impossible de charger l\'aperçu.</span>');
                }
            });
        }
    }
    
    $('#validate_date_debut, #validate_date_fin').on('change', updatePreview);
    $('input[name="periode"]').on('change', updatePreview);
    
    // Validation de la date fin >= date début
    $('#validate_date_fin').on('change', function() {
        var dateDebut = $('#validate_date_debut').val();
        var dateFin = $(this).val();
        
        if (dateDebut && dateFin && dateFin < dateDebut) {
            alert('La date de fin doit être supérieure ou égale à la date de début');
            $(this).val(dateDebut);
        }
        updatePreview();
    });
    
    // Charger l'aperçu à l'ouverture du modal
    $('#validatePeriodModal').on('shown.bs.modal', function() {
        updatePreview();
    });

    // Aperçu du nombre de voyages à invalider
    function updateInvalidatePreview() {
        var dateDebut = $('#invalidate_date_debut').val();
        var dateFin = $('#invalidate_date_fin').val();
        var periode = $('#invalidatePeriodModal input[name="periode"]:checked').val();
        
        if (dateDebut && dateFin) {
            $('#previewInvalidation').show();
            
            $.ajax({
                url: '{{ route("voyages.countTermines") }}',
                method: 'GET',
                data: {
                    date_debut: dateDebut,
                    date_fin: dateFin,
                    periode: periode
                },
                success: function(response) {
                    $('#previewInvalidateText').html('<span class="text-warning"><strong>' + response.count + '</strong> voyage(s) terminé(s) seront invalidés.</span>');
                },
                error: function() {
                    $('#previewInvalidateText').html('<span class="text-muted">Impossible de charger l\'aperçu.</span>');
                }
            });
        }
    }
    
    $('#invalidate_date_debut, #invalidate_date_fin').on('change', updateInvalidatePreview);
    $('#invalidatePeriodModal input[name="periode"]').on('change', updateInvalidatePreview);
    
    $('#invalidate_date_fin').on('change', function() {
        var dateDebut = $('#invalidate_date_debut').val();
        var dateFin = $(this).val();
        
        if (dateDebut && dateFin && dateFin < dateDebut) {
            alert('La date de fin doit être supérieure ou égale à la date de début');
            $(this).val(dateDebut);
        }
        updateInvalidatePreview();
    });
    
    $('#invalidatePeriodModal').on('shown.bs.modal', function() {
        updateInvalidatePreview();
    });
});
</script>
@endpush
