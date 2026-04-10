<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bus;
use App\Models\Conducteur;
use App\Models\Ligne;
use App\Models\Voyage;

class VoyageController extends Controller
{
    /**
     * Dashboard principal (vue d'ensemble)
     */
    public function dashboard()
    {
        $conducteursActifs = Conducteur::where('actif', true)->count();
        $totalConducteurs = Conducteur::count();
        $busDisponibles = Bus::where('disponible', true)->count();
        $totalBus = Bus::count();
        $totalLignes = Ligne::count();
        $voyagesProgrammes = Voyage::where('date_depart', '>=', now())->count();

        $derniersVoyages = Voyage::with(['ligne', 'bus', 'conducteur'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('voyages.dashboard', compact('conducteursActifs', 'totalConducteurs', 'busDisponibles', 'totalBus', 'totalLignes', 'voyagesProgrammes', 'derniersVoyages'));
    }

    /**
     * Historique des programmations de voyages
     */
    public function historique()
    {
        $voyages = Voyage::with(['ligne', 'bus', 'conducteur', 'conducteur2', 'bus.typeBus'])
            ->orderByDesc('date_depart')
            ->get();
            
        return view('voyages.historique', compact('voyages'));
    }

    /**
     * Formulaire de planification d'un voyage à venir
     */
    public function planification()
    {
        $lignes = Ligne::all();
        $bus = Bus::with('typeBus')->get(); // Idéalement filtrer les bus disponibles
        $conducteurs = Conducteur::all(); // Idéalement filtrer les conducteurs disponibles
        
        return view('voyages.planification', compact('lignes', 'bus', 'conducteurs'));
    }

    /**
     * Enregistre une nouvelle programmation de voyage
     */
    public function planifier(Request $request)
    {
        $request->validate([
            'date_depart' => 'required|date',
            'ligne_id' => 'required|exists:lignes,id',
            'bus_id' => 'required|exists:bus,id',
            'conducteur_id' => 'required|exists:conducteurs,id',
            'periode' => 'required|in:Jour,Nuit',
            'sens' => 'required|in:Aller,Retour',
        ]);

        $data = $request->all();
        // Si la case "forcer la nuit" est cochée, forcer la valeur à 1 (sinon 0)
        $data['force_nuit'] = $request->has('force_nuit') ? 1 : 0;
        Voyage::create($data);

        return redirect()->route('voyages.historique')->with('success', 'Voyage planifié avec succès');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->historique();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return $this->planification();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return $this->planifier($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $voyage = Voyage::with(['ligne', 'bus', 'conducteur'])->findOrFail($id);
        return view('voyages.show', compact('voyage'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $voyage = Voyage::findOrFail($id);
        $lignes = Ligne::all();
        $bus = Bus::all();
        $conducteurs = Conducteur::all();
        return view('voyages.edit', compact('voyage', 'lignes', 'bus', 'conducteurs'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'date_depart' => 'required|date',
            'ligne_id' => 'required|exists:lignes,id',
            'bus_id' => 'required|exists:bus,id',
            'conducteur_id' => 'required|exists:conducteurs,id',
            'conducteur_2_id' => 'nullable|exists:conducteurs,id',
            'periode' => 'required|in:Jour,Nuit',
            'sens' => 'required|in:Aller,Retour',
            'statut' => 'nullable|in:Planifié,En cours,Terminé,Annulé',
        ]);

        $voyage = Voyage::findOrFail($id);
        $ancienStatut = $voyage->statut;
        
        // Gérer le cas où conducteur_2_id est vide
        $data = $request->all();
        if (empty($data['conducteur_2_id'])) {
            $data['conducteur_2_id'] = null;
        }
        
        $voyage->update($data);

        // Si le voyage passe à "Terminé", mettre à jour la ville_actuelle du conducteur
        if ($request->statut === 'Terminé' && $ancienStatut !== 'Terminé') {
            $this->mettreAJourVilleConducteur($voyage);
        }

        return redirect()->route('voyages.historique')->with('success', 'Voyage mis à jour avec succès');
    }

    /**
     * Valider un voyage et mettre à jour la position du conducteur et du bus
     */
    public function valider(string $id)
    {
        $voyage = Voyage::with(['ligne', 'conducteur', 'conducteur2', 'bus'])->findOrFail($id);
        
        if ($voyage->statut === 'Terminé') {
            return redirect()->back()->with('info', 'Ce voyage est déjà validé');
        }

        $voyage->update(['statut' => 'Terminé']);
        $this->mettreAJourVilleConducteur($voyage);

        $message = 'Voyage validé ! Bus ' . $voyage->bus->immatriculation;
        $message .= ', ' . $voyage->conducteur->prenom;
        if ($voyage->conducteur2) {
            $message .= ' et ' . $voyage->conducteur2->prenom;
        }
        $message .= ' maintenant à ' . $voyage->ligne->ville_arrivee;

        return redirect()->back()->with('success', $message);
    }

    /**
     * Met à jour la ville_actuelle des conducteurs et du bus avec la ville d'arrivée de la ligne
     */
    private function mettreAJourVilleConducteur(Voyage $voyage)
    {
        $voyage->load(['ligne', 'conducteur', 'conducteur2', 'bus']);
        
        // Mettre à jour le conducteur principal
        if ($voyage->conducteur && $voyage->ligne) {
            $voyage->conducteur->update([
                'ville_actuelle' => $voyage->ligne->ville_arrivee
            ]);
        }

        // Mettre à jour le 2ème conducteur (voyages de nuit)
        if ($voyage->conducteur2 && $voyage->ligne) {
            $voyage->conducteur2->update([
                'ville_actuelle' => $voyage->ligne->ville_arrivee
            ]);
        }

        // Mettre à jour la position du bus
        if ($voyage->bus && $voyage->ligne) {
            $voyage->bus->update([
                'ville_actuelle' => $voyage->ligne->ville_arrivee
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $voyage = Voyage::findOrFail($id);
        $voyage->delete();

        return redirect()->route('voyages.historique')->with('success', 'Voyage supprimé avec succès');
    }

    /**
     * Supprime tous les voyages programmés
     */
    public function deleteAll()
    {
        $count = Voyage::count();
        Voyage::query()->delete();

        return redirect()->route('voyages.historique')
            ->with('success', "$count voyage(s) supprimé(s) avec succès.");
    }

    /**
     * Affiche le formulaire pour modifier la programmation d'une date
     */
    public function editByDate(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        
        $voyages = Voyage::with(['ligne', 'bus', 'bus.typeBus', 'conducteur', 'conducteur2'])
            ->whereDate('date_depart', $date)
            ->orderBy('date_depart')
            ->get();
        
        $lignes = Ligne::orderBy('nom')->get();
        $bus = Bus::with('typeBus')->where('disponible', true)->get();
        $conducteurs = Conducteur::where('actif', true)->orderBy('nom')->get();
        
        return view('voyages.edit-by-date', compact('date', 'voyages', 'lignes', 'bus', 'conducteurs'));
    }

    /**
     * Met à jour tous les voyages d'une date
     */
    public function updateByDate(Request $request)
    {
        $date = $request->date;
        $voyagesData = $request->voyages ?? [];
        
        $updated = 0;
        $deleted = 0;
        $errors = [];
        
        // Récupérer les IDs des voyages existants pour cette date
        $existingIds = Voyage::whereDate('date_depart', $date)->pluck('id')->toArray();
        $submittedIds = [];
        
        foreach ($voyagesData as $voyageInfo) {
            $id = $voyageInfo['id'] ?? null;
            
            // Si marqué pour suppression
            if (isset($voyageInfo['delete']) && $voyageInfo['delete'] == '1') {
                if ($id) {
                    Voyage::where('id', $id)->delete();
                    $deleted++;
                }
                continue;
            }
            
            // Ignorer si données incomplètes
            if (empty($voyageInfo['ligne_id']) || empty($voyageInfo['bus_id']) || empty($voyageInfo['conducteur_id'])) {
                continue;
            }
            
            $submittedIds[] = $id;
            
            $ligne = Ligne::find($voyageInfo['ligne_id']);
            if (!$ligne) continue;
            
            $data = [
                'date_depart' => $date . ' ' . $ligne->horaire,
                'ligne_id' => $voyageInfo['ligne_id'],
                'bus_id' => $voyageInfo['bus_id'],
                'conducteur_id' => $voyageInfo['conducteur_id'],
                'conducteur_2_id' => !empty($voyageInfo['conducteur_2_id']) ? $voyageInfo['conducteur_2_id'] : null,
                'periode' => $voyageInfo['periode'] ?? 'Jour',
                'sens' => $ligne->type === 'Retour' ? 'Retour' : 'Aller',
                'statut' => $voyageInfo['statut'] ?? 'Planifié',
            ];
            
            if ($id) {
                // Mise à jour
                Voyage::where('id', $id)->update($data);
            } else {
                // Création
                $data['created_at'] = now();
                $data['updated_at'] = now();
                Voyage::insert($data);
            }
            $updated++;
        }
        
        $message = "$updated voyage(s) mis à jour.";
        if ($deleted > 0) {
            $message .= " $deleted voyage(s) supprimé(s).";
        }
        
        return redirect()->route('voyages.editByDate', ['date' => $date])
            ->with('success', $message);
    }

    /**
     * Supprime tous les voyages d'une date
     */
    public function deleteByDate(Request $request)
    {
        $date = $request->date;
        $count = Voyage::whereDate('date_depart', $date)->count();
        Voyage::whereDate('date_depart', $date)->delete();
        
        return redirect()->route('voyages.historique')
            ->with('success', "$count voyage(s) du " . \Carbon\Carbon::parse($date)->format('d/m/Y') . " supprimé(s).");
    }

    /**
     * Valide tous les voyages d'une date comme effectués
     */
    public function validateByDate(Request $request)
    {
        $date = $request->date;
        
        $voyages = Voyage::with(['ligne', 'conducteur', 'conducteur2', 'bus'])
            ->whereDate('date_depart', $date)
            ->where('statut', '!=', 'Terminé')
            ->get();
        
        $count = 0;
        foreach ($voyages as $voyage) {
            $voyage->update(['statut' => 'Terminé']);
            $this->mettreAJourVilleConducteur($voyage);
            $count++;
        }
        
        return redirect()->route('voyages.editByDate', ['date' => $date])
            ->with('success', "$count voyage(s) validé(s). Les positions des conducteurs et bus ont été mises à jour.");
    }

    /**
     * Affiche le formulaire de génération automatique
     */
    public function genererForm()
    {
        $lignesAller = Ligne::with('ligneRetourAssociee')
            ->where('type', 'Aller')
            ->orderBy('nom')
            ->get();
        
        $lignesRetour = Ligne::where('type', 'Retour')
            ->orderBy('nom')
            ->get();
        
        return view('voyages.generer', compact('lignesAller', 'lignesRetour'));
    }

    /**
     * Génère automatiquement la programmation pour une journée
     * Logique: Les bus/conducteurs ayant fait un aller hier font le retour aujourd'hui et vice versa
     */
    public function generer(Request $request)
    {
        $date = $request->date;
        $periodes = $request->periode === 'Les deux' ? ['Jour', 'Nuit'] : [$request->periode];
        
        // Si les données viennent du formulaire d'aperçu avec ajustements
        if ($request->has('voyages')) {
            return $this->genererDepuisApercu($request);
        }
        
        $request->validate([
            'date' => 'required|date',
            'periode' => 'required|in:Jour,Nuit,Les deux',
            'lignes' => 'nullable|array',
            'lignes.*' => 'exists:lignes,id',
        ]);

        $dateHier = date('Y-m-d', strtotime($date . ' -1 day'));
        
        // Lignes sélectionnées (si aucune, prendre toutes les lignes Aller)
        $lignesSelectionnees = $request->lignes ? Ligne::whereIn('id', $request->lignes)->get() 
            : Ligne::aller()->get();
        
        $voyagesCrees = [];
        $erreurs = [];
        
        // Suivi des lignes déjà programmées pour cette journée (pour éviter 2 voyages par ligne quand "Les deux" périodes)
        $lignesProgrammees = [];
        
        // Récupérer les conducteurs en repos ou indisponibles à cette date
        $conducteursEnRepos = Conducteur::where('actif', true)
            ->where(function($q) use ($date) {
                $q->whereHas('repos', function($rq) use ($date) {
                    $rq->where('date_debut', '<=', $date)
                       ->where('date_fin', '>=', $date);
                })->orWhereHas('indisponibilites', function($iq) use ($date) {
                    $iq->where('date_debut', '<=', $date)
                       ->where('date_fin', '>=', $date);
                });
            })
            ->pluck('id')
            ->toArray();

        // Pour chaque période demandée
        foreach ($periodes as $periode) {
            // Récupérer les bus et conducteurs déjà assignés pour cette date/période
            $busAssignes = Voyage::whereDate('date_depart', $date)
                ->where('periode', $periode)
                ->pluck('bus_id')
                ->toArray();
            
            $conducteursAssignes = Voyage::whereDate('date_depart', $date)
                ->where('periode', $periode)
                ->pluck('conducteur_id')
                ->toArray();
            
            // Ajouter les conducteurs en repos à la liste des assignés (ils ne seront pas sélectionnés)
            $conducteursAssignes = array_merge($conducteursAssignes, $conducteursEnRepos);
            
            $conducteurs2Assignes = Voyage::whereDate('date_depart', $date)
                ->where('periode', $periode)
                ->whereNotNull('conducteur_2_id')
                ->pluck('conducteur_2_id')
                ->toArray();
            
            $conducteursAssignes = array_merge($conducteursAssignes, $conducteurs2Assignes);

            // IDs des lignes sélectionnées et leurs lignes retour
            $lignesIds = $lignesSelectionnees->pluck('id')->toArray();
            $lignesRetourIds = $lignesSelectionnees->map(fn($l) => $l->getLigneRetour()?->id)->filter()->toArray();
            $toutesLignesIds = array_merge($lignesIds, $lignesRetourIds);

            // ÉTAPE 1: Programmer les RETOURS pour les bus/conducteurs ayant fait un ALLER hier
            // On prend en compte tous les voyages programmés (Planifié, En cours, Terminé), pas seulement les terminés
            $voyagesAllerHier = Voyage::with(['ligne', 'bus', 'conducteur', 'conducteur2'])
                ->whereDate('date_depart', $dateHier)
                ->where('periode', $periode)
                ->whereIn('statut', ['Planifié', 'En cours', 'Terminé']) // Considère la programmation existante
                ->whereIn('ligne_id', $lignesIds) // Seulement les lignes sélectionnées
                ->whereHas('ligne', function($q) {
                    $q->where('type', 'Aller');
                })
                ->get();

            foreach ($voyagesAllerHier as $voyageHier) {
                // Vérifier si le bus n'est pas déjà assigné
                if (in_array($voyageHier->bus_id, $busAssignes)) {
                    continue;
                }

                // Trouver la ligne retour correspondante
                $ligneRetour = $voyageHier->ligne->getLigneRetour();
                
                if (!$ligneRetour) {
                    $erreurs[] = "Ligne retour non trouvée pour {$voyageHier->ligne->nom}";
                    continue;
                }

                // VÉRIFICATION ANTI-DOUBLON: Vérifier si ce voyage retour existe déjà
                $voyageExiste = Voyage::whereDate('date_depart', $date)
                    ->where('periode', $periode)
                    ->where('ligne_id', $ligneRetour->id)
                    ->exists();
                
                if ($voyageExiste) {
                    continue; // Ne pas créer de doublon
                }

                // Vérifier que le bus est toujours disponible
                if (!$voyageHier->bus->disponible) {
                    $erreurs[] = "Bus {$voyageHier->bus->immatriculation} non disponible pour le retour";
                    continue;
                }

                // Vérifier que le conducteur n'est pas en repos
                if ($voyageHier->conducteur->estEnRepos($date) || $voyageHier->conducteur->estIndisponible($date)) {
                    $motif = $voyageHier->conducteur->getMotifIndisponibilite($date);
                    $erreurs[] = "Conducteur {$voyageHier->conducteur->prenom} {$voyageHier->conducteur->nom} indisponible pour le retour de {$voyageHier->ligne->nom} ({$motif})";
                    continue;
                }

                // Vérifier que les conducteurs sont disponibles (pas déjà assignés)
                if (in_array($voyageHier->conducteur_id, $conducteursAssignes)) {
                    $erreurs[] = "Conducteur déjà assigné pour le retour de {$voyageHier->ligne->nom}";
                    continue;
                }

                // Vérifier le 2ème conducteur si nuit
                if ($voyageHier->conducteur2) {
                    if ($voyageHier->conducteur2->estEnRepos($date) || $voyageHier->conducteur2->estIndisponible($date)) {
                        $motif = $voyageHier->conducteur2->getMotifIndisponibilite($date);
                        $erreurs[] = "2ème conducteur {$voyageHier->conducteur2->prenom} indisponible ({$motif}) - Le retour nécessitera un remplaçant";
                    }
                }

                // INVERSION DES CONDUCTEURS pour le retour:
                // Relais aller (conducteur_2) → Principal retour
                // Principal aller (conducteur) → Relais retour
                $conducteurPrincipalRetour = $voyageHier->conducteur2 ?? $voyageHier->conducteur;
                $conducteurRelaisRetour = $voyageHier->conducteur2 ? $voyageHier->conducteur : null;

                // Créer le voyage retour avec conducteurs inversés
                $voyageData = [
                    'date_depart' => $date . ' ' . $ligneRetour->horaire,
                    'ligne_id' => $ligneRetour->id,
                    'bus_id' => $voyageHier->bus_id,
                    'conducteur_id' => $conducteurPrincipalRetour->id,
                    'periode' => $periode,
                    'sens' => 'Retour',
                    'statut' => 'Planifié',
                ];

                if ($conducteurRelaisRetour && !in_array($conducteurRelaisRetour->id, $conducteursAssignes)) {
                    $voyageData['conducteur_2_id'] = $conducteurRelaisRetour->id;
                }

                Voyage::create($voyageData);

                $conducteurInfo = $conducteurPrincipalRetour->prenom . ' ' . $conducteurPrincipalRetour->nom;
                if ($conducteurRelaisRetour) {
                    $conducteurInfo .= ' + ' . $conducteurRelaisRetour->prenom . ' ' . $conducteurRelaisRetour->nom;
                }

                $voyagesCrees[] = [
                    'ligne' => $ligneRetour->nom,
                    'horaire' => $ligneRetour->horaire_formate,
                    'bus' => $voyageHier->bus->immatriculation,
                    'conducteur' => $conducteurInfo,
                    'periode' => $periode,
                    'type' => 'Retour (suite aller hier)',
                ];

                $busAssignes[] = $voyageHier->bus_id;
                $conducteursAssignes[] = $conducteurPrincipalRetour->id;
                if ($conducteurRelaisRetour) {
                    $conducteursAssignes[] = $conducteurRelaisRetour->id;
                }
                
                // Marquer la ligne aller originale comme programmée (le retour est programmé)
                $lignesProgrammees[] = $voyageHier->ligne_id;
            }

            // ÉTAPE 2: Programmer les ALLERS pour les bus/conducteurs ayant fait un RETOUR hier
            // On prend en compte tous les voyages programmés (Planifié, En cours, Terminé), pas seulement les terminés
            $voyagesRetourHier = Voyage::with(['ligne', 'bus', 'conducteur', 'conducteur2'])
                ->whereDate('date_depart', $dateHier)
                ->where('periode', $periode)
                ->whereIn('statut', ['Planifié', 'En cours', 'Terminé']) // Considère la programmation existante
                ->whereIn('ligne_id', $lignesRetourIds) // Lignes retour des lignes sélectionnées
                ->whereHas('ligne', function($q) {
                    $q->where('type', 'Retour');
                })
                ->get();

            foreach ($voyagesRetourHier as $voyageHier) {
                if (in_array($voyageHier->bus_id, $busAssignes)) {
                    continue;
                }

                $ligneAller = $voyageHier->ligne->getLigneAller();
                
                if (!$ligneAller) {
                    $erreurs[] = "Ligne aller non trouvée pour {$voyageHier->ligne->nom}";
                    continue;
                }

                // VÉRIFICATION ANTI-DOUBLON: Vérifier si ce voyage aller existe déjà
                $voyageExiste = Voyage::whereDate('date_depart', $date)
                    ->where('periode', $periode)
                    ->where('ligne_id', $ligneAller->id)
                    ->exists();
                
                if ($voyageExiste) {
                    continue; // Ne pas créer de doublon
                }

                if (!$voyageHier->bus->disponible) {
                    $erreurs[] = "Bus {$voyageHier->bus->immatriculation} non disponible pour l'aller";
                    continue;
                }

                // Vérifier que le conducteur n'est pas en repos
                if ($voyageHier->conducteur->estEnRepos($date) || $voyageHier->conducteur->estIndisponible($date)) {
                    $motif = $voyageHier->conducteur->getMotifIndisponibilite($date);
                    $erreurs[] = "Conducteur {$voyageHier->conducteur->prenom} {$voyageHier->conducteur->nom} indisponible pour l'aller de {$voyageHier->ligne->nom} ({$motif})";
                    continue;
                }

                if (in_array($voyageHier->conducteur_id, $conducteursAssignes)) {
                    $erreurs[] = "Conducteur déjà assigné pour l'aller de {$voyageHier->ligne->nom}";
                    continue;
                }

                // Vérifier le 2ème conducteur si nuit
                if ($voyageHier->conducteur2) {
                    if ($voyageHier->conducteur2->estEnRepos($date) || $voyageHier->conducteur2->estIndisponible($date)) {
                        $motif = $voyageHier->conducteur2->getMotifIndisponibilite($date);
                        $erreurs[] = "2ème conducteur {$voyageHier->conducteur2->prenom} indisponible ({$motif}) - L'aller nécessitera un remplaçant";
                    }
                }

                // INVERSION DES CONDUCTEURS pour l'aller:
                // Relais retour (conducteur_2) → Principal aller
                // Principal retour (conducteur) → Relais aller
                $conducteurPrincipalAller = $voyageHier->conducteur2 ?? $voyageHier->conducteur;
                $conducteurRelaisAller = $voyageHier->conducteur2 ? $voyageHier->conducteur : null;

                $voyageData = [
                    'date_depart' => $date . ' ' . $ligneAller->horaire,
                    'ligne_id' => $ligneAller->id,
                    'bus_id' => $voyageHier->bus_id,
                    'conducteur_id' => $conducteurPrincipalAller->id,
                    'periode' => $periode,
                    'sens' => 'Aller',
                    'statut' => 'Planifié',
                ];

                if ($conducteurRelaisAller && !in_array($conducteurRelaisAller->id, $conducteursAssignes)) {
                    $voyageData['conducteur_2_id'] = $conducteurRelaisAller->id;
                }

                Voyage::create($voyageData);

                $conducteurInfo = $conducteurPrincipalAller->prenom . ' ' . $conducteurPrincipalAller->nom;
                if ($conducteurRelaisAller) {
                    $conducteurInfo .= ' + ' . $conducteurRelaisAller->prenom . ' ' . $conducteurRelaisAller->nom;
                }

                $voyagesCrees[] = [
                    'ligne' => $ligneAller->nom,
                    'horaire' => $ligneAller->horaire_formate,
                    'bus' => $voyageHier->bus->immatriculation,
                    'conducteur' => $conducteurInfo,
                    'periode' => $periode,
                    'type' => 'Aller (suite retour hier)',
                ];

                $busAssignes[] = $voyageHier->bus_id;
                $conducteursAssignes[] = $conducteurPrincipalAller->id;
                if ($conducteurRelaisAller) {
                    $conducteursAssignes[] = $conducteurRelaisAller->id;
                }
                
                // Marquer cette ligne aller comme programmée
                $lignesProgrammees[] = $ligneAller->id;
            }

            // ÉTAPE 3: Pour les lignes non couvertes, assigner de nouveaux bus/conducteurs depuis Parakou
            foreach ($lignesSelectionnees as $ligne) {
                // Vérifier si cette ligne a déjà été programmée dans cette génération (pour "Les deux" périodes)
                if (in_array($ligne->id, $lignesProgrammees)) {
                    continue;
                }
                
                // Vérifier si cette ligne a déjà un voyage aujourd'hui pour cette période
                $dejaProgamme = Voyage::whereDate('date_depart', $date)
                    ->where('periode', $periode)
                    ->where('ligne_id', $ligne->id)
                    ->exists();

                if ($dejaProgamme) {
                    continue;
                }

                // Pour les lignes Aller seulement: vérifier si la ligne retour correspondante est déjà programmée
                // (pour éviter doublons quand le retour existe déjà suite à un aller précédent)
                // Les lignes Retour explicitement sélectionnées ne sont pas concernées par cette vérification
                if ($ligne->type === 'Aller') {
                    $ligneRetour = $ligne->getLigneRetour();
                    $retourProgramme = $ligneRetour ? Voyage::whereDate('date_depart', $date)
                        ->where('periode', $periode)
                        ->where('ligne_id', $ligneRetour->id)
                        ->exists() : false;

                    if ($retourProgramme) {
                        continue;
                    }
                }

                // Trouver un bus disponible à la ville de départ de la ligne
                // en tenant compte de la position actuelle basée sur la programmation précédente
                $bus = Bus::where('disponible', true)
                    ->whereNotIn('id', $busAssignes)
                    ->where('ville_actuelle', $ligne->ville_depart)
                    ->first();

                // Si aucun bus trouvé à la ville de départ, on peut prendre n'importe quel bus disponible
                // mais on l'indique comme warning
                if (!$bus) {
                    $bus = Bus::where('disponible', true)
                        ->whereNotIn('id', $busAssignes)
                        ->first();
                    
                    if ($bus) {
                        $erreurs[] = "Attention: Bus {$bus->immatriculation} assigné pour {$ligne->nom} mais actuellement à {$bus->ville_actuelle} (devrait être à {$ligne->ville_depart})";
                    }
                }

                if (!$bus) {
                    $erreurs[] = "Pas de bus disponible pour {$ligne->nom} ({$periode})";
                    continue;
                }

                // Trouver des conducteurs disponibles à la ville de départ (excluant ceux en repos)
                $conducteurQuery = Conducteur::disponible($date)
                    ->where('ville_actuelle', $ligne->ville_depart)
                    ->whereNotIn('id', $conducteursAssignes);

                $conducteur = null;
                $conducteur2 = null;

                if ($periode === 'Nuit') {
                    $conducteur = $conducteurQuery->clone()
                        ->where(function($q) {
                            $q->where('specialiste_nuit', true)
                              ->orWhere('remplacant_nuit', true);
                        })
                        ->first();
                    
                    if (!$conducteur) {
                        $conducteur = $conducteurQuery->clone()->first();
                    }

                    if ($conducteur) {
                        $conducteursAssignes[] = $conducteur->id;
                        
                        $conducteur2 = Conducteur::disponible($date)
                            ->where('ville_actuelle', $ligne->ville_depart)
                            ->whereNotIn('id', $conducteursAssignes)
                            ->first();
                        
                        if (!$conducteur2) {
                            $erreurs[] = "Pas de 2ème conducteur disponible à {$ligne->ville_depart} pour {$ligne->nom} (Nuit)";
                        }
                    }
                } else {
                    $conducteur = $conducteurQuery->first();
                }

                if (!$conducteur) {
                    $erreurs[] = "Pas de conducteur disponible à {$ligne->ville_depart} pour {$ligne->nom} ({$periode})";
                    continue;
                }

                $voyageData = [
                    'date_depart' => $date . ' ' . $ligne->horaire,
                    'ligne_id' => $ligne->id,
                    'bus_id' => $bus->id,
                    'conducteur_id' => $conducteur->id,
                    'periode' => $periode,
                    'sens' => $ligne->type === 'Retour' ? 'Retour' : 'Aller',
                    'statut' => 'Planifié',
                ];

                if ($conducteur2) {
                    $voyageData['conducteur_2_id'] = $conducteur2->id;
                }

                Voyage::create($voyageData);

                $conducteurInfo = $conducteur->prenom . ' ' . $conducteur->nom;
                if ($conducteur2) {
                    $conducteurInfo .= ' + ' . $conducteur2->prenom . ' ' . $conducteur2->nom;
                }

                $voyagesCrees[] = [
                    'ligne' => $ligne->nom,
                    'horaire' => $ligne->horaire_formate,
                    'bus' => $bus->immatriculation,
                    'conducteur' => $conducteurInfo,
                    'periode' => $periode,
                    'type' => $ligne->type === 'Retour' ? 'Nouveau Retour' : 'Nouvel Aller',
                ];

                $busAssignes[] = $bus->id;
                $conducteursAssignes[] = $conducteur->id;
                if ($conducteur2) {
                    $conducteursAssignes[] = $conducteur2->id;
                }
                
                // Marquer cette ligne comme programmée
                $lignesProgrammees[] = $ligne->id;
            }
        }

        return view('voyages.generer-resultat', compact('voyagesCrees', 'erreurs', 'date', 'periodes'));
    }

    /**
     * Génère les voyages depuis l'aperçu avec les ajustements de l'utilisateur
     */
    private function genererDepuisApercu(Request $request)
    {
        $date = $request->date;
        $periodes = $request->periode === 'Les deux' ? ['Jour', 'Nuit'] : [$request->periode];
        $voyagesData = $request->voyages;
        
        $voyagesCrees = [];
        $erreurs = [];
        
        foreach ($voyagesData as $index => $voyageInfo) {
            // Ignorer si la ligne est désactivée
            if (!isset($voyageInfo['active']) || $voyageInfo['active'] != '1') {
                continue;
            }
            
            // Ignorer si pas de ligne, bus ou conducteur
            if (empty($voyageInfo['ligne_id']) || empty($voyageInfo['bus_id']) || empty($voyageInfo['conducteur_id'])) {
                continue;
            }
            
            $ligne = Ligne::find($voyageInfo['ligne_id']);
            $bus = Bus::find($voyageInfo['bus_id']);
            $conducteur = Conducteur::find($voyageInfo['conducteur_id']);
            $conducteur2 = isset($voyageInfo['conducteur_2_id']) && !empty($voyageInfo['conducteur_2_id']) 
                ? Conducteur::find($voyageInfo['conducteur_2_id']) 
                : null;
            
            if (!$ligne || !$bus || !$conducteur) {
                $erreurs[] = "Données invalides pour la ligne index {$index}";
                continue;
            }
            
            $periode = $voyageInfo['periode'] ?? 'Jour';
            
            // Valider que la période est correcte
            if (!in_array($periode, ['Jour', 'Nuit'])) {
                $erreurs[] = "Période invalide '{$periode}' pour {$ligne->nom} - ignoré";
                continue;
            }
            
            // Vérifier si ce voyage existe déjà
            $voyageExiste = Voyage::whereDate('date_depart', $date)
                ->where('periode', $periode)
                ->where('ligne_id', $ligne->id)
                ->exists();
            
            if ($voyageExiste) {
                $erreurs[] = "Voyage déjà existant pour {$ligne->nom} ({$periode})";
                continue;
            }
            
            // Créer le voyage
            $voyageData = [
                'date_depart' => $date . ' ' . $ligne->horaire,
                'ligne_id' => $ligne->id,
                'bus_id' => $bus->id,
                'conducteur_id' => $conducteur->id,
                'periode' => $periode,
                'sens' => $ligne->type === 'Retour' ? 'Retour' : 'Aller',
                'statut' => 'Planifié',
            ];
            
            if ($conducteur2) {
                $voyageData['conducteur_2_id'] = $conducteur2->id;
            }
            
            Voyage::create($voyageData);
            
            $conducteurInfo = $conducteur->prenom . ' ' . $conducteur->nom;
            if ($conducteur2) {
                $conducteurInfo .= ' + ' . $conducteur2->prenom . ' ' . $conducteur2->nom;
            }
            
            $voyagesCrees[] = [
                'ligne' => $ligne->nom,
                'horaire' => $ligne->horaire_formate,
                'bus' => $bus->immatriculation,
                'conducteur' => $conducteurInfo,
                'periode' => $periode,
                'type' => $ligne->type === 'Retour' ? 'Retour' : 'Aller',
            ];
        }
        
        return view('voyages.generer-resultat', compact('voyagesCrees', 'erreurs', 'date', 'periodes'));
    }

    /**
     * Aperçu de la programmation avant génération
     * Affiche TOUTES les lignes disponibles:
     * 1. Propositions basées sur la veille (si disponible): même bus, conducteurs inversés
     * 2. Lignes non couvertes: l'utilisateur peut les définir manuellement
     */
    public function previewGeneration(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'periode' => 'required|in:Jour,Nuit,Les deux',
        ]);

        $date = $request->date;
        $dateHier = date('Y-m-d', strtotime($date . ' -1 day'));
        $periodeSelectionnee = $request->periode;
        $periodes = $periodeSelectionnee === 'Les deux' ? ['Jour', 'Nuit'] : [$periodeSelectionnee];
        
        $propositions = [];
        $alertes = [];
        
        // Vérifier si la programmation de la veille existe
        $voyagesHierCount = Voyage::whereDate('date_depart', $dateHier)->count();
        
        // Lignes déjà proposées (pour éviter doublons)
        $lignesProposees = [];
        $busUtilises = [];

        // Voyages déjà existants pour cette date
        $voyagesExistants = Voyage::whereDate('date_depart', $date)->get();
        $lignesDejaEnBase = $voyagesExistants->pluck('ligne_id')->toArray();

        // ========================================
        // ÉTAPE 1: Propositions basées sur la veille
        // ========================================
        if ($voyagesHierCount > 0) {
            $tousVoyagesHier = Voyage::with(['ligne', 'bus', 'conducteur', 'conducteur2'])
                ->whereDate('date_depart', $dateHier)
                ->orderBy('date_depart')
                ->get();

            foreach ($tousVoyagesHier as $voyageHier) {
                $ligneHier = $voyageHier->ligne;
                
                // Déterminer la ligne inverse (ALLER → RETOUR ou RETOUR → ALLER)
                if ($ligneHier->type === 'Aller') {
                    $ligneAujourdhui = $ligneHier->getLigneRetour();
                    $source = 'retour_suite_aller';
                } else {
                    $ligneAujourdhui = $ligneHier->getLigneAller();
                    $source = 'aller_suite_retour';
                }
                
                if (!$ligneAujourdhui) {
                    $alertes[] = "Ligne inverse non trouvée pour {$ligneHier->nom} (Bus {$voyageHier->bus->immatriculation})";
                    continue;
                }
                
                // Déterminer la période selon l'heure
                $heure = (int) substr($ligneAujourdhui->horaire, 0, 2);
                $periode = ($heure >= 19) ? 'Nuit' : 'Jour';
                
                // Filtrer selon la période sélectionnée
                if ($periodeSelectionnee !== 'Les deux' && $periode !== $periodeSelectionnee) {
                    continue;
                }
                
                // Vérifier si ce voyage existe déjà en base
                if (in_array($ligneAujourdhui->id, $lignesDejaEnBase)) {
                    continue;
                }
                
                // Éviter les doublons de ligne dans les propositions
                if (in_array($ligneAujourdhui->id, $lignesProposees)) {
                    continue;
                }
                
                // INVERSION DES CONDUCTEURS
                $conducteurPrincipal = $voyageHier->conducteur2 ?? $voyageHier->conducteur;
                $conducteurRelais = $voyageHier->conducteur2 ? $voyageHier->conducteur : null;
                
                // Vérifier disponibilité
                $conducteurPrincipalOk = $conducteurPrincipal && 
                    !$conducteurPrincipal->estEnRepos($date) && 
                    !$conducteurPrincipal->estIndisponible($date);
                
                $conducteurRelaisOk = !$conducteurRelais || (
                    !$conducteurRelais->estEnRepos($date) && 
                    !$conducteurRelais->estIndisponible($date)
                );
                
                $bus = $voyageHier->bus->disponible ? $voyageHier->bus : null;
                
                $proposition = [
                    'ligne' => $ligneAujourdhui,
                    'ligne_hier' => $ligneHier,
                    'periode' => $periode,
                    'bus' => $bus,
                    'conducteur' => $conducteurPrincipalOk ? $conducteurPrincipal : null,
                    'conducteur2' => ($conducteurRelaisOk && $conducteurRelais) ? $conducteurRelais : null,
                    'possible' => $bus && $conducteurPrincipalOk,
                    'nuit_complet' => $periode === 'Nuit' ? ($conducteurPrincipalOk && $conducteurRelaisOk && $conducteurRelais) : true,
                    'source' => $source,
                    'voyage_hier_id' => $voyageHier->id,
                    'horaire_hier' => $voyageHier->date_depart,
                ];
                
                $propositions[] = $proposition;
                $lignesProposees[] = $ligneAujourdhui->id;
                if ($bus) $busUtilises[] = $bus->id;
            }
        }

        // ========================================
        // ÉTAPE 2: Ajouter les lignes non couvertes
        // ========================================
        $toutesLignes = Ligne::orderBy('horaire')->get();
        
        foreach ($toutesLignes as $ligne) {
            // Vérifier si déjà proposée ou en base
            if (in_array($ligne->id, $lignesProposees) || in_array($ligne->id, $lignesDejaEnBase)) {
                continue;
            }
            
            // Déterminer la période
            $heure = (int) substr($ligne->horaire, 0, 2);
            $periode = ($heure >= 19) ? 'Nuit' : 'Jour';
            
            // Filtrer selon la période sélectionnée
            if ($periodeSelectionnee !== 'Les deux' && $periode !== $periodeSelectionnee) {
                continue;
            }
            
            // Chercher un bus disponible à la ville de départ
            $bus = Bus::where('disponible', true)
                ->whereNotIn('id', $busUtilises)
                ->where('ville_actuelle', $ligne->ville_depart)
                ->first();
            
            // Chercher un conducteur disponible
            $conducteur = null;
            $conducteur2 = null;
            
            if ($periode === 'Nuit') {
                $conducteur = Conducteur::where('actif', true)
                    ->where('ville_actuelle', $ligne->ville_depart)
                    ->where(function($q) {
                        $q->where('specialiste_nuit', true)
                          ->orWhere('remplacant_nuit', true);
                    })
                    ->first();
                
                if (!$conducteur) {
                    $conducteur = Conducteur::where('actif', true)
                        ->where('ville_actuelle', $ligne->ville_depart)
                        ->first();
                }
                
                if ($conducteur) {
                    $conducteur2 = Conducteur::where('actif', true)
                        ->where('ville_actuelle', $ligne->ville_depart)
                        ->where('id', '!=', $conducteur->id)
                        ->first();
                }
            } else {
                $conducteur = Conducteur::where('actif', true)
                    ->where('ville_actuelle', $ligne->ville_depart)
                    ->first();
            }
            
            $proposition = [
                'ligne' => $ligne,
                'ligne_hier' => null,
                'periode' => $periode,
                'bus' => $bus,
                'conducteur' => $conducteur,
                'conducteur2' => $conducteur2,
                'possible' => $bus && $conducteur,
                'nuit_complet' => $periode === 'Nuit' ? ($conducteur && $conducteur2) : true,
                'source' => 'nouveau',
                'voyage_hier_id' => null,
                'horaire_hier' => null,
            ];
            
            $propositions[] = $proposition;
            $lignesProposees[] = $ligne->id;
            if ($bus) $busUtilises[] = $bus->id;
        }

        // Trier les propositions par horaire
        usort($propositions, function($a, $b) {
            return strcmp($a['ligne']->horaire, $b['ligne']->horaire);
        });

        return view('voyages.generer-preview', compact('propositions', 'alertes', 'date', 'periodes', 'voyagesHierCount'));
    }

    /**
     * Génère automatiquement la programmation pour une période donnée
     * Applique la même logique que la génération journalière, jour après jour
     */
    public function genererSurPeriode(Request $request)
    {
        $request->validate([
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'periode_range' => 'required|in:Jour,Nuit,Les deux',
            'lignes' => 'nullable|array',
            'lignes.*' => 'exists:lignes,id',
        ]);

        $dateDebut = $request->date_debut;
        $dateFin = $request->date_fin;
        $periodeSelectionnee = $request->periode_range;
        $periodes = $periodeSelectionnee === 'Les deux' ? ['Jour', 'Nuit'] : [$periodeSelectionnee];
        
        // Limiter à 30 jours maximum pour éviter les problèmes de performance
        $debut = new \DateTime($dateDebut);
        $fin = new \DateTime($dateFin);
        $diff = $debut->diff($fin)->days;
        
        if ($diff > 30) {
            return redirect()->back()
                ->with('error', 'La période ne peut pas dépasser 30 jours.')
                ->withInput();
        }

        // Lignes sélectionnées (si aucune, prendre toutes les lignes Aller)
        $lignesSelectionnees = $request->lignes ? Ligne::whereIn('id', $request->lignes)->get() 
            : Ligne::aller()->get();

        $resultatsParJour = [];
        $totalVoyagesCrees = 0;
        $totalErreurs = 0;

        // Boucler sur chaque jour de la période
        $currentDate = clone $debut;
        while ($currentDate <= $fin) {
            $date = $currentDate->format('Y-m-d');
            $result = $this->genererPourUnJour($date, $periodes, $lignesSelectionnees);
            
            $resultatsParJour[$date] = [
                'voyages' => $result['voyagesCrees'],
                'erreurs' => $result['erreurs'],
            ];
            
            $totalVoyagesCrees += count($result['voyagesCrees']);
            $totalErreurs += count($result['erreurs']);
            
            $currentDate->modify('+1 day');
        }

        return view('voyages.generer-periode-resultat', compact(
            'resultatsParJour', 
            'totalVoyagesCrees', 
            'totalErreurs', 
            'dateDebut', 
            'dateFin', 
            'periodes'
        ));
    }

    /**
     * Aperçu de la programmation sur une période
     */
    public function previewPeriode(Request $request)
    {
        $request->validate([
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'periode_range' => 'required|in:Jour,Nuit,Les deux',
        ]);

        $dateDebut = $request->date_debut;
        $dateFin = $request->date_fin;
        $periodeSelectionnee = $request->periode_range;
        $periodes = $periodeSelectionnee === 'Les deux' ? ['Jour', 'Nuit'] : [$periodeSelectionnee];

        // Limiter à 30 jours maximum
        $debut = new \DateTime($dateDebut);
        $fin = new \DateTime($dateFin);
        $diff = $debut->diff($fin)->days;
        
        if ($diff > 30) {
            return redirect()->back()
                ->with('error', 'La période ne peut pas dépasser 30 jours.')
                ->withInput();
        }

        // Lignes sélectionnées
        $lignesSelectionnees = $request->lignes ? Ligne::whereIn('id', $request->lignes)->get() 
            : Ligne::aller()->get();

        $aperçuParJour = [];
        $totalPropositions = 0;
        $totalAlertes = 0;

        // Simuler la génération pour chaque jour
        $currentDate = clone $debut;
        while ($currentDate <= $fin) {
            $date = $currentDate->format('Y-m-d');
            $apercu = $this->simulerGenerationPourUnJour($date, $periodes, $lignesSelectionnees);
            
            $aperçuParJour[$date] = $apercu;
            $totalPropositions += count($apercu['propositions']);
            $totalAlertes += count($apercu['alertes']);
            
            $currentDate->modify('+1 day');
        }

        return view('voyages.generer-periode-preview', compact(
            'aperçuParJour', 
            'totalPropositions', 
            'totalAlertes', 
            'dateDebut', 
            'dateFin', 
            'periodes',
            'lignesSelectionnees'
        ));
    }

    /**
     * Logique de génération pour un jour spécifique (utilisée par genererSurPeriode)
     */
    private function genererPourUnJour($date, $periodes, $lignesSelectionnees)
    {
        $dateHier = date('Y-m-d', strtotime($date . ' -1 day'));
        
        $voyagesCrees = [];
        $erreurs = [];
        
        // Suivi des lignes déjà programmées
        $lignesProgrammees = [];
        
        // Récupérer les conducteurs en repos ou indisponibles à cette date
        $conducteursEnRepos = Conducteur::where('actif', true)
            ->where(function($q) use ($date) {
                $q->whereHas('repos', function($rq) use ($date) {
                    $rq->where('date_debut', '<=', $date)
                       ->where('date_fin', '>=', $date);
                })->orWhereHas('indisponibilites', function($iq) use ($date) {
                    $iq->where('date_debut', '<=', $date)
                       ->where('date_fin', '>=', $date);
                });
            })
            ->pluck('id')
            ->toArray();

        foreach ($periodes as $periode) {
            // Récupérer les bus et conducteurs déjà assignés
            $busAssignes = Voyage::whereDate('date_depart', $date)
                ->where('periode', $periode)
                ->pluck('bus_id')
                ->toArray();
            
            $conducteursAssignes = Voyage::whereDate('date_depart', $date)
                ->where('periode', $periode)
                ->pluck('conducteur_id')
                ->toArray();
            
            $conducteursAssignes = array_merge($conducteursAssignes, $conducteursEnRepos);
            
            $conducteurs2Assignes = Voyage::whereDate('date_depart', $date)
                ->where('periode', $periode)
                ->whereNotNull('conducteur_2_id')
                ->pluck('conducteur_2_id')
                ->toArray();
            
            $conducteursAssignes = array_merge($conducteursAssignes, $conducteurs2Assignes);

            // IDs des lignes
            $lignesIds = $lignesSelectionnees->pluck('id')->toArray();
            $lignesRetourIds = $lignesSelectionnees->map(fn($l) => $l->getLigneRetour()?->id)->filter()->toArray();

            // ÉTAPE 1: RETOURS suite aux ALLERS d'hier
            $voyagesAllerHier = Voyage::with(['ligne', 'bus', 'conducteur', 'conducteur2'])
                ->whereDate('date_depart', $dateHier)
                ->where('periode', $periode)
                ->whereIn('statut', ['Planifié', 'En cours', 'Terminé'])
                ->whereIn('ligne_id', $lignesIds)
                ->whereHas('ligne', function($q) {
                    $q->where('type', 'Aller');
                })
                ->get();

            foreach ($voyagesAllerHier as $voyageHier) {
                if (in_array($voyageHier->bus_id, $busAssignes)) continue;

                $ligneRetour = $voyageHier->ligne->getLigneRetour();
                if (!$ligneRetour) continue;

                // Vérifier si voyage existe déjà
                $voyageExiste = Voyage::whereDate('date_depart', $date)
                    ->where('periode', $periode)
                    ->where('ligne_id', $ligneRetour->id)
                    ->exists();
                
                if ($voyageExiste) continue;

                if (!$voyageHier->bus->disponible) {
                    $erreurs[] = "Bus {$voyageHier->bus->immatriculation} non disponible";
                    continue;
                }

                // Vérifier conducteur
                if ($voyageHier->conducteur->estEnRepos($date) || $voyageHier->conducteur->estIndisponible($date)) {
                    $motif = $voyageHier->conducteur->getMotifIndisponibilite($date);
                    $erreurs[] = "{$voyageHier->conducteur->prenom} indisponible ({$motif})";
                    continue;
                }

                if (in_array($voyageHier->conducteur_id, $conducteursAssignes)) continue;

                // Inversion des conducteurs
                $conducteurPrincipalRetour = $voyageHier->conducteur2 ?? $voyageHier->conducteur;
                $conducteurRelaisRetour = $voyageHier->conducteur2 ? $voyageHier->conducteur : null;

                $voyageData = [
                    'date_depart' => $date . ' ' . $ligneRetour->horaire,
                    'ligne_id' => $ligneRetour->id,
                    'bus_id' => $voyageHier->bus_id,
                    'conducteur_id' => $conducteurPrincipalRetour->id,
                    'periode' => $periode,
                    'sens' => 'Retour',
                    'statut' => 'Planifié',
                ];

                if ($conducteurRelaisRetour && !in_array($conducteurRelaisRetour->id, $conducteursAssignes)) {
                    $voyageData['conducteur_2_id'] = $conducteurRelaisRetour->id;
                }

                Voyage::create($voyageData);

                $conducteurInfo = $conducteurPrincipalRetour->prenom . ' ' . $conducteurPrincipalRetour->nom;
                if ($conducteurRelaisRetour) {
                    $conducteurInfo .= ' + ' . $conducteurRelaisRetour->prenom . ' ' . $conducteurRelaisRetour->nom;
                }

                $voyagesCrees[] = [
                    'ligne' => $ligneRetour->nom,
                    'horaire' => $ligneRetour->horaire_formate,
                    'bus' => $voyageHier->bus->immatriculation,
                    'conducteur' => $conducteurInfo,
                    'periode' => $periode,
                    'type' => 'Retour (suite aller)',
                ];

                $busAssignes[] = $voyageHier->bus_id;
                $conducteursAssignes[] = $conducteurPrincipalRetour->id;
                if ($conducteurRelaisRetour) {
                    $conducteursAssignes[] = $conducteurRelaisRetour->id;
                }
                $lignesProgrammees[] = $voyageHier->ligne_id;
            }

            // ÉTAPE 2: ALLERS suite aux RETOURS d'hier
            $voyagesRetourHier = Voyage::with(['ligne', 'bus', 'conducteur', 'conducteur2'])
                ->whereDate('date_depart', $dateHier)
                ->where('periode', $periode)
                ->whereIn('statut', ['Planifié', 'En cours', 'Terminé'])
                ->whereIn('ligne_id', $lignesRetourIds)
                ->whereHas('ligne', function($q) {
                    $q->where('type', 'Retour');
                })
                ->get();

            foreach ($voyagesRetourHier as $voyageHier) {
                if (in_array($voyageHier->bus_id, $busAssignes)) continue;

                $ligneAller = $voyageHier->ligne->getLigneAller();
                if (!$ligneAller) continue;

                $voyageExiste = Voyage::whereDate('date_depart', $date)
                    ->where('periode', $periode)
                    ->where('ligne_id', $ligneAller->id)
                    ->exists();
                
                if ($voyageExiste) continue;

                if (!$voyageHier->bus->disponible) continue;

                if ($voyageHier->conducteur->estEnRepos($date) || $voyageHier->conducteur->estIndisponible($date)) {
                    $motif = $voyageHier->conducteur->getMotifIndisponibilite($date);
                    $erreurs[] = "{$voyageHier->conducteur->prenom} indisponible ({$motif})";
                    continue;
                }

                if (in_array($voyageHier->conducteur_id, $conducteursAssignes)) continue;

                // Inversion des conducteurs
                $conducteurPrincipalAller = $voyageHier->conducteur2 ?? $voyageHier->conducteur;
                $conducteurRelaisAller = $voyageHier->conducteur2 ? $voyageHier->conducteur : null;

                $voyageData = [
                    'date_depart' => $date . ' ' . $ligneAller->horaire,
                    'ligne_id' => $ligneAller->id,
                    'bus_id' => $voyageHier->bus_id,
                    'conducteur_id' => $conducteurPrincipalAller->id,
                    'periode' => $periode,
                    'sens' => 'Aller',
                    'statut' => 'Planifié',
                ];

                if ($conducteurRelaisAller && !in_array($conducteurRelaisAller->id, $conducteursAssignes)) {
                    $voyageData['conducteur_2_id'] = $conducteurRelaisAller->id;
                }

                Voyage::create($voyageData);

                $conducteurInfo = $conducteurPrincipalAller->prenom . ' ' . $conducteurPrincipalAller->nom;
                if ($conducteurRelaisAller) {
                    $conducteurInfo .= ' + ' . $conducteurRelaisAller->prenom . ' ' . $conducteurRelaisAller->nom;
                }

                $voyagesCrees[] = [
                    'ligne' => $ligneAller->nom,
                    'horaire' => $ligneAller->horaire_formate,
                    'bus' => $voyageHier->bus->immatriculation,
                    'conducteur' => $conducteurInfo,
                    'periode' => $periode,
                    'type' => 'Aller (suite retour)',
                ];

                $busAssignes[] = $voyageHier->bus_id;
                $conducteursAssignes[] = $conducteurPrincipalAller->id;
                if ($conducteurRelaisAller) {
                    $conducteursAssignes[] = $conducteurRelaisAller->id;
                }
                $lignesProgrammees[] = $ligneAller->id;
            }

            // ÉTAPE 3: Lignes non couvertes
            foreach ($lignesSelectionnees as $ligne) {
                if (in_array($ligne->id, $lignesProgrammees)) continue;
                
                $dejaProgamme = Voyage::whereDate('date_depart', $date)
                    ->where('periode', $periode)
                    ->where('ligne_id', $ligne->id)
                    ->exists();

                if ($dejaProgamme) continue;

                if ($ligne->type === 'Aller') {
                    $ligneRetour = $ligne->getLigneRetour();
                    $retourProgramme = $ligneRetour ? Voyage::whereDate('date_depart', $date)
                        ->where('periode', $periode)
                        ->where('ligne_id', $ligneRetour->id)
                        ->exists() : false;

                    if ($retourProgramme) continue;
                }

                // Chercher un bus disponible
                $bus = Bus::where('disponible', true)
                    ->whereNotIn('id', $busAssignes)
                    ->where('ville_actuelle', $ligne->ville_depart)
                    ->first();

                if (!$bus) {
                    $bus = Bus::where('disponible', true)
                        ->whereNotIn('id', $busAssignes)
                        ->first();
                    
                    if ($bus) {
                        $erreurs[] = "Bus {$bus->immatriculation} pour {$ligne->nom} (mauvaise position)";
                    }
                }

                if (!$bus) {
                    $erreurs[] = "Pas de bus pour {$ligne->nom} ({$periode})";
                    continue;
                }

                // Chercher conducteur
                $conducteurQuery = Conducteur::disponible($date)
                    ->where('ville_actuelle', $ligne->ville_depart)
                    ->whereNotIn('id', $conducteursAssignes);

                $conducteur = null;
                $conducteur2 = null;

                if ($periode === 'Nuit') {
                    $conducteur = $conducteurQuery->clone()
                        ->where(function($q) {
                            $q->where('specialiste_nuit', true)->orWhere('remplacant_nuit', true);
                        })
                        ->first();
                    
                    if (!$conducteur) {
                        $conducteur = $conducteurQuery->clone()->first();
                    }

                    if ($conducteur) {
                        $conducteursAssignes[] = $conducteur->id;
                        $conducteur2 = Conducteur::disponible($date)
                            ->where('ville_actuelle', $ligne->ville_depart)
                            ->whereNotIn('id', $conducteursAssignes)
                            ->first();
                    }
                } else {
                    $conducteur = $conducteurQuery->first();
                }

                if (!$conducteur) {
                    $erreurs[] = "Pas de conducteur pour {$ligne->nom} ({$periode})";
                    continue;
                }

                $voyageData = [
                    'date_depart' => $date . ' ' . $ligne->horaire,
                    'ligne_id' => $ligne->id,
                    'bus_id' => $bus->id,
                    'conducteur_id' => $conducteur->id,
                    'periode' => $periode,
                    'sens' => $ligne->type === 'Retour' ? 'Retour' : 'Aller',
                    'statut' => 'Planifié',
                ];

                if ($conducteur2) {
                    $voyageData['conducteur_2_id'] = $conducteur2->id;
                }

                Voyage::create($voyageData);

                $conducteurInfo = $conducteur->prenom . ' ' . $conducteur->nom;
                if ($conducteur2) {
                    $conducteurInfo .= ' + ' . $conducteur2->prenom . ' ' . $conducteur2->nom;
                }

                $voyagesCrees[] = [
                    'ligne' => $ligne->nom,
                    'horaire' => $ligne->horaire_formate,
                    'bus' => $bus->immatriculation,
                    'conducteur' => $conducteurInfo,
                    'periode' => $periode,
                    'type' => $ligne->type === 'Retour' ? 'Nouveau Retour' : 'Nouvel Aller',
                ];

                $busAssignes[] = $bus->id;
                $conducteursAssignes[] = $conducteur->id;
                if ($conducteur2) {
                    $conducteursAssignes[] = $conducteur2->id;
                }
                $lignesProgrammees[] = $ligne->id;
            }
        }

        return [
            'voyagesCrees' => $voyagesCrees,
            'erreurs' => $erreurs,
        ];
    }

    /**
     * Simule la génération pour l'aperçu (sans créer les voyages)
     */
    private function simulerGenerationPourUnJour($date, $periodes, $lignesSelectionnees)
    {
        $dateHier = date('Y-m-d', strtotime($date . ' -1 day'));
        
        $propositions = [];
        $alertes = [];
        $lignesProposees = [];
        $busUtilises = [];

        // Voyages déjà existants
        $voyagesExistants = Voyage::whereDate('date_depart', $date)->get();
        $lignesDejaEnBase = $voyagesExistants->pluck('ligne_id')->toArray();

        // Récupérer les voyages d'hier
        $voyagesHierCount = Voyage::whereDate('date_depart', $dateHier)->count();

        // ÉTAPE 1: Propositions basées sur la veille
        if ($voyagesHierCount > 0) {
            $tousVoyagesHier = Voyage::with(['ligne', 'bus', 'conducteur', 'conducteur2'])
                ->whereDate('date_depart', $dateHier)
                ->whereIn('ligne_id', $lignesSelectionnees->pluck('id')->merge(
                    $lignesSelectionnees->map(fn($l) => $l->getLigneRetour()?->id)->filter()
                ))
                ->orderBy('date_depart')
                ->get();

            foreach ($tousVoyagesHier as $voyageHier) {
                $ligneHier = $voyageHier->ligne;
                
                if ($ligneHier->type === 'Aller') {
                    $ligneAujourdhui = $ligneHier->getLigneRetour();
                    $source = 'retour_suite_aller';
                } else {
                    $ligneAujourdhui = $ligneHier->getLigneAller();
                    $source = 'aller_suite_retour';
                }
                
                if (!$ligneAujourdhui) continue;
                
                $heure = (int) substr($ligneAujourdhui->horaire, 0, 2);
                $periode = ($heure >= 19) ? 'Nuit' : 'Jour';
                
                if (!in_array($periode, $periodes)) continue;
                if (in_array($ligneAujourdhui->id, $lignesDejaEnBase)) continue;
                if (in_array($ligneAujourdhui->id, $lignesProposees)) continue;
                
                // Inversion des conducteurs
                $conducteurPrincipal = $voyageHier->conducteur2 ?? $voyageHier->conducteur;
                $conducteurRelais = $voyageHier->conducteur2 ? $voyageHier->conducteur : null;
                
                $conducteurPrincipalOk = $conducteurPrincipal && 
                    !$conducteurPrincipal->estEnRepos($date) && 
                    !$conducteurPrincipal->estIndisponible($date);
                
                $conducteurRelaisOk = !$conducteurRelais || (
                    !$conducteurRelais->estEnRepos($date) && 
                    !$conducteurRelais->estIndisponible($date)
                );
                
                $bus = $voyageHier->bus->disponible ? $voyageHier->bus : null;
                
                $propositions[] = [
                    'ligne' => $ligneAujourdhui,
                    'periode' => $periode,
                    'bus' => $bus,
                    'conducteur' => $conducteurPrincipalOk ? $conducteurPrincipal : null,
                    'conducteur2' => ($conducteurRelaisOk && $conducteurRelais) ? $conducteurRelais : null,
                    'possible' => $bus && $conducteurPrincipalOk,
                    'source' => $source,
                ];
                
                $lignesProposees[] = $ligneAujourdhui->id;
                if ($bus) $busUtilises[] = $bus->id;
            }
        }

        // ÉTAPE 2: Lignes non couvertes
        foreach ($lignesSelectionnees as $ligne) {
            if (in_array($ligne->id, $lignesProposees) || in_array($ligne->id, $lignesDejaEnBase)) continue;
            
            $heure = (int) substr($ligne->horaire, 0, 2);
            $periode = ($heure >= 19) ? 'Nuit' : 'Jour';
            
            if (!in_array($periode, $periodes)) continue;
            
            $bus = Bus::where('disponible', true)
                ->whereNotIn('id', $busUtilises)
                ->where('ville_actuelle', $ligne->ville_depart)
                ->first();
            
            $conducteur = Conducteur::where('actif', true)
                ->where('ville_actuelle', $ligne->ville_depart)
                ->when($periode === 'Nuit', function($q) {
                    $q->where(function($sub) {
                        $sub->where('specialiste_nuit', true)->orWhere('remplacant_nuit', true);
                    });
                })
                ->first();
            
            $conducteur2 = null;
            if ($periode === 'Nuit' && $conducteur) {
                $conducteur2 = Conducteur::where('actif', true)
                    ->where('ville_actuelle', $ligne->ville_depart)
                    ->where('id', '!=', $conducteur->id)
                    ->first();
            }
            
            $propositions[] = [
                'ligne' => $ligne,
                'periode' => $periode,
                'bus' => $bus,
                'conducteur' => $conducteur,
                'conducteur2' => $conducteur2,
                'possible' => $bus && $conducteur,
                'source' => 'nouveau',
            ];
            
            $lignesProposees[] = $ligne->id;
            if ($bus) $busUtilises[] = $bus->id;
        }

        return [
            'propositions' => $propositions,
            'alertes' => $alertes,
            'voyagesExistants' => count($voyagesExistants),
        ];
    }

    /**
     * Compte les voyages planifiés pour une période donnée (AJAX)
     */
    public function countPlanifies(Request $request)
    {
        $request->validate([
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'periode' => 'nullable|in:Jour,Nuit,Les deux',
        ]);

        $query = Voyage::where('statut', 'Planifié')
            ->whereDate('date_depart', '>=', $request->date_debut)
            ->whereDate('date_depart', '<=', $request->date_fin);

        if ($request->periode && $request->periode !== 'Les deux') {
            $query->where('periode', $request->periode);
        }

        return response()->json([
            'count' => $query->count(),
            'date_debut' => \Carbon\Carbon::parse($request->date_debut)->format('d/m/Y'),
            'date_fin' => \Carbon\Carbon::parse($request->date_fin)->format('d/m/Y'),
        ]);
    }

    /**
     * Valide tous les voyages d'une période donnée
     */
    public function validateByPeriod(Request $request)
    {
        $request->validate([
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'periode' => 'nullable|in:Jour,Nuit,Les deux',
        ]);

        $query = Voyage::with(['ligne', 'conducteur', 'conducteur2', 'bus'])
            ->where('statut', 'Planifié')
            ->whereDate('date_depart', '>=', $request->date_debut)
            ->whereDate('date_depart', '<=', $request->date_fin);

        if ($request->periode && $request->periode !== 'Les deux') {
            $query->where('periode', $request->periode);
        }

        $voyages = $query->get();
        $count = 0;

        foreach ($voyages as $voyage) {
            $voyage->update(['statut' => 'Terminé']);
            $this->mettreAJourVilleConducteur($voyage);
            $count++;
        }

        $dateDebut = \Carbon\Carbon::parse($request->date_debut)->format('d/m/Y');
        $dateFin = \Carbon\Carbon::parse($request->date_fin)->format('d/m/Y');
        $periodeText = $request->periode && $request->periode !== 'Les deux' 
            ? " ({$request->periode})" 
            : '';

        return redirect()->route('voyages.historique')
            ->with('success', "$count voyage(s) validé(s) du $dateDebut au $dateFin$periodeText. Les positions des conducteurs et bus ont été mises à jour.");
    }

    /**
     * Compte les voyages terminés pour une période donnée (AJAX)
     */
    public function countTermines(Request $request)
    {
        $request->validate([
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'periode' => 'nullable|in:Jour,Nuit,Les deux',
        ]);

        $query = Voyage::where('statut', 'Terminé')
            ->whereDate('date_depart', '>=', $request->date_debut)
            ->whereDate('date_depart', '<=', $request->date_fin);

        if ($request->periode && $request->periode !== 'Les deux') {
            $query->where('periode', $request->periode);
        }

        return response()->json([
            'count' => $query->count(),
            'date_debut' => \Carbon\Carbon::parse($request->date_debut)->format('d/m/Y'),
            'date_fin' => \Carbon\Carbon::parse($request->date_fin)->format('d/m/Y'),
        ]);
    }

    /**
     * Invalide tous les voyages d'une période donnée (remet en Planifié)
     */
    public function invalidateByPeriod(Request $request)
    {
        $request->validate([
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'periode' => 'nullable|in:Jour,Nuit,Les deux',
        ]);

        $query = Voyage::where('statut', 'Terminé')
            ->whereDate('date_depart', '>=', $request->date_debut)
            ->whereDate('date_depart', '<=', $request->date_fin);

        if ($request->periode && $request->periode !== 'Les deux') {
            $query->where('periode', $request->periode);
        }

        $count = $query->update(['statut' => 'Planifié']);

        $dateDebut = \Carbon\Carbon::parse($request->date_debut)->format('d/m/Y');
        $dateFin = \Carbon\Carbon::parse($request->date_fin)->format('d/m/Y');
        $periodeText = $request->periode && $request->periode !== 'Les deux' 
            ? " ({$request->periode})" 
            : '';

        return redirect()->route('voyages.historique')
            ->with('success', "$count voyage(s) invalidé(s) du $dateDebut au $dateFin$periodeText. Les voyages ont été remis au statut Planifié.");
    }

    /**
     * Télécharge la programmation journalière au format PDF
     */
    public function downloadPDF(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        
        // Récupérer tous les voyages du jour avec leurs relations
        $voyages = Voyage::with(['ligne', 'bus', 'bus.typeBus', 'conducteur', 'conducteur2'])
            ->whereDate('date_depart', $date)
            ->orderBy('date_depart')
            ->get();
        
        // Séparer les allers et retours, triés par heure
        $allers = $voyages->filter(function($v) {
            return $v->ligne && $v->ligne->type === 'Aller';
        })->sortBy(function($v) {
            return $v->ligne->horaire;
        })->values();
        
        $retours = $voyages->filter(function($v) {
            return $v->ligne && $v->ligne->type === 'Retour';
        })->sortBy(function($v) {
            return $v->ligne->horaire;
        })->values();
        
        $dateFormatted = \Carbon\Carbon::parse($date)->format('d/m/Y');
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('voyages.programmation-pdf', compact(
            'allers', 
            'retours', 
            'date',
            'dateFormatted'
        ));
        
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->download("programmation-{$date}.pdf");
    }
}
