<?php

namespace App\Services;

use App\Models\Conducteur;
use App\Models\Voyage;
use App\Models\ReposConducteur;
use App\Models\CritereProgrammation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class FatigueService
{
    /**
     * Coefficients de fatigue (configurables via CritereProgrammation)
     */
    protected array $coefficients;

    /**
     * Seuils d'alerte
     */
    protected array $seuils;

    public function __construct()
    {
        $this->loadConfiguration();
    }

    /**
     * Charge la configuration depuis les critères de programmation
     */
    protected function loadConfiguration(): void
    {
        $this->coefficients = [
            'voyage_nuit' => CritereProgrammation::get('coef_fatigue_voyage_nuit', 15),
            'voyage_jour' => CritereProgrammation::get('coef_fatigue_voyage_jour', 8),
            'jour_travail' => CritereProgrammation::get('coef_fatigue_jour_travail', 5),
            'heures_supplementaires' => CritereProgrammation::get('coef_fatigue_heures_sup', 3),
            'recuperation_repos' => CritereProgrammation::get('coef_recuperation_repos', -30),
            'recuperation_journaliere' => CritereProgrammation::get('coef_recuperation_journaliere', -10),
        ];

        $this->seuils = [
            'vert' => CritereProgrammation::get('seuil_fatigue_vert', 30),
            'jaune' => CritereProgrammation::get('seuil_fatigue_jaune', 50),
            'orange' => CritereProgrammation::get('seuil_fatigue_orange', 70),
            'rouge' => CritereProgrammation::get('seuil_fatigue_rouge', 85),
            'max_nuits_consecutives' => CritereProgrammation::get('max_nuits_consecutives', 3),
            'max_jours_sans_repos' => CritereProgrammation::get('max_jours_sans_repos', 6),
            'max_heures_semaine' => CritereProgrammation::get('max_heures_semaine', 48),
        ];
    }

    /**
     * Calcule le score de fatigue global d'un conducteur
     * 
     * @return array{score: int, details: array, niveau: string, alertes: array}
     */
    public function calculerScoreFatigue(Conducteur $conducteur, ?Carbon $date = null): array
    {
        $date = $date ?? Carbon::today();
        
        // Récupérer les voyages sur les 7 derniers jours
        $voyages = $this->getVoyagesRecents($conducteur, $date, 7);
        
        // Compter les différents types de voyages
        $stats = $this->calculerStatistiquesVoyages($conducteur, $voyages, $date);
        
        // Calculer le score de base
        $scoreBase = 0;
        $details = [];

        // Score pour les voyages de nuit consécutifs
        $scoreNuit = $stats['nuits_consecutives'] * $this->coefficients['voyage_nuit'];
        $scoreBase += $scoreNuit;
        $details['nuits_consecutives'] = [
            'valeur' => $stats['nuits_consecutives'],
            'contribution' => $scoreNuit,
            'description' => "{$stats['nuits_consecutives']} nuits consécutives"
        ];

        // Score pour les voyages de jour consécutifs
        $scoreJour = $stats['jours_consecutifs'] * $this->coefficients['voyage_jour'];
        $scoreBase += $scoreJour;
        $details['jours_consecutifs'] = [
            'valeur' => $stats['jours_consecutifs'],
            'contribution' => $scoreJour,
            'description' => "{$stats['jours_consecutifs']} jours consécutifs"
        ];

        // Score pour les jours de travail sans repos
        $scoreJoursTravail = $stats['jours_travail_consecutifs'] * $this->coefficients['jour_travail'];
        $scoreBase += $scoreJoursTravail;
        $details['jours_travail'] = [
            'valeur' => $stats['jours_travail_consecutifs'],
            'contribution' => $scoreJoursTravail,
            'description' => "{$stats['jours_travail_consecutifs']} jours sans repos"
        ];

        // Bonus/malus selon le dernier repos
        $joursDepuisRepos = $stats['jours_depuis_dernier_repos'];
        if ($joursDepuisRepos !== null) {
            if ($joursDepuisRepos <= 1) {
                $scoreRecup = $this->coefficients['recuperation_repos'];
                $scoreBase += $scoreRecup;
                $details['recuperation'] = [
                    'valeur' => $joursDepuisRepos,
                    'contribution' => $scoreRecup,
                    'description' => "Repos récent (il y a {$joursDepuisRepos} jour(s))"
                ];
            }
        }

        // Calculer le score pour les heures de conduite cette semaine
        if ($stats['heures_semaine'] > $this->seuils['max_heures_semaine']) {
            $heuresSup = $stats['heures_semaine'] - $this->seuils['max_heures_semaine'];
            $scoreHeures = $heuresSup * $this->coefficients['heures_supplementaires'];
            $scoreBase += $scoreHeures;
            $details['heures_supplementaires'] = [
                'valeur' => $heuresSup,
                'contribution' => $scoreHeures,
                'description' => "{$heuresSup}h au-delà du maximum hebdomadaire"
            ];
        }

        // Garantir que le score est entre 0 et 100
        $score = max(0, min(100, $scoreBase));
        
        // Déterminer le niveau d'alerte
        $niveau = $this->determinerNiveauAlerte($score);
        
        // Générer les alertes
        $alertes = $this->genererAlertes($conducteur, $stats, $score, $niveau);

        return [
            'score' => $score,
            'score_brut' => $scoreBase,
            'details' => $details,
            'statistiques' => $stats,
            'niveau' => $niveau,
            'alertes' => $alertes,
            'couleur' => $this->getCouleurNiveau($niveau),
            'recommandation' => $this->genererRecommandation($score, $stats, $niveau),
        ];
    }

    /**
     * Récupère les voyages récents d'un conducteur
     */
    protected function getVoyagesRecents(Conducteur $conducteur, Carbon $date, int $jours): Collection
    {
        $dateDebut = $date->copy()->subDays($jours);
        
        return Voyage::where(function($query) use ($conducteur) {
                $query->where('conducteur_id', $conducteur->id)
                      ->orWhere('conducteur_2_id', $conducteur->id);
            })
            ->whereBetween('date_depart', [$dateDebut, $date->endOfDay()])
            ->orderBy('date_depart')
            ->get();
    }

    /**
     * Calcule les statistiques détaillées des voyages
     */
    protected function calculerStatistiquesVoyages(Conducteur $conducteur, Collection $voyages, Carbon $date): array
    {
        $heureDebutNuit = CritereProgrammation::get('heure_debut_nuit', 19);
        $heureFinNuit = CritereProgrammation::get('heure_fin_nuit', 6);

        // Séparation jour/nuit
        $voyagesNuit = $voyages->filter(fn($v) => $v->periode === 'Nuit');
        $voyagesJour = $voyages->filter(fn($v) => $v->periode === 'Jour');

        // Calculer les nuits consécutives (en partant de la date actuelle)
        $nuitsConsecutives = 0;
        $checkDate = $date->copy();
        for ($i = 0; $i < 14; $i++) {
            $voyageNuitJour = $voyages->filter(function($v) use ($checkDate) {
                return $v->periode === 'Nuit' && 
                       Carbon::parse($v->date_depart)->toDateString() === $checkDate->toDateString();
            });
            if ($voyageNuitJour->isEmpty()) {
                break;
            }
            $nuitsConsecutives++;
            $checkDate->subDay();
        }

        // Calculer les jours consécutifs
        $joursConsecutifs = 0;
        $checkDate = $date->copy();
        for ($i = 0; $i < 14; $i++) {
            $voyageJourJour = $voyages->filter(function($v) use ($checkDate) {
                return $v->periode === 'Jour' && 
                       Carbon::parse($v->date_depart)->toDateString() === $checkDate->toDateString();
            });
            if ($voyageJourJour->isEmpty()) {
                break;
            }
            $joursConsecutifs++;
            $checkDate->subDay();
        }

        // Jours de travail consécutifs (jour ou nuit)
        $joursTravailConsecutifs = 0;
        $checkDate = $date->copy();
        for ($i = 0; $i < 30; $i++) {
            $voyageDuJour = $voyages->filter(function($v) use ($checkDate) {
                return Carbon::parse($v->date_depart)->toDateString() === $checkDate->toDateString();
            });
            
            // Vérifier aussi s'il était en repos
            $enRepos = ReposConducteur::where('conducteur_id', $conducteur->id)
                ->where('date_debut', '<=', $checkDate)
                ->where('date_fin', '>=', $checkDate)
                ->exists();
            
            if ($voyageDuJour->isEmpty() || $enRepos) {
                break;
            }
            $joursTravailConsecutifs++;
            $checkDate->subDay();
        }

        // Dernier repos
        $dernierRepos = ReposConducteur::where('conducteur_id', $conducteur->id)
            ->where('date_fin', '<', $date)
            ->orderByDesc('date_fin')
            ->first();
        
        $joursDepuisRepos = $dernierRepos 
            ? Carbon::parse($dernierRepos->date_fin)->diffInDays($date) 
            : null;

        // Estimation des heures de conduite cette semaine (supposons ~4h par voyage)
        $debutSemaine = $date->copy()->startOfWeek();
        $voyagesSemaine = $voyages->filter(fn($v) => Carbon::parse($v->date_depart)->gte($debutSemaine));
        $heuresSemaine = $voyagesSemaine->count() * 4;

        return [
            'total_voyages' => $voyages->count(),
            'voyages_nuit' => $voyagesNuit->count(),
            'voyages_jour' => $voyagesJour->count(),
            'nuits_consecutives' => $nuitsConsecutives,
            'jours_consecutifs' => $joursConsecutifs,
            'jours_travail_consecutifs' => $joursTravailConsecutifs,
            'dernier_repos' => $dernierRepos?->date_fin,
            'jours_depuis_dernier_repos' => $joursDepuisRepos,
            'heures_semaine' => $heuresSemaine,
            'date_analyse' => $date->toDateString(),
        ];
    }

    /**
     * Détermine le niveau d'alerte basé sur le score
     */
    protected function determinerNiveauAlerte(int $score): string
    {
        if ($score >= $this->seuils['rouge']) {
            return 'rouge';
        }
        if ($score >= $this->seuils['orange']) {
            return 'orange';
        }
        if ($score >= $this->seuils['jaune']) {
            return 'jaune';
        }
        return 'vert';
    }

    /**
     * Retourne la couleur CSS pour un niveau
     */
    public function getCouleurNiveau(string $niveau): string
    {
        return match($niveau) {
            'rouge' => '#dc3545',
            'orange' => '#fd7e14',
            'jaune' => '#ffc107',
            'vert' => '#28a745',
            default => '#6c757d',
        };
    }

    /**
     * Génère les alertes spécifiques
     */
    protected function genererAlertes(Conducteur $conducteur, array $stats, int $score, string $niveau): array
    {
        $alertes = [];

        // Alerte nuits consécutives
        if ($stats['nuits_consecutives'] >= $this->seuils['max_nuits_consecutives']) {
            $alertes[] = [
                'type' => 'nuits_consecutives',
                'niveau' => 'urgent',
                'titre' => 'Trop de nuits consécutives',
                'message' => "{$conducteur->prenom} {$conducteur->nom} a effectué {$stats['nuits_consecutives']} voyages de nuit consécutifs. Un repos est IMPÉRATIF.",
                'icone' => 'fa-moon',
            ];
        } elseif ($stats['nuits_consecutives'] >= $this->seuils['max_nuits_consecutives'] - 1) {
            $alertes[] = [
                'type' => 'nuits_consecutives',
                'niveau' => 'attention',
                'titre' => 'Nuits consécutives élevées',
                'message' => "{$conducteur->prenom} {$conducteur->nom} approche la limite de nuits consécutives ({$stats['nuits_consecutives']}/{$this->seuils['max_nuits_consecutives']}).",
                'icone' => 'fa-moon',
            ];
        }

        // Alerte jours sans repos
        if ($stats['jours_travail_consecutifs'] >= $this->seuils['max_jours_sans_repos']) {
            $alertes[] = [
                'type' => 'sans_repos',
                'niveau' => 'critique',
                'titre' => 'Période prolongée sans repos',
                'message' => "{$conducteur->prenom} {$conducteur->nom} travaille depuis {$stats['jours_travail_consecutifs']} jours sans repos. Repos obligatoire!",
                'icone' => 'fa-bed',
            ];
        } elseif ($stats['jours_travail_consecutifs'] >= $this->seuils['max_jours_sans_repos'] - 2) {
            $alertes[] = [
                'type' => 'sans_repos',
                'niveau' => 'attention',
                'titre' => 'Planifier un repos bientôt',
                'message' => "{$conducteur->prenom} {$conducteur->nom} travaille depuis {$stats['jours_travail_consecutifs']} jours. Prévoir un repos dans les prochains jours.",
                'icone' => 'fa-bed',
            ];
        }

        // Alerte heures hebdomadaires
        if ($stats['heures_semaine'] > $this->seuils['max_heures_semaine']) {
            $alertes[] = [
                'type' => 'surcharge_semaine',
                'niveau' => 'urgent',
                'titre' => 'Heures hebdomadaires dépassées',
                'message' => "{$conducteur->prenom} {$conducteur->nom} a {$stats['heures_semaine']}h cette semaine (max: {$this->seuils['max_heures_semaine']}h).",
                'icone' => 'fa-clock',
            ];
        }

        // Alerte fatigue générale
        if ($niveau === 'rouge') {
            $alertes[] = [
                'type' => 'fatigue_generale',
                'niveau' => 'critique',
                'titre' => 'Niveau de fatigue critique',
                'message' => "Le score de fatigue de {$conducteur->prenom} {$conducteur->nom} est à {$score}/100. Ne pas programmer de nouveaux voyages!",
                'icone' => 'fa-exclamation-triangle',
            ];
        }

        return $alertes;
    }

    /**
     * Génère une recommandation de repos
     */
    protected function genererRecommandation(int $score, array $stats, string $niveau): array
    {
        if ($niveau === 'vert') {
            return [
                'type' => 'ok',
                'titre' => 'État satisfaisant',
                'message' => 'Le conducteur peut être programmé normalement.',
                'repos_suggere' => null,
            ];
        }

        // Déterminer le type de repos nécessaire
        $typeRepos = 'complet';
        $duree = 1;

        if ($stats['nuits_consecutives'] >= 3 && $stats['jours_consecutifs'] <= 1) {
            $typeRepos = 'nuit';
            $duree = 2; // 2 jours sans conduite de nuit
        } elseif ($stats['jours_consecutifs'] >= 4 && $stats['nuits_consecutives'] <= 1) {
            $typeRepos = 'jour';
            $duree = 1;
        } else {
            $typeRepos = 'complet';
            $duree = max(1, min(3, intval($score / 30)));
        }

        $messages = [
            'jaune' => "Prévoir un repos dans les 2-3 prochains jours.",
            'orange' => "Un repos est fortement recommandé dès que possible.",
            'rouge' => "REPOS IMPÉRATIF. Ne pas programmer ce conducteur!",
        ];

        return [
            'type' => $typeRepos,
            'titre' => match($niveau) {
                'jaune' => 'Repos conseillé',
                'orange' => 'Repos recommandé',
                'rouge' => 'Repos obligatoire',
                default => 'Information',
            },
            'message' => $messages[$niveau] ?? '',
            'repos_suggere' => [
                'type' => $typeRepos,
                'duree' => $duree,
                'date_debut' => Carbon::today()->addDay()->toDateString(),
                'date_fin' => Carbon::today()->addDays($duree)->toDateString(),
                'motif' => match($typeRepos) {
                    'nuit' => 'Repos nuit - récupération après nuits consécutives',
                    'jour' => 'Repos jour - récupération après jours consécutifs',
                    'complet' => 'Repos réglementaire - niveau de fatigue élevé',
                }
            ],
        ];
    }

    /**
     * Génère un repos automatique si nécessaire
     */
    public function genererReposAutomatique(Conducteur $conducteur, bool $forcer = false): ?ReposConducteur
    {
        $analyse = $this->calculerScoreFatigue($conducteur);
        
        if (!$forcer && $analyse['niveau'] === 'vert') {
            return null;
        }

        if (!$forcer && $analyse['niveau'] === 'jaune') {
            // Pour jaune, on suggère seulement (pas de création automatique)
            return null;
        }

        $recommandation = $analyse['recommandation'];
        
        if (!isset($recommandation['repos_suggere'])) {
            return null;
        }

        $suggere = $recommandation['repos_suggere'];

        // Vérifier qu'il n'y a pas déjà un repos planifié
        $reposExistant = ReposConducteur::where('conducteur_id', $conducteur->id)
            ->where('date_debut', '<=', $suggere['date_fin'])
            ->where('date_fin', '>=', $suggere['date_debut'])
            ->exists();

        if ($reposExistant) {
            return null;
        }

        // Créer le repos
        return ReposConducteur::create([
            'conducteur_id' => $conducteur->id,
            'date_debut' => $suggere['date_debut'],
            'date_fin' => $suggere['date_fin'],
            'motif' => 'Repos réglementaire',
            'type_repos' => $suggere['type'],
            'source' => 'automatique',
            'score_fatigue_declencheur' => $analyse['score'],
            'voyages_nuit_avant' => $analyse['statistiques']['nuits_consecutives'],
            'voyages_jour_avant' => $analyse['statistiques']['jours_consecutifs'],
            'jours_travail_consecutifs' => $analyse['statistiques']['jours_travail_consecutifs'],
            'accepte' => false, // Doit être validé par un opérateur
            'notes' => "Repos généré automatiquement - Score fatigue: {$analyse['score']}/100 - {$recommandation['message']}",
        ]);
    }

    /**
     * Analyse tous les conducteurs et retourne un tableau de bord
     */
    public function analyserTousConducteurs(?Carbon $date = null): array
    {
        $date = $date ?? Carbon::today();
        $conducteurs = Conducteur::where('actif', true)->get();
        
        $resultats = [
            'date' => $date->toDateString(),
            'total_conducteurs' => $conducteurs->count(),
            'par_niveau' => [
                'vert' => [],
                'jaune' => [],
                'orange' => [],
                'rouge' => [],
            ],
            'alertes_critiques' => [],
            'repos_suggeres' => [],
            'statistiques_globales' => [
                'score_moyen' => 0,
                'conducteurs_en_repos' => 0,
                'conducteurs_a_risque' => 0,
            ],
        ];

        $totalScore = 0;

        foreach ($conducteurs as $conducteur) {
            // Vérifier s'il est en repos
            if ($conducteur->estEnRepos($date)) {
                $resultats['statistiques_globales']['conducteurs_en_repos']++;
                continue;
            }

            $analyse = $this->calculerScoreFatigue($conducteur, $date);
            
            $resultats['par_niveau'][$analyse['niveau']][] = [
                'conducteur' => $conducteur,
                'analyse' => $analyse,
            ];

            $totalScore += $analyse['score'];

            // Collecter les alertes critiques
            foreach ($analyse['alertes'] as $alerte) {
                if (in_array($alerte['niveau'], ['urgent', 'critique'])) {
                    $resultats['alertes_critiques'][] = array_merge($alerte, [
                        'conducteur_id' => $conducteur->id,
                        'conducteur_nom' => "{$conducteur->prenom} {$conducteur->nom}",
                    ]);
                }
            }

            // Conducteurs à risque
            if (in_array($analyse['niveau'], ['orange', 'rouge'])) {
                $resultats['statistiques_globales']['conducteurs_a_risque']++;
                
                if ($analyse['recommandation']['repos_suggere']) {
                    $resultats['repos_suggeres'][] = [
                        'conducteur' => $conducteur,
                        'recommandation' => $analyse['recommandation'],
                        'score' => $analyse['score'],
                    ];
                }
            }
        }

        $conducteursAnalyses = $conducteurs->count() - $resultats['statistiques_globales']['conducteurs_en_repos'];
        $resultats['statistiques_globales']['score_moyen'] = $conducteursAnalyses > 0 
            ? round($totalScore / $conducteursAnalyses) 
            : 0;

        return $resultats;
    }

    /**
     * Vérifie si un conducteur peut être programmé pour un voyage
     */
    public function peutEtreProgramme(Conducteur $conducteur, string $periode = 'Jour', ?Carbon $date = null): array
    {
        $date = $date ?? Carbon::today();
        $analyse = $this->calculerScoreFatigue($conducteur, $date);
        
        $peut = true;
        $raisons = [];

        // Blocage si niveau rouge
        if ($analyse['niveau'] === 'rouge') {
            $peut = false;
            $raisons[] = "Niveau de fatigue critique ({$analyse['score']}/100)";
        }

        // Blocage spécifique pour la nuit
        if ($periode === 'Nuit') {
            if ($analyse['statistiques']['nuits_consecutives'] >= $this->seuils['max_nuits_consecutives']) {
                $peut = false;
                $raisons[] = "Maximum de nuits consécutives atteint ({$analyse['statistiques']['nuits_consecutives']})";
            }
        }

        // Blocage si trop de jours sans repos
        if ($analyse['statistiques']['jours_travail_consecutifs'] >= $this->seuils['max_jours_sans_repos']) {
            $peut = false;
            $raisons[] = "Trop de jours sans repos ({$analyse['statistiques']['jours_travail_consecutifs']})";
        }

        return [
            'peut_etre_programme' => $peut,
            'raisons' => $raisons,
            'avertissements' => $analyse['niveau'] === 'orange' ? ["Niveau de fatigue élevé - À surveiller"] : [],
            'analyse' => $analyse,
        ];
    }
}
