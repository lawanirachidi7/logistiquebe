@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1"><i class="fas fa-calendar-alt text-primary me-2"></i>Calendrier des Congés</h1>
            <p class="text-muted mb-0">Vue mensuelle des congés planifiés</p>
        </div>
        <div>
            <a href="{{ route('conges.index') }}" class="btn btn-outline-secondary me-2">
                <i class="fas fa-list me-1"></i> Liste
            </a>
            <a href="{{ route('conges.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Nouveau Congé
            </a>
        </div>
    </div>

    <!-- Navigation mois -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                @php
                    $moisPrecedent = $mois == 1 ? 12 : $mois - 1;
                    $anneePrecedente = $mois == 1 ? $annee - 1 : $annee;
                    $moisSuivant = $mois == 12 ? 1 : $mois + 1;
                    $anneeSuivante = $mois == 12 ? $annee + 1 : $annee;
                @endphp
                
                <a href="{{ route('conges.calendrier', ['mois' => $moisPrecedent, 'annee' => $anneePrecedente]) }}" 
                   class="btn btn-outline-primary">
                    <i class="fas fa-chevron-left"></i> Mois précédent
                </a>
                
                <h4 class="mb-0">
                    {{ $debut->translatedFormat('F Y') }}
                </h4>
                
                <a href="{{ route('conges.calendrier', ['mois' => $moisSuivant, 'annee' => $anneeSuivante]) }}" 
                   class="btn btn-outline-primary">
                    Mois suivant <i class="fas fa-chevron-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Calendrier -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table  mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-center" style="width: 14.28%">Lun</th>
                            <th class="text-center" style="width: 14.28%">Mar</th>
                            <th class="text-center" style="width: 14.28%">Mer</th>
                            <th class="text-center" style="width: 14.28%">Jeu</th>
                            <th class="text-center" style="width: 14.28%">Ven</th>
                            <th class="text-center bg-light text-muted" style="width: 14.28%">Sam</th>
                            <th class="text-center bg-light text-muted" style="width: 14.28%">Dim</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $premierJour = $debut->copy()->startOfMonth();
                            $dernierJour = $debut->copy()->endOfMonth();
                            $jourSemaine = $premierJour->dayOfWeekIso;
                            $today = \Carbon\Carbon::today();
                            
                            // Remplissage des jours du mois précédent
                            $joursAvant = $jourSemaine - 1;
                            
                            // Créer un tableau des congés par jour
                            $congesParJour = [];
                            foreach ($conges as $conge) {
                                $dateDebut = max($conge->date_debut, $debut);
                                $dateFin = min($conge->date_fin, $fin);
                                
                                for ($d = $dateDebut->copy(); $d <= $dateFin; $d->addDay()) {
                                    $key = $d->format('Y-m-d');
                                    if (!isset($congesParJour[$key])) {
                                        $congesParJour[$key] = [];
                                    }
                                    $congesParJour[$key][] = $conge;
                                }
                            }
                        @endphp
                        
                        @for($semaine = 0; $semaine < 6; $semaine++)
                            @if($semaine * 7 - $joursAvant + 1 <= $dernierJour->day)
                            <tr style="height: 120px;">
                                @for($jour = 1; $jour <= 7; $jour++)
                                    @php
                                        $numeroJour = $semaine * 7 + $jour - $joursAvant;
                                        $estDansMois = $numeroJour >= 1 && $numeroJour <= $dernierJour->day;
                                        $dateActuelle = $estDansMois ? $debut->copy()->setDay($numeroJour) : null;
                                        $estAujourdhui = $dateActuelle && $dateActuelle->isSameDay($today);
                                        $congesDuJour = $estDansMois ? ($congesParJour[$dateActuelle->format('Y-m-d')] ?? []) : [];
                                        $estWeekend = $jour >= 6;
                                    @endphp
                                    <td class="p-1 align-top {{ $estWeekend ? 'bg-light' : '' }} {{ $estAujourdhui ? 'border-primary border-2' : '' }} {{ !$estDansMois ? 'text-muted bg-white' : '' }}" style="vertical-align: top;">
                                        @if($estDansMois)
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                <span class="fw-bold {{ $estAujourdhui ? 'badge bg-primary' : '' }}">
                                                    {{ $numeroJour }}
                                                </span>
                                                @if(count($congesDuJour) > 0)
                                                    <span class="badge bg-info">{{ count($congesDuJour) }}</span>
                                                @endif
                                            </div>
                                            <div class="overflow-auto" style="max-height: 90px;">
                                                @foreach($congesDuJour as $conge)
                                                    <a href="{{ route('conges.show', $conge) }}" 
                                                       class="d-block small text-decoration-none mb-1 p-1 rounded bg-{{ $conge->type_couleur }} bg-opacity-25 text-{{ $conge->type_couleur }}"
                                                       title="{{ $conge->conducteur->prenom }} {{ $conge->conducteur->nom }} - {{ $conge->type_label }}">
                                                        <i class="fas {{ $conge->type_icone }}"></i>
                                                        {{ Str::limit($conge->conducteur->nom, 10) }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                @endfor
                            </tr>
                            @endif
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Légende -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="fas fa-palette me-2"></i>Légende des types de congés</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @foreach(\App\Models\Conge::TYPES_LABELS as $type => $label)
                    <div class="col-md-3 col-sm-6">
                        <span class="badge bg-{{ \App\Models\Conge::TYPES_COULEURS[$type] }} me-2">
                            <i class="fas {{ \App\Models\Conge::TYPES_ICONES[$type] }}"></i>
                        </span>
                        {{ $label }}
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Liste des conducteurs en congé ce mois -->
    @if($conges->count() > 0)
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="fas fa-users me-2"></i>Congés ce mois ({{ $conges->count() }})</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Conducteur</th>
                            <th>Type</th>
                            <th>Période</th>
                            <th>Durée</th>
                            <th>Motif</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($conges->unique('id') as $conge)
                        <tr>
                            <td>
                                <strong>{{ $conge->conducteur->prenom }} {{ $conge->conducteur->nom }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-{{ $conge->type_couleur }}">
                                    <i class="fas {{ $conge->type_icone }} me-1"></i>
                                    {{ $conge->type_label }}
                                </span>
                            </td>
                            <td>
                                {{ $conge->date_debut->format('d/m/Y') }} - {{ $conge->date_fin->format('d/m/Y') }}
                            </td>
                            <td>{{ $conge->duree }} jour(s)</td>
                            <td>{{ Str::limit($conge->motif, 30) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
