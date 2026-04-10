<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conducteur;
use App\Models\Bus;
use App\Models\Voyage;
use App\Models\Ligne;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StatistiqueController extends Controller
{
    /**
     * Vue principale des statistiques
     */
    public function index(Request $request)
    {
        $dateDebut = $request->get('date_debut', Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d'));
        $dateFin = $request->get('date_fin', Carbon::now()->format('Y-m-d'));

        // Statistiques globales
        $stats = [
            'total_voyages' => Voyage::whereBetween(DB::raw('DATE(date_depart)'), [$dateDebut, $dateFin])->count(),
            'voyages_termines' => Voyage::whereBetween(DB::raw('DATE(date_depart)'), [$dateDebut, $dateFin])
                ->where('statut', 'Terminé')->count(),
            'voyages_planifies' => Voyage::whereBetween(DB::raw('DATE(date_depart)'), [$dateDebut, $dateFin])
                ->where('statut', 'Planifié')->count(),
            'total_conducteurs' => Conducteur::where('actif', true)->count(),
            'bus_disponibles' => Bus::where('disponible', true)->count(),
            'bus_total' => Bus::count(),
            'total_lignes' => Ligne::count(),
        ];

        // Distance totale (somme des distances des lignes des voyages terminés)
        $stats['distance_totale'] = Voyage::whereBetween(DB::raw('DATE(date_depart)'), [$dateDebut, $dateFin])
            ->where('statut', 'Terminé')
            ->join('lignes', 'voyages.ligne_id', '=', 'lignes.id')
            ->sum('lignes.distance_km') ?? 0;

        // Top 5 conducteurs par nombre de voyages
        $topConducteurs = Conducteur::select('conducteurs.*')
            ->selectRaw('COUNT(voyages.id) as nb_voyages')
            ->selectRaw("SUM(CASE WHEN voyages.statut = 'Terminé' THEN COALESCE(lignes.distance_km, 0) ELSE 0 END) as distance_parcourue")
            ->leftJoin('voyages', function($join) use ($dateDebut, $dateFin) {
                $join->on('conducteurs.id', '=', 'voyages.conducteur_id')
                    ->whereRaw("DATE(voyages.date_depart) >= ?", [$dateDebut])
                    ->whereRaw("DATE(voyages.date_depart) <= ?", [$dateFin]);
            })
            ->leftJoin('lignes', 'voyages.ligne_id', '=', 'lignes.id')
            ->groupBy('conducteurs.id')
            ->orderByDesc('nb_voyages')
            ->limit(5)
            ->get();

        // Top 5 bus par nombre de voyages
        $topBus = Bus::select('bus.*')
            ->selectRaw('COUNT(voyages.id) as nb_voyages')
            ->selectRaw("SUM(CASE WHEN voyages.statut = 'Terminé' THEN COALESCE(lignes.distance_km, 0) ELSE 0 END) as distance_parcourue")
            ->leftJoin('voyages', function($join) use ($dateDebut, $dateFin) {
                $join->on('bus.id', '=', 'voyages.bus_id')
                    ->whereRaw("DATE(voyages.date_depart) >= ?", [$dateDebut])
                    ->whereRaw("DATE(voyages.date_depart) <= ?", [$dateFin]);
            })
            ->leftJoin('lignes', 'voyages.ligne_id', '=', 'lignes.id')
            ->groupBy('bus.id')
            ->orderByDesc('nb_voyages')
            ->limit(5)
            ->get();

        // Voyages par période (Jour/Nuit)
        $voyagesParPeriode = Voyage::select('periode')
            ->selectRaw('COUNT(*) as total')
            ->whereBetween(DB::raw('DATE(date_depart)'), [$dateDebut, $dateFin])
            ->groupBy('periode')
            ->pluck('total', 'periode')
            ->toArray();

        // Voyages par ligne
        $voyagesParLigne = Ligne::select('lignes.nom', 'lignes.type')
            ->selectRaw('COUNT(voyages.id) as total')
            ->leftJoin('voyages', function($join) use ($dateDebut, $dateFin) {
                $join->on('lignes.id', '=', 'voyages.ligne_id')
                    ->whereRaw("DATE(voyages.date_depart) >= ?", [$dateDebut])
                    ->whereRaw("DATE(voyages.date_depart) <= ?", [$dateFin]);
            })
            ->groupBy('lignes.id', 'lignes.nom', 'lignes.type')
            ->orderByDesc('total')
            ->get();

        return view('statistiques.index', compact(
            'stats',
            'topConducteurs',
            'topBus',
            'voyagesParPeriode',
            'voyagesParLigne',
            'dateDebut',
            'dateFin'
        ));
    }

    /**
     * Statistiques détaillées des conducteurs
     */
    public function conducteurs(Request $request)
    {
        $dateDebut = $request->get('date_debut', Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d'));
        $dateFin = $request->get('date_fin', Carbon::now()->format('Y-m-d'));


        $conducteurs = Conducteur::with(['voyages' => function($q) use ($dateDebut, $dateFin) {
            $q->whereBetween(DB::raw('DATE(date_depart)'), [$dateDebut, $dateFin]);
        }, 'voyagesSecond' => function($q) use ($dateDebut, $dateFin) {
            $q->whereBetween(DB::raw('DATE(date_depart)'), [$dateDebut, $dateFin]);
        }, 'voyages.ligne', 'voyagesSecond.ligne'])
        ->get()
        ->map(function($conducteur) {
            // Récupérer tous les voyages où il est principal ou second, sans doublons
            $voyages = $conducteur->voyages->merge($conducteur->voyagesSecond)->unique('id');
            $conducteur->nb_voyages_principal = $conducteur->voyages->count();
            $conducteur->nb_voyages_second = $conducteur->voyagesSecond->count();
            $conducteur->nb_voyages_total = $voyages->count();
            $conducteur->distance_totale = $voyages->where('statut', 'Terminé')->sum(function($v) {
                return $v->ligne->distance_km ?? 0;
            });
            $conducteur->voyages_jour = $voyages->where('periode', 'Jour')->count();
            $conducteur->voyages_nuit = $voyages->where('periode', 'Nuit')->count();
            $conducteur->voyages_aller = $voyages->where('sens', 'Aller')->count();
            $conducteur->voyages_retour = $voyages->where('sens', 'Retour')->count();
            return $conducteur;
        });

        // Statistiques globales des conducteurs
        $statsGlobales = [
            'total_conducteurs' => Conducteur::where('actif', true)->count(),
            'total_voyages' => Voyage::whereBetween(DB::raw('DATE(date_depart)'), [$dateDebut, $dateFin])->count(),
            'moyenne_voyages' => $conducteurs->avg('nb_voyages_total') ?? 0,
            'max_voyages' => $conducteurs->max('nb_voyages_total') ?? 0,
            'distance_totale' => $conducteurs->sum('distance_totale') ?? 0,
        ];

        return view('statistiques.conducteurs', compact('conducteurs', 'statsGlobales', 'dateDebut', 'dateFin'));
    }

    /**
     * Statistiques détaillées des bus
     */
    public function bus(Request $request)
    {
        $dateDebut = $request->get('date_debut', Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d'));
        $dateFin = $request->get('date_fin', Carbon::now()->format('Y-m-d'));

        $bus = Bus::select('bus.*', 'types_bus.libelle as type_bus_nom')
            ->selectRaw('COUNT(voyages.id) as nb_voyages')
            ->selectRaw("SUM(CASE WHEN voyages.statut = 'Terminé' THEN COALESCE(lignes.distance_km, 0) ELSE 0 END) as distance_parcourue")
            ->selectRaw("SUM(CASE WHEN voyages.periode = 'Jour' THEN 1 ELSE 0 END) as voyages_jour")
            ->selectRaw("SUM(CASE WHEN voyages.periode = 'Nuit' THEN 1 ELSE 0 END) as voyages_nuit")
            ->selectRaw("SUM(CASE WHEN voyages.sens = 'Aller' THEN 1 ELSE 0 END) as voyages_aller")
            ->selectRaw("SUM(CASE WHEN voyages.sens = 'Retour' THEN 1 ELSE 0 END) as voyages_retour")
            ->selectRaw('MAX(voyages.date_depart) as dernier_voyage')
            ->leftJoin('voyages', function($join) use ($dateDebut, $dateFin) {
                $join->on('bus.id', '=', 'voyages.bus_id')
                    ->whereRaw("DATE(voyages.date_depart) >= ?", [$dateDebut])
                    ->whereRaw("DATE(voyages.date_depart) <= ?", [$dateFin]);
            })
            ->leftJoin('lignes', 'voyages.ligne_id', '=', 'lignes.id')
            ->leftJoin('types_bus', 'bus.type_bus_id', '=', 'types_bus.id')
            ->groupBy('bus.id', 'types_bus.libelle')
            ->orderByDesc('nb_voyages')
            ->get();

        // Statistiques globales des bus
        $statsGlobales = [
            'total_bus' => Bus::count(),
            'bus_disponibles' => Bus::where('disponible', true)->count(),
            'total_voyages' => $bus->sum('nb_voyages'),
            'distance_totale' => $bus->sum('distance_parcourue') ?? 0,
            'moyenne_voyages' => $bus->avg('nb_voyages') ?? 0,
            'moyenne_distance' => $bus->avg('distance_parcourue') ?? 0,
        ];

        // Répartition par type de bus
        $parTypeBus = $bus->groupBy('type_bus_nom')->map(function($group) {
            return [
                'count' => $group->count(),
                'voyages' => $group->sum('nb_voyages'),
                'distance' => $group->sum('distance_parcourue'),
            ];
        });

        return view('statistiques.bus', compact('bus', 'statsGlobales', 'parTypeBus', 'dateDebut', 'dateFin'));
    }

    /**
     * Statistiques par ligne
     */
    public function lignes(Request $request)
    {
        $dateDebut = $request->get('date_debut', Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d'));
        $dateFin = $request->get('date_fin', Carbon::now()->format('Y-m-d'));

        $lignes = Ligne::select('lignes.*')
            ->selectRaw('COUNT(voyages.id) as nb_voyages')
            ->selectRaw("SUM(CASE WHEN voyages.statut = 'Terminé' THEN 1 ELSE 0 END) as voyages_termines")
            ->selectRaw("SUM(CASE WHEN voyages.statut = 'Planifié' THEN 1 ELSE 0 END) as voyages_planifies")
            ->selectRaw("SUM(CASE WHEN voyages.periode = 'Jour' THEN 1 ELSE 0 END) as voyages_jour")
            ->selectRaw("SUM(CASE WHEN voyages.periode = 'Nuit' THEN 1 ELSE 0 END) as voyages_nuit")
            ->selectRaw('COUNT(DISTINCT voyages.bus_id) as bus_utilises')
            ->selectRaw('COUNT(DISTINCT voyages.conducteur_id) as conducteurs_utilises')
            ->leftJoin('voyages', function($join) use ($dateDebut, $dateFin) {
                $join->on('lignes.id', '=', 'voyages.ligne_id')
                    ->whereRaw("DATE(voyages.date_depart) >= ?", [$dateDebut])
                    ->whereRaw("DATE(voyages.date_depart) <= ?", [$dateFin]);
            })
            ->groupBy('lignes.id')
            ->orderByDesc('nb_voyages')
            ->get();

        // Statistiques globales
        $statsGlobales = [
            'total_lignes' => Ligne::count(),
            'lignes_aller' => Ligne::where('type', 'Aller')->count(),
            'lignes_retour' => Ligne::where('type', 'Retour')->count(),
            'total_voyages' => $lignes->sum('nb_voyages'),
            'distance_totale' => $lignes->sum(fn($l) => $l->nb_voyages * ($l->distance_km ?? 0)),
        ];

        return view('statistiques.lignes', compact('lignes', 'statsGlobales', 'dateDebut', 'dateFin'));
    }

    /**
     * Statistiques d'un conducteur spécifique
     */
    public function conducteurDetail(Request $request, $id)
    {
        $dateDebut = $request->get('date_debut', Carbon::now()->startOfYear()->format('Y-m-d'));
        $dateFin = $request->get('date_fin', Carbon::now()->format('Y-m-d'));

        $conducteur = Conducteur::findOrFail($id);

        // Voyages du conducteur
        $voyages = Voyage::with(['ligne', 'bus'])
            ->where(function($q) use ($id) {
                $q->where('conducteur_id', $id)
                  ->orWhere('conducteur_2_id', $id);
            })
            ->whereBetween(DB::raw('DATE(date_depart)'), [$dateDebut, $dateFin])
            ->orderByDesc('date_depart')
            ->get();

        // Statistiques détaillées
        $stats = [
            'total_voyages' => $voyages->count(),
            'voyages_principal' => $voyages->where('conducteur_id', $id)->count(),
            'voyages_second' => $voyages->where('conducteur_2_id', $id)->count(),
            'voyages_termines' => $voyages->where('statut', 'Terminé')->count(),
            'voyages_jour' => $voyages->where('periode', 'Jour')->count(),
            'voyages_nuit' => $voyages->where('periode', 'Nuit')->count(),
            'voyages_aller' => $voyages->where('sens', 'Aller')->count(),
            'voyages_retour' => $voyages->where('sens', 'Retour')->count(),
            'distance_totale' => $voyages->where('statut', 'Terminé')->sum(fn($v) => $v->ligne->distance_km ?? 0),
        ];

        // Voyages par mois (graphique)
        $voyagesParMois = $voyages->groupBy(function($v) {
            return Carbon::parse($v->date_depart)->format('Y-m');
        })->map->count();

        // Lignes les plus fréquentes
        $lignesFrequentes = $voyages->groupBy('ligne_id')
            ->map(function($group) {
                return [
                    'ligne' => $group->first()->ligne,
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('count')
            ->take(5);

        // Bus utilisés
        $busUtilises = $voyages->groupBy('bus_id')
            ->map(function($group) {
                return [
                    'bus' => $group->first()->bus,
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('count')
            ->take(5);

        return view('statistiques.conducteur-detail', compact(
            'conducteur',
            'voyages',
            'stats',
            'voyagesParMois',
            'lignesFrequentes',
            'busUtilises',
            'dateDebut',
            'dateFin'
        ));
    }

    /**
     * Statistiques d'un bus spécifique
     */
    public function busDetail(Request $request, $id)
    {
        $dateDebut = $request->get('date_debut', Carbon::now()->startOfYear()->format('Y-m-d'));
        $dateFin = $request->get('date_fin', Carbon::now()->format('Y-m-d'));

        $bus = Bus::with('typeBus')->findOrFail($id);

        // Voyages du bus
        $voyages = Voyage::with(['ligne', 'conducteur', 'conducteur2'])
            ->where('bus_id', $id)
            ->whereBetween(DB::raw('DATE(date_depart)'), [$dateDebut, $dateFin])
            ->orderByDesc('date_depart')
            ->get();

        // Statistiques détaillées
        $stats = [
            'total_voyages' => $voyages->count(),
            'voyages_termines' => $voyages->where('statut', 'Terminé')->count(),
            'voyages_jour' => $voyages->where('periode', 'Jour')->count(),
            'voyages_nuit' => $voyages->where('periode', 'Nuit')->count(),
            'voyages_aller' => $voyages->where('sens', 'Aller')->count(),
            'voyages_retour' => $voyages->where('sens', 'Retour')->count(),
            'distance_totale' => $voyages->where('statut', 'Terminé')->sum(fn($v) => $v->ligne->distance_km ?? 0),
        ];

        // Voyages par mois
        $voyagesParMois = $voyages->groupBy(function($v) {
            return Carbon::parse($v->date_depart)->format('Y-m');
        })->map->count();

        // Conducteurs ayant utilisé ce bus
        $conducteursUtilises = $voyages->groupBy('conducteur_id')
            ->map(function($group) {
                return [
                    'conducteur' => $group->first()->conducteur,
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('count')
            ->take(5);

        // Lignes parcourues
        $lignesParcourues = $voyages->groupBy('ligne_id')
            ->map(function($group) {
                return [
                    'ligne' => $group->first()->ligne,
                    'count' => $group->count(),
                    'distance' => $group->count() * ($group->first()->ligne->distance_km ?? 0),
                ];
            })
            ->sortByDesc('count')
            ->take(5);

        return view('statistiques.bus-detail', compact(
            'bus',
            'voyages',
            'stats',
            'voyagesParMois',
            'conducteursUtilises',
            'lignesParcourues',
            'dateDebut',
            'dateFin'
        ));
    }

    /**
     * Export des statistiques en CSV
     */
    public function exportConducteurs(Request $request)
    {
        $dateDebut = $request->get('date_debut', Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d'));
        $dateFin = $request->get('date_fin', Carbon::now()->format('Y-m-d'));

        $conducteurs = Conducteur::select('conducteurs.*')
            ->selectRaw('COUNT(voyages.id) as nb_voyages')
            ->selectRaw("SUM(CASE WHEN voyages.statut = 'Terminé' THEN COALESCE(lignes.distance_km, 0) ELSE 0 END) as distance_totale")
            ->leftJoin('voyages', function($join) use ($dateDebut, $dateFin) {
                $join->on('conducteurs.id', '=', 'voyages.conducteur_id')
                    ->whereRaw("DATE(voyages.date_depart) >= ?", [$dateDebut])
                    ->whereRaw("DATE(voyages.date_depart) <= ?", [$dateFin]);
            })
            ->leftJoin('lignes', 'voyages.ligne_id', '=', 'lignes.id')
            ->groupBy('conducteurs.id')
            ->orderByDesc('nb_voyages')
            ->get();

        $filename = "statistiques_conducteurs_{$dateDebut}_{$dateFin}.csv";
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($conducteurs, $dateDebut, $dateFin) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            
            fputcsv($file, ['Statistiques Conducteurs', "Du $dateDebut au $dateFin"], ';');
            fputcsv($file, [], ';');
            fputcsv($file, ['Nom', 'Prénom', 'Ville Actuelle', 'Nb Voyages', 'Distance (km)', 'Actif'], ';');
            
            foreach ($conducteurs as $c) {
                fputcsv($file, [
                    $c->nom,
                    $c->prenom,
                    $c->ville_actuelle,
                    $c->nb_voyages,
                    $c->distance_totale ?? 0,
                    $c->actif ? 'Oui' : 'Non',
                ], ';');
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export des statistiques bus en CSV
     */
    public function exportBus(Request $request)
    {
        $dateDebut = $request->get('date_debut', Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d'));
        $dateFin = $request->get('date_fin', Carbon::now()->format('Y-m-d'));

        $bus = Bus::select('bus.*', 'types_bus.libelle as type_bus_nom')
            ->selectRaw('COUNT(voyages.id) as nb_voyages')
            ->selectRaw("SUM(CASE WHEN voyages.statut = 'Terminé' THEN COALESCE(lignes.distance_km, 0) ELSE 0 END) as distance_parcourue")
            ->leftJoin('voyages', function($join) use ($dateDebut, $dateFin) {
                $join->on('bus.id', '=', 'voyages.bus_id')
                    ->whereRaw("DATE(voyages.date_depart) >= ?", [$dateDebut])
                    ->whereRaw("DATE(voyages.date_depart) <= ?", [$dateFin]);
            })
            ->leftJoin('lignes', 'voyages.ligne_id', '=', 'lignes.id')
            ->leftJoin('types_bus', 'bus.type_bus_id', '=', 'types_bus.id')
            ->groupBy('bus.id', 'types_bus.libelle')
            ->orderByDesc('nb_voyages')
            ->get();

        $filename = "statistiques_bus_{$dateDebut}_{$dateFin}.csv";
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($bus, $dateDebut, $dateFin) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, ['Statistiques Bus', "Du $dateDebut au $dateFin"], ';');
            fputcsv($file, [], ';');
            fputcsv($file, ['Immatriculation', 'Type', 'Ville Actuelle', 'Nb Voyages', 'Distance (km)', 'Disponible'], ';');
            
            foreach ($bus as $b) {
                fputcsv($file, [
                    $b->immatriculation,
                    $b->type_bus_nom ?? '-',
                    $b->ville_actuelle,
                    $b->nb_voyages,
                    $b->distance_parcourue ?? 0,
                    $b->disponible ? 'Oui' : 'Non',
                ], ';');
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
