<?php

namespace App\Http\Controllers;

use App\Models\Conge;
use App\Models\Conducteur;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CongeController extends Controller
{
    /**
     * Liste des congés
     */
    public function index(Request $request)
    {
        $query = Conge::with('conducteur', 'validateur')
            ->orderByDesc('date_debut');

        // Filtres
        if ($request->conducteur_id) {
            $query->pourConducteur($request->conducteur_id);
        }

        if ($request->type) {
            $query->deType($request->type);
        }

        if ($request->statut === 'en_cours') {
            $query->actifsA(Carbon::today());
        } elseif ($request->statut === 'a_venir') {
            $query->aVenir();
        } elseif ($request->statut === 'termines') {
            $query->passes();
        } elseif ($request->statut === 'en_attente') {
            $query->enAttente();
        }

        // Filtre par période
        if ($request->date_debut && $request->date_fin) {
            $query->dansPeriode($request->date_debut, $request->date_fin);
        }

        $conges = $query->paginate(20);
        $conducteurs = Conducteur::where('actif', true)->orderBy('nom')->get();

        // Statistiques
        $stats = [
            'en_cours' => Conge::actifsA(Carbon::today())->count(),
            'a_venir' => Conge::aVenir()->count(),
            'total_annee' => Conge::whereYear('date_debut', Carbon::now()->year)->count(),
            'conducteurs_en_conge' => Conge::actifsA(Carbon::today())
                ->select('conducteur_id')
                ->distinct()
                ->count(),
        ];

        return view('conges.index', compact('conges', 'conducteurs', 'stats'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $conducteurs = Conducteur::where('actif', true)->orderBy('nom', 'asc')->get();
        $types = Conge::getTypesForSelect();
        return view('conges.create', compact('conducteurs', 'types'));
    }

    /**
     * Enregistrer un nouveau congé
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'conducteur_id' => 'required|exists:conducteurs,id',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'type' => 'required|in:annuel,maladie,maternite,paternite,sans_solde,special,autre',
            'motif' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Vérifier les chevauchements avec des congés existants
        $chevauchement = Conge::pourConducteur($validated['conducteur_id'])
            ->valides()
            ->dansPeriode($validated['date_debut'], $validated['date_fin'])
            ->exists();

        if ($chevauchement) {
            return back()
                ->withInput()
                ->with('error', 'Ce conducteur a déjà un congé validé qui chevauche cette période.');
        }

        $validated['valide'] = true;
        $validated['valide_par'] = auth()->id();
        $validated['valide_le'] = now();

        Conge::create($validated);

        return redirect()->route('conges.index')
            ->with('success', 'Congé créé avec succès.');
    }

    /**
     * Afficher un congé
     */
    public function show(Conge $conge)
    {
        $conge->load('conducteur', 'validateur');
        return view('conges.show', compact('conge'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit(Conge $conge)
    {
        $conducteurs = Conducteur::where('actif', true)->orderBy('nom', 'asc')->get();
        $types = Conge::getTypesForSelect();
        return view('conges.edit', compact('conge', 'conducteurs', 'types'));
    }

    /**
     * Mettre à jour un congé
     */
    public function update(Request $request, Conge $conge)
    {
        $validated = $request->validate([
            'conducteur_id' => 'required|exists:conducteurs,id',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'type' => 'required|in:annuel,maladie,maternite,paternite,sans_solde,special,autre',
            'motif' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Vérifier les chevauchements avec d'autres congés (excluant celui-ci)
        $chevauchement = Conge::pourConducteur($validated['conducteur_id'])
            ->valides()
            ->where('id', '!=', $conge->id)
            ->dansPeriode($validated['date_debut'], $validated['date_fin'])
            ->exists();

        if ($chevauchement) {
            return back()
                ->withInput()
                ->with('error', 'Ce conducteur a déjà un congé validé qui chevauche cette période.');
        }

        $conge->update($validated);

        return redirect()->route('conges.index')
            ->with('success', 'Congé mis à jour avec succès.');
    }

    /**
     * Supprimer un congé
     */
    public function destroy(Conge $conge)
    {
        $conge->delete();

        return redirect()->route('conges.index')
            ->with('success', 'Congé supprimé avec succès.');
    }

    /**
     * API: Obtenir les congés d'un conducteur
     */
    public function apiConducteur(Conducteur $conducteur)
    {
        $conges = $conducteur->conges()
            ->orderByDesc('date_debut')
            ->get()
            ->map(function($conge) {
                return [
                    'id' => $conge->id,
                    'date_debut' => $conge->date_debut->format('Y-m-d'),
                    'date_fin' => $conge->date_fin->format('Y-m-d'),
                    'date_debut_formatted' => $conge->date_debut->format('d/m/Y'),
                    'date_fin_formatted' => $conge->date_fin->format('d/m/Y'),
                    'type' => $conge->type,
                    'type_label' => $conge->type_label,
                    'motif' => $conge->motif,
                    'duree' => $conge->duree,
                    'statut' => $conge->statut,
                    'statut_label' => $conge->statut_label,
                    'est_actif' => $conge->est_actif,
                ];
            });

        return response()->json([
            'conducteur' => [
                'id' => $conducteur->id,
                'nom' => $conducteur->nom,
                'prenom' => $conducteur->prenom,
            ],
            'conges' => $conges,
            'en_conge' => $conducteur->estEnConge(),
        ]);
    }

    /**
     * API: Vérifier la disponibilité d'un conducteur sur une période
     */
    public function apiVerifierDisponibilite(Request $request)
    {
        $request->validate([
            'conducteur_id' => 'required|exists:conducteurs,id',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
        ]);

        $conducteur = Conducteur::find($request->conducteur_id);
        
        // Vérifier congés
        $congeEnCours = $conducteur->conges()
            ->valides()
            ->dansPeriode($request->date_debut, $request->date_fin)
            ->first();

        // Vérifier repos
        $reposEnCours = $conducteur->repos()
            ->actifsA(Carbon::parse($request->date_debut))
            ->first();

        // Vérifier indisponibilités
        $indispoEnCours = $conducteur->indisponibilites()
            ->where('date_debut', '<=', $request->date_fin)
            ->where('date_fin', '>=', $request->date_debut)
            ->first();

        $disponible = !$congeEnCours && !$reposEnCours && !$indispoEnCours;
        
        return response()->json([
            'disponible' => $disponible,
            'conge' => $congeEnCours ? [
                'type' => $congeEnCours->type_label,
                'date_debut' => $congeEnCours->date_debut->format('d/m/Y'),
                'date_fin' => $congeEnCours->date_fin->format('d/m/Y'),
            ] : null,
            'repos' => $reposEnCours ? [
                'motif' => $reposEnCours->motif,
                'date_fin' => $reposEnCours->date_fin->format('d/m/Y'),
            ] : null,
            'indisponibilite' => $indispoEnCours ? [
                'motif' => $indispoEnCours->motif,
                'date_fin' => $indispoEnCours->date_fin->format('d/m/Y'),
            ] : null,
        ]);
    }

    /**
     * Calendrier des congés
     */
    public function calendrier(Request $request)
    {
        $mois = $request->input('mois', Carbon::now()->month);
        $annee = $request->input('annee', Carbon::now()->year);

        $debut = Carbon::createFromDate($annee, $mois, 1)->startOfMonth();
        $fin = Carbon::createFromDate($annee, $mois, 1)->endOfMonth();

        $conges = Conge::with('conducteur')
            ->valides()
            ->dansPeriode($debut, $fin)
            ->get();

        $conducteurs = Conducteur::where('actif', true)->orderBy('nom')->get();

        return view('conges.calendrier', compact('conges', 'conducteurs', 'debut', 'fin', 'mois', 'annee'));
    }
}
