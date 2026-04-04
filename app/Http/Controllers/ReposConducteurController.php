<?php

namespace App\Http\Controllers;

use App\Models\Conducteur;
use App\Models\ReposConducteur;
use App\Services\FatigueService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReposConducteurController extends Controller
{
    protected FatigueService $fatigueService;

    public function __construct(FatigueService $fatigueService)
    {
        $this->fatigueService = $fatigueService;
    }

    /**
     * Liste des repos
     */
    public function index(Request $request)
    {
        $query = ReposConducteur::with('conducteur', 'validateur')
            ->orderByDesc('date_debut');

        // Filtres
        if ($request->conducteur_id) {
            $query->where('conducteur_id', $request->conducteur_id);
        }

        if ($request->type_repos) {
            $query->where('type_repos', $request->type_repos);
        }

        if ($request->source) {
            $query->where('source', $request->source);
        }

        if ($request->statut === 'en_attente') {
            $query->where('accepte', false);
        } elseif ($request->statut === 'valides') {
            $query->where('accepte', true);
        } elseif ($request->statut === 'actifs') {
            $query->actifsA(Carbon::today());
        }

        $repos = $query->paginate(20);
        $conducteurs = Conducteur::where('actif', true)->orderBy('nom')->get();

        // Statistiques
        $stats = [
            'en_attente' => ReposConducteur::where('accepte', false)->where('source', '!=', 'manuel')->count(),
            'actifs_aujourdhui' => ReposConducteur::actifsA(Carbon::today())->count(),
            'automatiques_semaine' => ReposConducteur::automatiques()
                ->where('created_at', '>=', Carbon::now()->subWeek())
                ->count(),
        ];

        return view('repos.index', compact('repos', 'conducteurs', 'stats'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $conducteurs = Conducteur::where('actif', true)->orderBy('nom', 'asc')->get();
        return view('repos.create', compact('conducteurs'));
    }

    /**
     * Enregistrer un nouveau repos
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'conducteur_id' => 'required|exists:conducteurs,id',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'motif' => 'required|string',
            'type_repos' => 'required|in:jour,nuit,complet',
            'notes' => 'nullable|string',
        ]);

        $validated['source'] = ReposConducteur::SOURCE_MANUEL;
        $validated['accepte'] = true;
        $validated['accepte_le'] = now();
        $validated['accepte_par'] = auth()->id();

        ReposConducteur::create($validated);

        return redirect()->route('repos.index')
            ->with('success', 'Repos créé avec succès');
    }

    /**
     * Formulaire d'édition
     */
    public function edit(ReposConducteur $repo)
    {
        $conducteurs = Conducteur::where('actif', true)->orderBy('nom', 'asc')->get();
        return view('repos.edit', compact('repo', 'conducteurs'));
    }

    /**
     * Mettre à jour un repos
     */
    public function update(Request $request, ReposConducteur $repo)
    {
        $validated = $request->validate([
            'conducteur_id' => 'required|exists:conducteurs,id',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'motif' => 'required|string',
            'type_repos' => 'required|in:jour,nuit,complet',
            'notes' => 'nullable|string',
        ]);

        $repo->update($validated);

        return redirect()->route('repos.index')
            ->with('success', 'Repos mis à jour avec succès');
    }

    /**
     * Supprimer un repos
     */
    public function destroy(ReposConducteur $repo)
    {
        $repo->delete();

        return redirect()->route('repos.index')
            ->with('success', 'Repos supprimé avec succès');
    }

    /**
     * Accepter un repos suggéré/automatique
     */
    public function accepter(ReposConducteur $repo)
    {
        $repo->accepter(auth()->id());

        return back()->with('success', 'Repos accepté');
    }

    /**
     * Refuser un repos suggéré/automatique
     */
    public function refuser(ReposConducteur $repo)
    {
        $repo->refuser();

        return back()->with('success', 'Repos refusé et supprimé');
    }

    /**
     * Dashboard de fatigue - Vue principale
     */
    public function dashboard()
    {
        $analyse = $this->fatigueService->analyserTousConducteurs();
        
        return view('repos.dashboard', compact('analyse'));
    }

    /**
     * Détail de fatigue d'un conducteur
     */
    public function detailConducteur(Conducteur $conducteur)
    {
        $analyse = $this->fatigueService->calculerScoreFatigue($conducteur);
        
        // Historique des voyages récents
        $voyagesRecents = $conducteur->voyages()
            ->with('ligne', 'bus')
            ->orderByDesc('date_depart')
            ->take(10)
            ->get();
        
        // Historique des repos
        $reposRecents = $conducteur->repos()
            ->orderByDesc('date_debut')
            ->take(5)
            ->get();
        
        return view('repos.detail-conducteur', compact('conducteur', 'analyse', 'voyagesRecents', 'reposRecents'));
    }

    /**
     * Générer un repos automatique pour un conducteur
     */
    public function genererRepos(Conducteur $conducteur)
    {
        $repos = $this->fatigueService->genererReposAutomatique($conducteur, true);

        if ($repos) {
            return back()->with('success', "Repos suggéré créé du {$repos->date_debut->format('d/m/Y')} au {$repos->date_fin->format('d/m/Y')}");
        }

        return back()->with('info', 'Ce conducteur n\'a pas besoin de repos actuellement');
    }

    /**
     * Générer les repos automatiques pour tous les conducteurs à risque
     */
    public function genererTousRepos()
    {
        $conducteurs = Conducteur::where('actif', true)->get();
        $reposCrees = 0;

        foreach ($conducteurs as $conducteur) {
            $repos = $this->fatigueService->genererReposAutomatique($conducteur);
            if ($repos) {
                $reposCrees++;
            }
        }

        return back()->with('success', "{$reposCrees} repos générés automatiquement");
    }

    /**
     * API: Obtenir le score de fatigue d'un conducteur
     */
    public function apiScoreFatigue(Conducteur $conducteur)
    {
        $analyse = $this->fatigueService->calculerScoreFatigue($conducteur);
        
        return response()->json([
            'conducteur' => [
                'id' => $conducteur->id,
                'nom' => $conducteur->nom,
                'prenom' => $conducteur->prenom,
            ],
            'fatigue' => $analyse,
        ]);
    }

    /**
     * API: Obtenir le dashboard complet
     */
    public function apiDashboard()
    {
        $analyse = $this->fatigueService->analyserTousConducteurs();
        
        // Simplifier pour l'API
        $resultat = [
            'date' => $analyse['date'],
            'statistiques' => $analyse['statistiques_globales'],
            'alertes_critiques' => $analyse['alertes_critiques'],
            'repos_suggeres' => count($analyse['repos_suggeres']),
            'par_niveau' => [
                'vert' => count($analyse['par_niveau']['vert']),
                'jaune' => count($analyse['par_niveau']['jaune']),
                'orange' => count($analyse['par_niveau']['orange']),
                'rouge' => count($analyse['par_niveau']['rouge']),
            ],
        ];
        
        return response()->json($resultat);
    }

    /**
     * Liste des repos en attente de validation
     */
    public function enAttente()
    {
        $reposEnAttente = ReposConducteur::with('conducteur')
            ->enAttente()
            ->orderBy('date_debut')
            ->get();

        return view('repos.en-attente', compact('reposEnAttente'));
    }

    /**
     * Validation en masse des repos en attente
     */
    public function validerEnMasse(Request $request)
    {
        $ids = $request->input('repos_ids', []);
        
        if (empty($ids)) {
            return back()->with('warning', 'Aucun repos sélectionné');
        }

        $count = ReposConducteur::whereIn('id', $ids)
            ->update([
                'accepte' => true,
                'accepte_le' => now(),
                'accepte_par' => auth()->id(),
            ]);

        return back()->with('success', "{$count} repos validés");
    }
}
