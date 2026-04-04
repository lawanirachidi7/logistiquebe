<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'niveau',
        'titre',
        'message',
        'icone',
        'lien',
        'lien_texte',
        'contexte',
        'lue',
        'lue_le',
        'expire_le',
    ];

    protected $casts = [
        'lue' => 'boolean',
        'lue_le' => 'datetime',
        'expire_le' => 'datetime',
        'contexte' => 'array',
    ];

    /**
     * Types de notifications
     */
    const TYPE_FATIGUE_CRITIQUE = 'fatigue_critique';
    const TYPE_FATIGUE_ELEVEE = 'fatigue_elevee';
    const TYPE_REPOS_SUGGERE = 'repos_suggere';
    const TYPE_REPOS_A_VALIDER = 'repos_a_valider';
    const TYPE_REPOS_VALIDE = 'repos_valide';
    const TYPE_VOYAGE_CREE = 'voyage_cree';
    const TYPE_VOYAGE_VALIDE = 'voyage_valide';
    const TYPE_CONDUCTEUR_INDISPONIBLE = 'conducteur_indisponible';
    const TYPE_ALERTE_SYSTEME = 'alerte_systeme';
    const TYPE_INFO = 'info';

    /**
     * Niveaux d'importance
     */
    const NIVEAU_INFO = 'info';
    const NIVEAU_WARNING = 'warning';
    const NIVEAU_DANGER = 'danger';
    const NIVEAU_SUCCESS = 'success';

    /**
     * Configuration des types
     */
    public static function getTypesConfig(): array
    {
        return [
            self::TYPE_FATIGUE_CRITIQUE => [
                'label' => 'Fatigue critique',
                'icone' => 'fa-exclamation-triangle',
                'niveau' => self::NIVEAU_DANGER,
                'couleur' => '#dc3545',
            ],
            self::TYPE_FATIGUE_ELEVEE => [
                'label' => 'Fatigue élevée',
                'icone' => 'fa-exclamation-circle',
                'niveau' => self::NIVEAU_WARNING,
                'couleur' => '#fd7e14',
            ],
            self::TYPE_REPOS_SUGGERE => [
                'label' => 'Repos suggéré',
                'icone' => 'fa-bed',
                'niveau' => self::NIVEAU_INFO,
                'couleur' => '#17a2b8',
            ],
            self::TYPE_REPOS_A_VALIDER => [
                'label' => 'Repos à valider',
                'icone' => 'fa-clock',
                'niveau' => self::NIVEAU_WARNING,
                'couleur' => '#ffc107',
            ],
            self::TYPE_REPOS_VALIDE => [
                'label' => 'Repos validé',
                'icone' => 'fa-check-circle',
                'niveau' => self::NIVEAU_SUCCESS,
                'couleur' => '#28a745',
            ],
            self::TYPE_VOYAGE_CREE => [
                'label' => 'Nouveau voyage',
                'icone' => 'fa-bus',
                'niveau' => self::NIVEAU_INFO,
                'couleur' => '#007bff',
            ],
            self::TYPE_VOYAGE_VALIDE => [
                'label' => 'Voyage validé',
                'icone' => 'fa-check',
                'niveau' => self::NIVEAU_SUCCESS,
                'couleur' => '#28a745',
            ],
            self::TYPE_CONDUCTEUR_INDISPONIBLE => [
                'label' => 'Conducteur indisponible',
                'icone' => 'fa-user-times',
                'niveau' => self::NIVEAU_WARNING,
                'couleur' => '#fd7e14',
            ],
            self::TYPE_ALERTE_SYSTEME => [
                'label' => 'Alerte système',
                'icone' => 'fa-cog',
                'niveau' => self::NIVEAU_DANGER,
                'couleur' => '#dc3545',
            ],
            self::TYPE_INFO => [
                'label' => 'Information',
                'icone' => 'fa-info-circle',
                'niveau' => self::NIVEAU_INFO,
                'couleur' => '#6c757d',
            ],
        ];
    }

    /**
     * Relations
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scopes
     */
    public function scopeNonLues($query)
    {
        return $query->where('lue', false);
    }

    public function scopeLues($query)
    {
        return $query->where('lue', true);
    }

    public function scopeRecentes($query, int $jours = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($jours));
    }

    public function scopeNonExpirees($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expire_le')
              ->orWhere('expire_le', '>', now());
        });
    }

    public function scopePourUtilisateur($query, ?int $userId = null)
    {
        $userId = $userId ?? auth()->id();
        return $query->where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
              ->orWhereNull('user_id'); // Notifications globales
        });
    }

    public function scopeDeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeParNiveau($query, string $niveau)
    {
        return $query->where('niveau', $niveau);
    }

    public function scopeCritiques($query)
    {
        return $query->whereIn('niveau', [self::NIVEAU_DANGER, self::NIVEAU_WARNING]);
    }

    /**
     * Accesseurs
     */
    public function getConfigAttribute(): array
    {
        return self::getTypesConfig()[$this->type] ?? self::getTypesConfig()[self::TYPE_INFO];
    }

    public function getCouleurAttribute(): string
    {
        return $this->config['couleur'];
    }

    public function getIconeCompletAttribute(): string
    {
        return 'fas ' . ($this->icone ?: $this->config['icone']);
    }

    public function getBadgeClassAttribute(): string
    {
        return match($this->niveau) {
            self::NIVEAU_DANGER => 'bg-danger',
            self::NIVEAU_WARNING => 'bg-warning text-dark',
            self::NIVEAU_SUCCESS => 'bg-success',
            default => 'bg-info',
        };
    }

    public function getTempsEcouleAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    public function getEstExpireAttribute(): bool
    {
        return $this->expire_le && $this->expire_le->isPast();
    }

    /**
     * Actions
     */
    public function marquerCommeLue(): void
    {
        if (!$this->lue) {
            $this->update([
                'lue' => true,
                'lue_le' => now(),
            ]);
        }
    }

    public function marquerCommeNonLue(): void
    {
        $this->update([
            'lue' => false,
            'lue_le' => null,
        ]);
    }

    /**
     * Créateurs statiques
     */
    public static function creerFatigueCritique(Conducteur $conducteur, int $score, ?int $userId = null): self
    {
        return self::create([
            'user_id' => $userId,
            'type' => self::TYPE_FATIGUE_CRITIQUE,
            'niveau' => self::NIVEAU_DANGER,
            'titre' => 'Fatigue critique détectée',
            'message' => "{$conducteur->prenom} {$conducteur->nom} a atteint un niveau de fatigue critique ({$score}%). Repos obligatoire!",
            'icone' => 'fa-exclamation-triangle',
            'lien' => route('repos.detail-conducteur', $conducteur->id),
            'lien_texte' => 'Voir les détails',
            'contexte' => [
                'conducteur_id' => $conducteur->id,
                'conducteur_nom' => "{$conducteur->prenom} {$conducteur->nom}",
                'score' => $score,
            ],
        ]);
    }

    public static function creerFatigueElevee(Conducteur $conducteur, int $score, ?int $userId = null): self
    {
        return self::create([
            'user_id' => $userId,
            'type' => self::TYPE_FATIGUE_ELEVEE,
            'niveau' => self::NIVEAU_WARNING,
            'titre' => 'Fatigue élevée',
            'message' => "{$conducteur->prenom} {$conducteur->nom} présente un niveau de fatigue élevé ({$score}%). Planifier un repos.",
            'icone' => 'fa-exclamation-circle',
            'lien' => route('repos.detail-conducteur', $conducteur->id),
            'lien_texte' => 'Voir les détails',
            'contexte' => [
                'conducteur_id' => $conducteur->id,
                'conducteur_nom' => "{$conducteur->prenom} {$conducteur->nom}",
                'score' => $score,
            ],
        ]);
    }

    public static function creerReposSuggere(ReposConducteur $repos): self
    {
        $conducteur = $repos->conducteur;
        return self::create([
            'user_id' => null, // Notification globale
            'type' => self::TYPE_REPOS_SUGGERE,
            'niveau' => self::NIVEAU_INFO,
            'titre' => 'Repos automatique créé',
            'message' => "Un repos a été suggéré pour {$conducteur->prenom} {$conducteur->nom} du {$repos->date_debut->format('d/m')} au {$repos->date_fin->format('d/m/Y')}.",
            'icone' => 'fa-bed',
            'lien' => route('repos.en-attente'),
            'lien_texte' => 'Valider le repos',
            'contexte' => [
                'repos_id' => $repos->id,
                'conducteur_id' => $conducteur->id,
                'conducteur_nom' => "{$conducteur->prenom} {$conducteur->nom}",
                'date_debut' => $repos->date_debut->format('Y-m-d'),
                'date_fin' => $repos->date_fin->format('Y-m-d'),
            ],
        ]);
    }

    public static function creerReposValide(ReposConducteur $repos): self
    {
        $conducteur = $repos->conducteur;
        return self::create([
            'user_id' => null,
            'type' => self::TYPE_REPOS_VALIDE,
            'niveau' => self::NIVEAU_SUCCESS,
            'titre' => 'Repos validé',
            'message' => "Le repos de {$conducteur->prenom} {$conducteur->nom} a été validé ({$repos->date_debut->format('d/m')} - {$repos->date_fin->format('d/m/Y')}).",
            'icone' => 'fa-check-circle',
            'lien' => route('repos.index'),
            'contexte' => [
                'repos_id' => $repos->id,
                'conducteur_id' => $conducteur->id,
            ],
        ]);
    }

    public static function creerInfo(string $titre, string $message, ?string $lien = null, ?int $userId = null): self
    {
        return self::create([
            'user_id' => $userId,
            'type' => self::TYPE_INFO,
            'niveau' => self::NIVEAU_INFO,
            'titre' => $titre,
            'message' => $message,
            'icone' => 'fa-info-circle',
            'lien' => $lien,
        ]);
    }

    /**
     * Supprimer les vieilles notifications
     */
    public static function nettoyerAnciennes(int $jours = 30): int
    {
        return self::where('created_at', '<', now()->subDays($jours))
            ->where('lue', true)
            ->delete();
    }
}
