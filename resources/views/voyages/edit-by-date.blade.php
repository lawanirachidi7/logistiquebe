@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-edit"></i> Modifier la programmation du {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                    </h4>
                    <div>
                        <form action="{{ route('voyages.editByDate') }}" method="GET" class="d-inline">
                            <div class="input-group">
                                <input type="date" name="date" value="{{ $date }}" class="form-control" onchange="this.form.submit()">
                                <button type="submit" class="btn btn-light">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    
                    @if($voyages->isEmpty())
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Aucun voyage programmé pour cette date.
                        </div>
                    @endif
                    
                    <form action="{{ route('voyages.updateByDate') }}" method="POST" id="formEditByDate">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="date" value="{{ $date }}">
                        
                        <div class="mb-3">
                            <button type="button" class="btn btn-primary" onclick="ajouterVoyage()">
                                <i class="fas fa-plus"></i> Ajouter un voyage
                            </button>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table  table-hover" id="voyages-table">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="width: 80px;">Ordre</th>
                                        <th style="width: 40px;">Suppr.</th>
                                        <th>Ligne</th>
                                        <th>Période</th>
                                        <th>Bus</th>
                                        <th>Conducteur Principal</th>
                                        <th>Conducteur Relais</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody id="voyages-tbody">
                                    @foreach($voyages as $index => $voyage)
                                    <tr id="row-{{ $index }}" class="{{ $voyage->statut === 'Terminé' ? 'table-success' : '' }}" data-index="{{ $index }}">
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="monterLigne(this)" title="Monter">
                                                <i class="fas fa-arrow-up"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="descendreLigne(this)" title="Descendre">
                                                <i class="fas fa-arrow-down"></i>
                                            </button>
                                        </td>
                                        <td class="text-center">
                                            <input type="hidden" name="voyages[{{ $index }}][id]" value="{{ $voyage->id }}">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input checkbox-delete" 
                                                       name="voyages[{{ $index }}][delete]" value="1"
                                                       onchange="toggleRowDelete(this)">
                                            </div>
                                        </td>
                                        <td>
                                            <select class="form-select select2" name="voyages[{{ $index }}][ligne_id]" required>
                                                <option value="">-- Sélectionner --</option>
                                                @foreach($lignes as $ligne)
                                                    <option value="{{ $ligne->id }}" {{ $voyage->ligne_id == $ligne->id ? 'selected' : '' }}>
                                                        {{ $ligne->nom }} ({{ $ligne->horaire_formate }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select class="form-select" name="voyages[{{ $index }}][periode]" required>
                                                <option value="Jour" {{ $voyage->periode == 'Jour' ? 'selected' : '' }}>Jour</option>
                                                <option value="Nuit" {{ $voyage->periode == 'Nuit' ? 'selected' : '' }}>Nuit</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select class="form-select select2" name="voyages[{{ $index }}][bus_id]" required>
                                                <option value="">-- Sélectionner --</option>
                                                @foreach($bus as $b)
                                                    <option value="{{ $b->id }}" {{ $voyage->bus_id == $b->id ? 'selected' : '' }}>
                                                        {{ $b->immatriculation }} ({{ $b->typeBus->nom ?? 'N/A' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select class="form-select select2" name="voyages[{{ $index }}][conducteur_id]" required>
                                                <option value="">-- Sélectionner --</option>
                                                @foreach($conducteurs as $c)
                                                    <option value="{{ $c->id }}" {{ $voyage->conducteur_id == $c->id ? 'selected' : '' }}>
                                                        {{ $c->prenom }} {{ $c->nom }} ({{ $c->ville_actuelle }})
                                                        @if($c->specialiste_nuit) - Spéc. Nuit @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select class="form-select select2" name="voyages[{{ $index }}][conducteur_2_id]">
                                                <option value="">-- Aucun --</option>
                                                @foreach($conducteurs as $c)
                                                    <option value="{{ $c->id }}" {{ $voyage->conducteur_2_id == $c->id ? 'selected' : '' }}>
                                                        {{ $c->prenom }} {{ $c->nom }} ({{ $c->ville_actuelle }})
                                                        @if($c->specialiste_nuit) - Spéc. Nuit @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select class="form-select" name="voyages[{{ $index }}][statut]">
                                                <option value="Planifié" {{ $voyage->statut == 'Planifié' ? 'selected' : '' }}>Planifié</option>
                                                <option value="En cours" {{ $voyage->statut == 'En cours' ? 'selected' : '' }}>En cours</option>
                                                <option value="Terminé" {{ $voyage->statut == 'Terminé' ? 'selected' : '' }}>Terminé</option>
                                                <option value="Annulé" {{ $voyage->statut == 'Annulé' ? 'selected' : '' }}>Annulé</option>
                                            </select>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="d-flex gap-2 mt-3 flex-wrap">
                            <a href="{{ route('voyages.historique') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save"></i> Enregistrer
                            </button>
                            @if($voyages->count() > 0)
                            <a href="{{ route('voyages.pdf', ['date' => $date]) }}" class="btn btn-info btn-lg" target="_blank">
                                <i class="fas fa-file-pdf"></i> Télécharger PDF
                            </a>
                            <form action="{{ route('voyages.validateByDate') }}" method="POST" class="d-inline" onsubmit="return confirm('Valider tous les voyages de cette date comme effectués ?');">
                                @csrf
                                <input type="hidden" name="date" value="{{ $date }}">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-check-double"></i> Valider tous
                                </button>
                            </form>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteByDateModal">
                                <i class="fas fa-trash"></i> Supprimer tous
                            </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation pour supprimer tous les voyages de la date -->
<div class="modal fade" id="deleteByDateModal" tabindex="-1" aria-labelledby="deleteByDateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteByDateModalLabel">Confirmer la suppression</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <strong>Attention !</strong> Cette action est irréversible.
                </div>
                <p>Êtes-vous sûr de vouloir supprimer <strong>tous les voyages</strong> du {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }} ?</p>
                <p>Nombre de voyages à supprimer : <strong>{{ $voyages->count() }}</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form action="{{ route('voyages.deleteByDate') }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="date" value="{{ $date }}">
                    <button type="submit" class="btn btn-danger">Supprimer tout</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var newRowIndex = {{ $voyages->count() }};

// Options pour les selects (générées depuis PHP)
var lignesOptions = `<option value="">-- Sélectionner --</option>
@foreach($lignes as $ligne)
<option value="{{ $ligne->id }}">{{ $ligne->nom }} ({{ $ligne->horaire_formate }})</option>
@endforeach`;

var busOptions = `<option value="">-- Sélectionner --</option>
@foreach($bus as $b)
<option value="{{ $b->id }}">{{ $b->immatriculation }} ({{ $b->typeBus->nom ?? 'N/A' }})</option>
@endforeach`;

var conducteursOptions = `<option value="">-- Sélectionner --</option>
@foreach($conducteurs as $c)
<option value="{{ $c->id }}">{{ $c->prenom }} {{ $c->nom }} ({{ $c->ville_actuelle }})@if($c->specialiste_nuit) - Spéc. Nuit @endif</option>
@endforeach`;

var conducteursRelaisOptions = `<option value="">-- Aucun --</option>
@foreach($conducteurs as $c)
<option value="{{ $c->id }}">{{ $c->prenom }} {{ $c->nom }} ({{ $c->ville_actuelle }})@if($c->specialiste_nuit) - Spéc. Nuit @endif</option>
@endforeach`;

function ajouterVoyage() {
    var tbody = document.getElementById('voyages-tbody');
    var newRow = document.createElement('tr');
    newRow.id = 'row-' + newRowIndex;
    newRow.dataset.index = newRowIndex;
    newRow.classList.add('table-info');
    
    newRow.innerHTML = `
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="monterLigne(this)" title="Monter">
                <i class="fas fa-arrow-up"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="descendreLigne(this)" title="Descendre">
                <i class="fas fa-arrow-down"></i>
            </button>
        </td>
        <td class="text-center">
            <input type="hidden" name="voyages[${newRowIndex}][id]" value="">
            <button type="button" class="btn btn-sm btn-danger" onclick="supprimerLigne(this)" title="Supprimer">
                <i class="fas fa-times"></i>
            </button>
        </td>
        <td>
            <select class="form-select" name="voyages[${newRowIndex}][ligne_id]" required>
                ${lignesOptions}
            </select>
        </td>
        <td>
            <select class="form-select" name="voyages[${newRowIndex}][periode]" required>
                <option value="Jour">Jour</option>
                <option value="Nuit">Nuit</option>
            </select>
        </td>
        <td>
            <select class="form-select" name="voyages[${newRowIndex}][bus_id]" required>
                ${busOptions}
            </select>
        </td>
        <td>
            <select class="form-select" name="voyages[${newRowIndex}][conducteur_id]" required>
                ${conducteursOptions}
            </select>
        </td>
        <td>
            <select class="form-select" name="voyages[${newRowIndex}][conducteur_2_id]">
                ${conducteursRelaisOptions}
            </select>
        </td>
        <td>
            <select class="form-select" name="voyages[${newRowIndex}][statut]">
                <option value="Planifié" selected>Planifié</option>
                <option value="En cours">En cours</option>
                <option value="Terminé">Terminé</option>
                <option value="Annulé">Annulé</option>
            </select>
        </td>
    `;
    
    tbody.appendChild(newRow);
    newRowIndex++;
    
    // Initialiser Select2 sur les nouveaux selects
    $(newRow).find('select').each(function() {
        if ($(this).hasClass('select2') || $(this).closest('td').index() > 1) {
            // Select2 simple pour ces champs
        }
    });
}

function supprimerLigne(button) {
    var row = button.closest('tr');
    row.remove();
}

function toggleRowDelete(checkbox) {
    var row = checkbox.closest('tr');
    
    if (checkbox.checked) {
        row.classList.add('table-danger');
        row.querySelectorAll('select').forEach(function(el) {
            el.disabled = true;
        });
    } else {
        row.classList.remove('table-danger');
        row.querySelectorAll('select').forEach(function(el) {
            el.disabled = false;
        });
    }
}

function monterLigne(button) {
    var row = button.closest('tr');
    var prevRow = row.previousElementSibling;
    if (prevRow) {
        row.parentNode.insertBefore(row, prevRow);
    }
}

function descendreLigne(button) {
    var row = button.closest('tr');
    var nextRow = row.nextElementSibling;
    if (nextRow) {
        row.parentNode.insertBefore(nextRow, row);
    }
}
</script>
@endpush
