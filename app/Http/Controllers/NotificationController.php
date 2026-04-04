<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Liste des notifications (page complète)
     */
    public function index(Request $request)
    {
        $query = Notification::pourUtilisateur()
            ->nonExpirees()
            ->orderByDesc('created_at');

        // Filtres
        if ($request->has('type') && $request->type) {
            $query->deType($request->type);
        }

        if ($request->has('niveau') && $request->niveau) {
            $query->parNiveau($request->niveau);
        }

        if ($request->has('lue')) {
            if ($request->lue === 'non') {
                $query->nonLues();
            } elseif ($request->lue === 'oui') {
                $query->lues();
            }
        }

        $notifications = $query->paginate(20);
        $nonLues = Notification::pourUtilisateur()->nonExpirees()->nonLues()->count();
        $types = Notification::getTypesConfig();

        return view('notifications.index', compact('notifications', 'nonLues', 'types'));
    }

    /**
     * API: Liste des notifications pour topbar (AJAX)
     */
    public function getNotifications(Request $request): JsonResponse
    {
        $limite = $request->get('limite', 10);
        $data = $this->notificationService->getNotificationsPourUtilisateur(auth()->id(), $limite);

        return response()->json([
            'success' => true,
            'notifications' => $data['notifications']->map(function ($n) {
                return [
                    'id' => $n->id,
                    'type' => $n->type,
                    'niveau' => $n->niveau,
                    'titre' => $n->titre,
                    'message' => $n->message,
                    'icone' => $n->icone_complet,
                    'couleur' => $n->couleur,
                    'badge_class' => $n->badge_class,
                    'lien' => $n->lien,
                    'lue' => $n->lue,
                    'temps_ecoule' => $n->temps_ecoule,
                    'created_at' => $n->created_at->format('d/m/Y H:i'),
                ];
            }),
            'non_lues' => $data['non_lues'],
            'critiques' => $data['critiques'],
        ]);
    }

    /**
     * API: Marquer une notification comme lue
     */
    public function marquerLue(Notification $notification): JsonResponse
    {
        // Vérifier que l'utilisateur peut accéder à cette notification
        if ($notification->user_id !== null && $notification->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        $notification->marquerCommeLue();

        return response()->json([
            'success' => true,
            'message' => 'Notification marquée comme lue',
        ]);
    }

    /**
     * API: Marquer toutes comme lues
     */
    public function marquerToutesLues(): JsonResponse
    {
        $count = $this->notificationService->marquerToutesCommeLues();

        return response()->json([
            'success' => true,
            'message' => "$count notification(s) marquée(s) comme lue(s)",
            'count' => $count,
        ]);
    }

    /**
     * API: Supprimer une notification
     */
    public function destroy(Notification $notification): JsonResponse
    {
        // Vérifier l'accès
        if ($notification->user_id !== null && $notification->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification supprimée',
        ]);
    }

    /**
     * API: Résumé pour dashboard
     */
    public function resume(): JsonResponse
    {
        $resume = $this->notificationService->getResumé();

        return response()->json([
            'success' => true,
            'data' => $resume,
        ]);
    }

    /**
     * Générer les notifications de fatigue (appelé par CRON ou manuellement)
     */
    public function genererNotificationsFatigue(): JsonResponse
    {
        $result = $this->notificationService->genererNotificationsFatigue();

        return response()->json([
            'success' => true,
            'message' => 'Notifications de fatigue générées',
            'critiques' => $result['critiques'],
            'elevees' => $result['elevees'],
        ]);
    }

    /**
     * Nettoyer les anciennes notifications
     */
    public function nettoyer(Request $request): JsonResponse
    {
        $jours = $request->get('jours', 30);
        $count = $this->notificationService->nettoyerAnciennes($jours);

        return response()->json([
            'success' => true,
            'message' => "$count ancienne(s) notification(s) supprimée(s)",
        ]);
    }

    /**
     * Rediriger vers le lien d'une notification et la marquer comme lue
     */
    public function action(Notification $notification)
    {
        // Marquer comme lue
        $notification->marquerCommeLue();

        // Rediriger vers le lien si disponible
        if ($notification->lien) {
            return redirect($notification->lien);
        }

        return redirect()->route('notifications.index')
            ->with('info', 'Notification consultée');
    }
}
