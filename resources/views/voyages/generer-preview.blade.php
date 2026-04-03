@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Aperçu de la Programmation - {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</h4>
                    <span class="badge bg-light text-dark">
                        {{ implode(' & ', $periodes) }}
                    </span>
                </div>
                <div class="card-body">
                    
                    @php
                        $voyagesSuiteVeille = collect($propositions)->whereIn('source', ['retour_suite_aller', 'aller_suite_retour'])->count();
                        $voyagesNouveaux = collect($propositions)->where('source', 'nouveau')->count();
                    @endphp

                    <div class="alert {{ isset($voyagesHierCount) && $voyagesHierCount > 0 ? 'alert-info' : 'alert-warning' }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                @if(isset($voyagesHierCount) && $voyagesHierCount > 0)
                                    <i class="fas fa-calendar-check"></i> 
                                    <strong>{{ $voyagesHierCount }} voyage(s)</strong> trouvé(s) pour la veille ({{ \Carbon\Carbon::parse($date)->subDay()->format('d/m/Y') }})
                                @else
                                    <i class="fas fa-calendar-times"></i> 
                                    <strong>Aucun voyage</strong> pour la veille ({{ \Carbon\Carbon::parse($date)->subDay()->format('d/m/Y') }})
                                @endif
                            </div>
                            <div class="text-end">
                                @if($voyagesSuiteVeille > 0)
                                    <span class="badge bg-primary">{{ $voyagesSuiteVeille }} suite veille</span>
                                @endif
                                @if($voyagesNouveaux > 0)
                                    <span class="badge bg-secondary">{{ $voyagesNouveaux }} nouveau(x)</span>
                                @endif
                            </div>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> 
                            Toutes les lignes disponibles sont affichées. Cochez/décochez pour maintenir ou exclure.
                        </small>
                    </div>

                    @if(count($alertes) > 0)
                    <div class="alert alert-warning">
                        <h6>Alertes :</h6>
                        <ul class="mb-0">
                            @foreach($alertes as $alerte)
                            <li>{{ $alerte }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form action="{{ route('voyages.generer') }}" method="POST" id="formGenerer">
                        @csrf
                        <input type="hidden" name="date" value="{{ $date }}">
                        <input type="hidden" name="periode" value="{{ count($periodes) > 1 ? 'Les deux' : $periodes[0] }}">
                        
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover datatable">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="row-num">N°</th>
                                        <th class="no-sort">Activer</th>
                                        <th>Ligne</th>
                                        <th>Type</th>
                                        <th>Heure</th>
                                        <th>Période</th>
                                        <th>Bus proposé</th>
                                        <th>Conducteur Principal</th>
                                        <th>Conducteur Relais (Nuit)</th>
                                        <th>Statut</th>
                                        <th class="no-sort no-export">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($propositions as $index => $prop)
                                    <tr class="{{ $prop['possible'] ? '' : 'table-danger' }} {{ ($prop['periode'] === 'Nuit' && !$prop['nuit_complet']) ? 'table-warning' : '' }}" id="row-{{ $index }}">
                                        <td></td>
                                        <td>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input ligne-active" id="active-{{ $index }}" 
                                                       name="voyages[{{ $index }}][active]" value="1" checked
                                                       onchange="toggleLigne({{ $index }})">
                                            </div>
                                        </td>
                                        <td>
                                            {{ $prop['ligne']->nom }}
                                            @if(isset($prop['ligne_hier']))
                                                <br><small class="text-muted" title="Suite de: {{ $prop['ligne_hier']->nom }} à {{ \Carbon\Carbon::parse($prop['horaire_hier'])->format('H:i') }}">
                                                    <i class="fas fa-arrow-right"></i> {{ $prop['ligne_hier']->nom }} ({{ \Carbon\Carbon::parse($prop['horaire_hier'])->format('H:i') }})
                                                </small>
                                            @endif
                                            <input type="hidden" name="voyages[{{ $index }}][ligne_id]" value="{{ $prop['ligne']->id }}">
                                            <input type="hidden" name="voyages[{{ $index }}][periode]" value="{{ $prop['periode'] }}">
                                        </td>
                                        <td>
                                            @if($prop['ligne']->type === 'Aller')
                                                <span class="badge bg-primary">Aller</span>
                                            @else
                                                <span class="badge bg-success">Retour</span>
                                            @endif
                                            @if(isset($prop['source']))
                                                @if($prop['source'] === 'retour_suite_aller')
                                                    <br><small class="text-info"><i class="fas fa-reply"></i> Suite aller</small>
                                                @elseif($prop['source'] === 'aller_suite_retour')
                                                    <br><small class="text-info"><i class="fas fa-share"></i> Suite retour</small>
                                                @elseif($prop['source'] === 'nouveau')
                                                    <br><small class="text-secondary"><i class="fas fa-plus-circle"></i> Nouveau</small>
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $prop['ligne']->horaire_formate }}</strong>
                                        </td>
                                        <td>
                                            @if($prop['periode'] === 'Jour')
                                                <span class="badge bg-warning text-dark">Jour</span>
                                            @else
                                                <span class="badge bg-dark">Nuit</span>
                                            @endif
                                        </td>
                                        <td id="bus-display-{{ $index }}">
                                            @if($prop['bus'])
                                                <strong>{{ $prop['bus']->immatriculation }}</strong>
                                                <br><small class="text-muted">{{ $prop['bus']->typeBus->nom ?? '' }}</small>
                                                <input type="hidden" name="voyages[{{ $index }}][bus_id]" value="{{ $prop['bus']->id }}" id="bus-input-{{ $index }}">
                                            @else
                                                <span class="text-danger">Aucun bus disponible</span>
                                                <input type="hidden" name="voyages[{{ $index }}][bus_id]" value="" id="bus-input-{{ $index }}">
                                            @endif
                                        </td>
                                        <td id="conducteur-display-{{ $index }}">
                                            @if($prop['conducteur'])
                                                @if($prop['periode'] === 'Nuit')
                                                    <span class="badge bg-primary mb-1">Principal</span><br>
                                                @endif
                                                <strong>{{ $prop['conducteur']->prenom }} {{ $prop['conducteur']->nom }}</strong>
                                                <br><small class="text-muted">{{ $prop['conducteur']->ville_actuelle }}</small>
                                                @if($prop['conducteur']->specialiste_nuit)
                                                    <span class="badge bg-dark">Spéc. Nuit</span>
                                                @endif
                                                <input type="hidden" name="voyages[{{ $index }}][conducteur_id]" value="{{ $prop['conducteur']->id }}" id="conducteur-input-{{ $index }}">
                                            @else
                                                <span class="text-danger">Aucun conducteur à {{ $prop['ligne']->ville_depart }}</span>
                                                <input type="hidden" name="voyages[{{ $index }}][conducteur_id]" value="" id="conducteur-input-{{ $index }}">
                                            @endif
                                        </td>
                                        <td id="conducteur2-display-{{ $index }}">
                                            @if($prop['periode'] === 'Nuit')
                                                @if(isset($prop['conducteur2']) && $prop['conducteur2'])
                                                    <span class="badge bg-secondary mb-1">Relais</span><br>
                                                    <strong>{{ $prop['conducteur2']->prenom }} {{ $prop['conducteur2']->nom }}</strong>
                                                    <br><small class="text-muted">{{ $prop['conducteur2']->ville_actuelle }}</small>
                                                    @if($prop['conducteur2']->specialiste_nuit)
                                                        <span class="badge bg-dark">Spéc. Nuit</span>
                                                    @endif
                                                    <input type="hidden" name="voyages[{{ $index }}][conducteur_2_id]" value="{{ $prop['conducteur2']->id }}" id="conducteur2-input-{{ $index }}">
                                                @else
                                                    <span class="text-danger"><i class="fas fa-exclamation-triangle"></i> Relais manquant</span>
                                                    <input type="hidden" name="voyages[{{ $index }}][conducteur_2_id]" value="" id="conducteur2-input-{{ $index }}">
                                                @endif
                                            @else
                                                <span class="text-muted">Non requis (Jour)</span>
                                            @endif
                                        </td>
                                        <td id="statut-{{ $index }}">
                                            @if($prop['possible'] && $prop['nuit_complet'])
                                                <span class="badge bg-success">Prêt</span>
                                            @elseif($prop['possible'] && !$prop['nuit_complet'])
                                                <span class="badge bg-warning text-dark">1 conducteur</span>
                                            @else
                                                <span class="badge bg-secondary">Incomplet</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-primary btn-ajuster" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalAjustement"
                                                    data-index="{{ $index }}"
                                                    data-ligne-id="{{ $prop['ligne']->id }}"
                                                    data-ligne-nom="{{ $prop['ligne']->nom }}"
                                                    data-periode="{{ $prop['periode'] }}"
                                                    data-bus-id="{{ $prop['bus']?->id ?? '' }}"
                                                    data-conducteur-id="{{ $prop['conducteur']?->id ?? '' }}"
                                                    data-conducteur2-id="{{ $prop['conducteur2']?->id ?? '' }}">
                                                <i class="fas fa-edit"></i> Ajuster
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @php
                            $voyagesPossibles = collect($propositions)->where('possible', true)->count();
                            $voyagesIncomplets = collect($propositions)->where('possible', false)->count();
                            $totalPropositions = count($propositions);
                            
                            // Comptage par source (déjà calculé en haut, mais on le refait pour être sûr)
                            $voyagesSuiteAller = collect($propositions)->where('source', 'retour_suite_aller')->count();
                            $voyagesSuiteRetour = collect($propositions)->where('source', 'aller_suite_retour')->count();
                            $voyagesNouveauxResume = collect($propositions)->where('source', 'nouveau')->count();
                        @endphp

                        <div class="alert alert-secondary">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong><i class="fas fa-list"></i> Total lignes :</strong> {{ $totalPropositions }}
                                </div>
                                <div class="col-md-4">
                                    <strong class="text-success"><i class="fas fa-check"></i> Complets :</strong> {{ $voyagesPossibles }}
                                </div>
                                <div class="col-md-4">
                                    <strong class="text-danger"><i class="fas fa-times"></i> Incomplets :</strong> {{ $voyagesIncomplets }}
                                </div>
                            </div>
                            <hr class="my-2">
                            <div class="row small">
                                <div class="col-md-4">
                                    <i class="fas fa-reply text-info"></i> Suite aller hier : <strong>{{ $voyagesSuiteAller }}</strong>
                                </div>
                                <div class="col-md-4">
                                    <i class="fas fa-share text-info"></i> Suite retour hier : <strong>{{ $voyagesSuiteRetour }}</strong>
                                </div>
                                <div class="col-md-4">
                                    <i class="fas fa-plus-circle text-secondary"></i> Nouveaux : <strong>{{ $voyagesNouveauxResume }}</strong>
                                </div>
                            </div>
                        </div>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="{{ route('voyages.generer.form') }}" class="btn btn-secondary">
                                ← Retour
                            </a>
                            <button type="button" class="btn btn-success btn-lg" onclick="verifierEtSoumettre()" {{ $totalPropositions == 0 ? 'disabled' : '' }}>
                                Confirmer et générer les voyages
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmation Irrégularités -->
<div class="modal fade" id="modalIrregularites" tabindex="-1" aria-labelledby="modalIrregularitesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="modalIrregularitesLabel">
                    <i class="fas fa-exclamation-triangle"></i> Attention - Irrégularités détectées
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <strong>Des problèmes ont été détectés dans la programmation :</strong>
                </div>
                <div id="liste-irregularites"></div>
                <hr>
                <p class="mb-0">
                    <i class="fas fa-info-circle text-info"></i> 
                    Les voyages incomplets (sans bus ou conducteur) seront <strong>ignorés</strong> lors de l'enregistrement.
                    <br>Les voyages de nuit sans conducteur relais seront tout de même enregistrés.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-arrow-left"></i> Revenir et corriger
                </button>
                <button type="button" class="btn btn-warning" onclick="forcerSoumission()">
                    <i class="fas fa-check"></i> Enregistrer quand même
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ajustement -->
<div class="modal fade" id="modalAjustement" tabindex="-1" aria-labelledby="modalAjustementLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalAjustementLabel">
                    <i class="fas fa-edit"></i> Ajuster la programmation
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modal-index">
                <input type="hidden" id="modal-periode">
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Ligne</label>
                    <p id="modal-ligne-nom" class="form-control-plaintext"></p>
                </div>
                
                <div class="mb-3">
                    <label for="modal-bus" class="form-label fw-bold">Bus</label>
                    <select class="form-select select2-modal" id="modal-bus">
                        <option value="">-- Sélectionner un bus --</option>
                        @foreach(\App\Models\Bus::where('disponible', true)->get() as $bus)
                            <option value="{{ $bus->id }}" data-immat="{{ $bus->immatriculation }}" data-type="{{ $bus->typeBus->nom ?? '' }}">
                                {{ $bus->immatriculation }} ({{ $bus->typeBus->nom ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="modal-conducteur" class="form-label fw-bold">Conducteur Principal</label>
                    <select class="form-select select2-modal" id="modal-conducteur">
                        <option value="">-- Sélectionner un conducteur --</option>
                        @foreach(\App\Models\Conducteur::where('actif', true)->orderBy('nom')->get() as $cond)
                            <option value="{{ $cond->id }}" 
                                    data-nom="{{ $cond->prenom }} {{ $cond->nom }}" 
                                    data-ville="{{ $cond->ville_actuelle }}"
                                    data-nuit="{{ $cond->specialiste_nuit ? '1' : '0' }}">
                                {{ $cond->prenom }} {{ $cond->nom }} ({{ $cond->ville_actuelle }})
                                @if($cond->specialiste_nuit) - Spéc. Nuit @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="mb-3" id="div-conducteur2">
                    <label for="modal-conducteur2" class="form-label fw-bold">Conducteur Relais (Nuit)</label>
                    <select class="form-select select2-modal" id="modal-conducteur2">
                        <option value="">-- Sélectionner un conducteur relais --</option>
                        @foreach(\App\Models\Conducteur::where('actif', true)->orderBy('nom')->get() as $cond)
                            <option value="{{ $cond->id }}" 
                                    data-nom="{{ $cond->prenom }} {{ $cond->nom }}" 
                                    data-ville="{{ $cond->ville_actuelle }}"
                                    data-nuit="{{ $cond->specialiste_nuit ? '1' : '0' }}">
                                {{ $cond->prenom }} {{ $cond->nom }} ({{ $cond->ville_actuelle }})
                                @if($cond->specialiste_nuit) - Spéc. Nuit @endif
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" onclick="appliquerAjustement()">
                    <i class="fas fa-check"></i> Appliquer
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Fonction pour vérifier les irrégularités et soumettre
function verifierEtSoumettre() {
    var irregularites = [];
    var voyagesActifs = 0;
    var voyagesValides = 0;
    
    // Parcourir toutes les lignes actives
    document.querySelectorAll('tr[id^="row-"]').forEach(function(row) {
        var index = row.id.replace('row-', '');
        var checkbox = document.getElementById('active-' + index);
        
        // Si la ligne est désactivée, ignorer
        if (!checkbox || !checkbox.checked) {
            return;
        }
        
        voyagesActifs++;
        
        var busInput = row.querySelector('[name="voyages[' + index + '][bus_id]"]');
        var conducteurInput = row.querySelector('[name="voyages[' + index + '][conducteur_id]"]');
        var conducteur2Input = row.querySelector('[name="voyages[' + index + '][conducteur_2_id]"]');
        var periodeInput = row.querySelector('[name="voyages[' + index + '][periode]"]');
        var ligneInput = row.querySelector('[name="voyages[' + index + '][ligne_id]"]');
        
        // Récupérer le nom de la ligne depuis la cellule
        var ligneNom = row.cells[2] ? row.cells[2].textContent.trim().split('\n')[0] : 'Ligne #' + index;
        var periode = periodeInput ? periodeInput.value : '';
        
        var hasBus = busInput && busInput.value;
        var hasConducteur = conducteurInput && conducteurInput.value;
        var hasConducteur2 = conducteur2Input && conducteur2Input.value;
        
        if (!hasBus && !hasConducteur) {
            irregularites.push({
                type: 'danger',
                message: '<strong>' + ligneNom + '</strong> (' + periode + ') : Aucun bus ni conducteur'
            });
        } else if (!hasBus) {
            irregularites.push({
                type: 'danger',
                message: '<strong>' + ligneNom + '</strong> (' + periode + ') : Bus manquant'
            });
        } else if (!hasConducteur) {
            irregularites.push({
                type: 'danger',
                message: '<strong>' + ligneNom + '</strong> (' + periode + ') : Conducteur manquant'
            });
        } else {
            voyagesValides++;
            // Voyage valide, vérifier le relais nuit
            if (periode === 'Nuit' && !hasConducteur2) {
                irregularites.push({
                    type: 'warning',
                    message: '<strong>' + ligneNom + '</strong> (Nuit) : Conducteur relais manquant - sera enregistré avec 1 seul conducteur'
                });
            }
        }
    });
    
    // Si aucune irrégularité, soumettre directement
    if (irregularites.length === 0) {
        document.getElementById('formGenerer').submit();
        return;
    }
    
    // Construire la liste des irrégularités
    var html = '<p class="mb-2">Voyages à enregistrer : <strong>' + voyagesValides + '</strong> / ' + voyagesActifs + ' actifs</p>';
    html += '<ul class="list-unstyled">';
    
    irregularites.forEach(function(irr) {
        var icon = irr.type === 'danger' ? 'times-circle' : 'exclamation-triangle';
        var textClass = irr.type === 'danger' ? 'text-danger' : 'text-warning';
        html += '<li class="mb-2 ' + textClass + '"><i class="fas fa-' + icon + '"></i> ' + irr.message + '</li>';
    });
    
    html += '</ul>';
    
    document.getElementById('liste-irregularites').innerHTML = html;
    
    // Afficher le modal
    var modal = new bootstrap.Modal(document.getElementById('modalIrregularites'));
    modal.show();
}

function forcerSoumission() {
    document.getElementById('formGenerer').submit();
}

// Fonction pour activer/désactiver une ligne de programmation
function toggleLigne(index) {
    var checkbox = document.getElementById('active-' + index);
    var row = document.getElementById('row-' + index);
    
    if (checkbox.checked) {
        row.classList.remove('table-secondary');
        row.style.opacity = '1';
        // Réactiver les boutons seulement (pas les inputs hidden)
        row.querySelectorAll('button').forEach(function(el) {
            el.disabled = false;
        });
    } else {
        row.classList.add('table-secondary');
        row.style.opacity = '0.5';
        // Désactiver les boutons seulement (pas les inputs hidden)
        row.querySelectorAll('button').forEach(function(el) {
            el.disabled = true;
        });
    }
}

// Initialiser Select2 pour les selects du modal
$('#modalAjustement').on('shown.bs.modal', function () {
    $('.select2-modal').select2({
        theme: 'bootstrap-5',
        language: 'fr',
        allowClear: true,
        placeholder: 'Rechercher...',
        width: '100%',
        dropdownParent: $('#modalAjustement')
    });
});

// Quand le modal s'ouvre, peupler les champs avec les données du bouton cliqué
$('#modalAjustement').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var index = button.data('index');
    var ligneNom = button.data('ligne-nom');
    var periode = button.data('periode');
    var busId = button.data('bus-id');
    var conducteurId = button.data('conducteur-id');
    var conducteur2Id = button.data('conducteur2-id');
    
    // Stocker les valeurs
    $('#modal-index').val(index);
    $('#modal-periode').val(periode);
    $('#modal-ligne-nom').text(ligneNom + ' (' + periode + ')');
    
    // Sélectionner les valeurs actuelles avec trigger pour Select2
    $('#modal-bus').val(busId || '').trigger('change');
    $('#modal-conducteur').val(conducteurId || '').trigger('change');
    $('#modal-conducteur2').val(conducteur2Id || '').trigger('change');
    
    // Afficher/masquer la sélection du conducteur relais selon la période
    if (periode === 'Nuit') {
        $('#div-conducteur2').show();
    } else {
        $('#div-conducteur2').hide();
    }
});

function appliquerAjustement() {
    var index = $('#modal-index').val();
    var periode = $('#modal-periode').val();
    
    // Récupérer les sélections
    var busSelect = document.getElementById('modal-bus');
    var conducteurSelect = document.getElementById('modal-conducteur');
    var conducteur2Select = document.getElementById('modal-conducteur2');
    
    var busId = busSelect.value;
    var conducteurId = conducteurSelect.value;
    var conducteur2Id = conducteur2Select.value;
    
    // Mettre à jour les inputs cachés
    $('#bus-input-' + index).val(busId);
    $('#conducteur-input-' + index).val(conducteurId);
    $('#conducteur2-input-' + index).val(conducteur2Id);
    
    // Mettre à jour l'affichage du bus
    var busDisplay = $('#bus-display-' + index);
    if (busId) {
        var busOption = busSelect.options[busSelect.selectedIndex];
        busDisplay.html('<strong>' + busOption.dataset.immat + '</strong><br><small class="text-muted">' + busOption.dataset.type + '</small>' +
                        '<input type="hidden" name="voyages[' + index + '][bus_id]" value="' + busId + '" id="bus-input-' + index + '">');
    } else {
        busDisplay.html('<span class="text-danger">Aucun bus</span>' +
                        '<input type="hidden" name="voyages[' + index + '][bus_id]" value="" id="bus-input-' + index + '">');
    }
    
    // Mettre à jour l'affichage du conducteur principal
    var conducteurDisplay = $('#conducteur-display-' + index);
    if (conducteurId) {
        var condOption = conducteurSelect.options[conducteurSelect.selectedIndex];
        var nuitBadge = condOption.dataset.nuit === '1' ? '<span class="badge bg-dark">Spéc. Nuit</span>' : '';
        var principalBadge = periode === 'Nuit' ? '<span class="badge bg-primary mb-1">Principal</span><br>' : '';
        conducteurDisplay.html(principalBadge + '<strong>' + condOption.dataset.nom + '</strong><br><small class="text-muted">' + condOption.dataset.ville + '</small> ' + nuitBadge +
                               '<input type="hidden" name="voyages[' + index + '][conducteur_id]" value="' + conducteurId + '" id="conducteur-input-' + index + '">');
    } else {
        conducteurDisplay.html('<span class="text-danger">Aucun conducteur</span>' +
                               '<input type="hidden" name="voyages[' + index + '][conducteur_id]" value="" id="conducteur-input-' + index + '">');
    }
    
    // Mettre à jour l'affichage du conducteur relais (nuit uniquement)
    var conducteur2Display = $('#conducteur2-display-' + index);
    if (conducteur2Display.length && periode === 'Nuit') {
        if (conducteur2Id) {
            var cond2Option = conducteur2Select.options[conducteur2Select.selectedIndex];
            var nuit2Badge = cond2Option.dataset.nuit === '1' ? '<span class="badge bg-dark">Spéc. Nuit</span>' : '';
            conducteur2Display.html('<span class="badge bg-secondary mb-1">Relais</span><br><strong>' + cond2Option.dataset.nom + '</strong><br><small class="text-muted">' + cond2Option.dataset.ville + '</small> ' + nuit2Badge +
                                    '<input type="hidden" name="voyages[' + index + '][conducteur_2_id]" value="' + conducteur2Id + '" id="conducteur2-input-' + index + '">');
        } else {
            conducteur2Display.html('<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> Relais manquant</span>' +
                                    '<input type="hidden" name="voyages[' + index + '][conducteur_2_id]" value="" id="conducteur2-input-' + index + '">');
        }
    }
    
    // Mettre à jour le statut
    var statutDisplay = $('#statut-' + index);
    var row = $('#row-' + index);
    
    if (busId && conducteurId) {
        if (periode === 'Nuit' && !conducteur2Id) {
            statutDisplay.html('<span class="badge bg-warning text-dark">1 conducteur</span>');
            row.removeClass('table-danger').addClass('table-warning');
        } else {
            statutDisplay.html('<span class="badge bg-success">Prêt</span>');
            row.removeClass('table-danger table-warning');
        }
    } else {
        statutDisplay.html('<span class="badge bg-secondary">Incomplet</span>');
        row.addClass('table-danger').removeClass('table-warning');
    }
    
    // Fermer le modal
    $('#modalAjustement').modal('hide');
}
</script>
@endpush
