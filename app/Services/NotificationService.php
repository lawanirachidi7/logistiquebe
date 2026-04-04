<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Conducteur;
use App\Models\ReposConducteur;
use App\Models\User;
use Carbon\Carbon;

class NotificationService
{
    protected FatigueService $fatigueService;

    public function __construct(FatigueService $fatigueService)
    {
        $this->fatigueService = $fatigueService;
    }

    /**
     * Récupère les notifications pour un utilisateur
     */
    public function getNotificationsPourUtilisateur(?int $userId = null, int $limite = 20): array
    {
        $userId = $userId ?? auth()->id();

        $notifications = Notification::pourUtilisateur($userId)
            ->nonExpirees()
            ->orderByDesc('created_at')
            ->take($limite)
            ->get();

        $nonLues = Notification::pourUtilisateur($userId)
            ->nonExpirees()
            ->nonLues()
            ->count();

        return [
            'notifications' => $notifications,
            'non_lues' => $nonLues,
            'critiques' => $notifications->where('niveau', Notification::NIVEAU_DANGER)->count(),
        ];
    }

    /**
     * Marque toutes les notifications comme lues pour un utilisateur
     */
    public function marquerToutesCommeLues(?int $userId = null): int
    {
        $userId = $userId ?? auth()->id();

        return Notification::pourUtilisateur($userId)
            ->nonLues()
            ->update([
                'lue' => true,
                'lue_le' => now(),
            ]);
    }

    /**
     * Génère les notifications de fatigue pour tous les conducteurs
     */
    public function genererNotificationsFatigue(): array
    {
        $conducteurs = Conducteur::where('actif', true)->get();
        $notificationsCreees = [
            'critiques' => 0,
            'elevees' => 0,
        ];

        foreach ($conducteurs as $conducteur) {
            // Ignorer les conducteurs en repos
            if ($conducteur->estEnRepos()) {
                continue;
            }

            $analyse = $this->fatigueService->calculerScoreFatigue($conducteur);
            $score = $analyse['score'];
            $niveau = $analyse['niveau'];

            // Vérifier s'il n'y a pas déjà une notification récente pour ce conducteur
            $notificationRecente = Notification::whereIn('type', [
                    Notification::TYPE_FATIGUE_CRITIQUE,
                    Notification::TYPE_FATIGUE_ELEVEE
                ])
                ->where('contexte->conducteur_id', $conducteur->id)
                ->where('created_at', '>=', now()->subHours(24))
                ->exists();

            if ($notificationRecente) {
                continue;
            }

            // Créer les notifications selon le niveau
            if ($niveau === 'rouge') {
                Notification::creerFatigueCritique($conducteur, $score);
                $notificationsCreees['critiques']++;
            } elseif ($niveau === 'orange') {
                Notification::creerFatigueElevee($conducteur, $score);
                $notificationsCreees['elevees']++;
            }
        }

        return $notificationsCreees;
    }

    /**
     * Notifie d'un repos suggéré/créé automatiquement
     */
    public function notifierReposSuggere(ReposConducteur $repos): Notification
    {
        return Notification::creerReposSuggere($repos);
    }

    /**
     * Notifie d'un repos validé
     */
    public function notifierReposValide(ReposConducteur $repos): Notification
    {
        return Notification::creerReposValide($repos);
    }

    /**
     * Crée une notification d'information
     */
    public function notifierInfo(string $titre, string $message, ?string $lien = null, ?int $userId = null): Notification
    {
        return Notification::creerInfo($titre, $message, $lien, $userId);
    }

    /**
     * Créer une notification pour tous les admins/opérateurs
     */
    public function notifierAdmins(string $titre, string $message, string $type = Notification::TYPE_INFO, ?string $lien = null): int
    {
        $users = User::whereIn('role', ['admin', 'operateur'])
            ->where('actif', true)
            ->get();

        $count = 0;
        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => $type,
                'niveau' => Notification::getTypesConfig()[$type]['niveau'] ?? Notification::NIVEAU_INFO,
                'titre' => $titre,
                'message' => $message,
                'icone' => Notification::getTypesConfig()[$type]['icone'] ?? 'fa-bell',
                'lien' => $lien,
            ]);
            $count++;
        }

        return $count;
    }

    /**
     * Résumé quotidien des notifications
     */
    public function getResumé(): array
    {
        $aujourdhui = Carbon::today();

        return [
            'total_non_lues' => Notification::nonLues()->count(),
            'critiques_non_lues' => Notification::nonLues()->parNiveau(Notification::NIVEAU_DANGER)->count(),
            'creees_aujourdhui' => Notification::whereDate('created_at', $aujourdhui)->count(),
            'repos_en_attente' => ReposConducteur::enAttente()->count(),
            'conducteurs_fatigues' => Conducteur::where('actif', true)
                ->get()
                ->filter(fn($c) => !$c->estEnRepos() && in_array($c->getNiveauFatigue(), ['orange', 'rouge']))
                ->count(),
        ];
    }

    /**
     * Nettoie les anciennes notifications
     */
    public function nettoyerAnciennes(int $jours = 30): int
    {
        return Notification::nettoyerAnciennes($jours);
    }

    /**
     * Crée une notification de voyage créé
     */
    public function notifierVoyageCree(array $donnees): Notification
    {
        return Notification::create([
            'user_id' => null,
            'type' => Notification::TYPE_VOYAGE_CREE,
            'niveau' => Notification::NIVEAU_INFO,
            'titre' => 'Nouveau voyage créé',
            'message' => "Voyage {$donnees['ligne']} programmé le {$donnees['date']} ({$donnees['periode']}).",
            'icone' => 'fa-bus',
            'lien' => route('voyages.index'),
            'contexte' => $donnees,
        ]);
    }
}
