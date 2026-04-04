<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class CritereProgrammation extends Model
{
    protected $table = 'criteres_programmation';

    protected $fillable = [
        'cle',
        'libelle',
        'description',
        'categorie',
        'type',
        'valeur',
        'valeur_defaut',
        'actif',
        'ordre',
    ];

    protected $casts = [
        'actif' => 'boolean',
        'ordre' => 'integer',
    ];

    /**
     * Catégories disponibles
     */
    const CATEGORIE_HORAIRES = 'horaires';
    const CATEGORIE_CONDUCTEURS = 'conducteurs';
    const CATEGORIE_BUS = 'bus';
    const CATEGORIE_REGLES = 'regles';
    const CATEGORIE_VALIDATION = 'validation';

    /**
     * Types de valeurs
     */
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_INTEGER = 'integer';
    const TYPE_STRING = 'string';
    const TYPE_TIME = 'time';

    /**
     * Obtenir la valeur d'un critère par sa clé
     */
    public static function get(string $cle, $default = null)
    {
        $critere = Cache::remember("critere.{$cle}", 3600, function () use ($cle) {
            return self::where('cle', $cle)->where('actif', true)->first();
        });

        if (!$critere) {
            return $default;
        }

        return self::castValue($critere->valeur, $critere->type);
    }

    /**
     * Définir la valeur d'un critère
     */
    public static function set(string $cle, $valeur): bool
    {
        $critere = self::where('cle', $cle)->first();
        
        if (!$critere) {
            return false;
        }

        $critere->update(['valeur' => (string) $valeur]);
        Cache::forget("critere.{$cle}");
        
        return true;
    }

    /**
     * Réinitialiser un critère à sa valeur par défaut
     */
    public function resetToDefault(): void
    {
        $this->update(['valeur' => $this->valeur_defaut]);
        Cache::forget("critere.{$this->cle}");
    }

    /**
     * Réinitialiser tous les critères
     */
    public static function resetAll(): void
    {
        foreach (self::all() as $critere) {
            $critere->resetToDefault();
        }
        Cache::flush();
    }

    /**
     * Obtenir tous les critères par catégorie
     */
    public static function parCategorie(): array
    {
        return self::orderBy('categorie')
            ->orderBy('ordre')
            ->get()
            ->groupBy('categorie')
            ->toArray();
    }

    /**
     * Cast la valeur selon le type
     */
    public static function castValue($valeur, string $type)
    {
        return match ($type) {
            self::TYPE_BOOLEAN => filter_var($valeur, FILTER_VALIDATE_BOOLEAN),
            self::TYPE_INTEGER => (int) $valeur,
            self::TYPE_TIME => $valeur,
            default => $valeur,
        };
    }

    /**
     * Obtenir la valeur castée
     */
    public function getValeurCasteeAttribute()
    {
        return self::castValue($this->valeur, $this->type);
    }

    /**
     * Labels des catégories
     */
    public static function getCategoriesLabels(): array
    {
        return [
            self::CATEGORIE_HORAIRES => 'Horaires & Périodes',
            self::CATEGORIE_CONDUCTEURS => 'Gestion des Conducteurs',
            self::CATEGORIE_BUS => 'Gestion des Bus',
            self::CATEGORIE_REGLES => 'Règles de Programmation',
            self::CATEGORIE_VALIDATION => 'Validation & Contrôles',
        ];
    }

    /**
     * Icônes des catégories
     */
    public static function getCategoriesIcons(): array
    {
        return [
            self::CATEGORIE_HORAIRES => 'fa-clock',
            self::CATEGORIE_CONDUCTEURS => 'fa-id-card',
            self::CATEGORIE_BUS => 'fa-bus',
            self::CATEGORIE_REGLES => 'fa-cogs',
            self::CATEGORIE_VALIDATION => 'fa-check-circle',
        ];
    }

    /**
     * Initialiser les critères par défaut
     */
    public static function initDefaults(): void
    {
        $criteres = self::getDefaultCriteres();
        
        foreach ($criteres as $critere) {
            self::firstOrCreate(
                ['cle' => $critere['cle']],
                $critere
            );
        }
    }

    /**
     * Liste des critères par défaut
     */
    public static function getDefaultCriteres(): array
    {
        return [
            // === HORAIRES ===
            [
                'cle' => 'heure_debut_nuit',
                'libelle' => 'Heure de début de la période nuit',
                'description' => 'Les voyages partant après cette heure sont considérés comme voyages de nuit',
                'categorie' => self::CATEGORIE_HORAIRES,
                'type' => self::TYPE_INTEGER,
                'valeur' => '19',
                'valeur_defaut' => '19',
                'actif' => true,
                'ordre' => 1,
            ],
            [
                'cle' => 'heure_fin_nuit',
                'libelle' => 'Heure de fin de la période nuit',
                'description' => 'Les voyages partant avant cette heure (le matin) sont considérés comme voyages de nuit',
                'categorie' => self::CATEGORIE_HORAIRES,
                'type' => self::TYPE_INTEGER,
                'valeur' => '6',
                'valeur_defaut' => '6',
                'actif' => true,
                'ordre' => 2,
            ],

            // === CONDUCTEURS ===
            [
                'cle' => 'conducteur_2_obligatoire_nuit',
                'libelle' => '2ème conducteur obligatoire la nuit',
                'description' => 'Exiger un deuxième conducteur pour les voyages de nuit',
                'categorie' => self::CATEGORIE_CONDUCTEURS,
                'type' => self::TYPE_BOOLEAN,
                'valeur' => '1',
                'valeur_defaut' => '1',
                'actif' => true,
                'ordre' => 1,
            ],
            [
                'cle' => 'priorite_specialiste_nuit',
                'libelle' => 'Priorité aux spécialistes de nuit',
                'description' => 'Donner la priorité aux conducteurs spécialistes nuit pour les voyages de nuit',
                'categorie' => self::CATEGORIE_CONDUCTEURS,
                'type' => self::TYPE_BOOLEAN,
                'valeur' => '1',
                'valeur_defaut' => '1',
                'actif' => true,
                'ordre' => 2,
            ],
            [
                'cle' => 'remplacant_nuit_autorise',
                'libelle' => 'Autoriser les remplaçants de nuit',
                'description' => 'Permettre aux conducteurs remplaçants de nuit de conduire la nuit si aucun spécialiste disponible',
                'categorie' => self::CATEGORIE_CONDUCTEURS,
                'type' => self::TYPE_BOOLEAN,
                'valeur' => '1',
                'valeur_defaut' => '1',
                'actif' => true,
                'ordre' => 3,
            ],
            [
                'cle' => 'inversion_conducteurs_retour',
                'libelle' => 'Inverser les conducteurs au retour',
                'description' => 'Le conducteur relais devient principal et vice versa au retour',
                'categorie' => self::CATEGORIE_CONDUCTEURS,
                'type' => self::TYPE_BOOLEAN,
                'valeur' => '1',
                'valeur_defaut' => '1',
                'actif' => true,
                'ordre' => 4,
            ],
            [
                'cle' => 'verifier_repos_conducteur',
                'libelle' => 'Vérifier les repos conducteurs',
                'description' => 'Exclure les conducteurs en repos ou indisponibles de la programmation',
                'categorie' => self::CATEGORIE_CONDUCTEURS,
                'type' => self::TYPE_BOOLEAN,
                'valeur' => '1',
                'valeur_defaut' => '1',
                'actif' => true,
                'ordre' => 5,
            ],
            [
                'cle' => 'verifier_position_conducteur',
                'libelle' => 'Vérifier position du conducteur',
                'description' => 'Le conducteur doit être à la ville de départ de la ligne',
                'categorie' => self::CATEGORIE_CONDUCTEURS,
                'type' => self::TYPE_BOOLEAN,
                'valeur' => '1',
                'valeur_defaut' => '1',
                'actif' => true,
                'ordre' => 6,
            ],
            [
                'cle' => 'max_voyages_conducteur_jour',
                'libelle' => 'Max voyages par conducteur/jour',
                'description' => 'Nombre maximum de voyages qu\'un conducteur peut faire par jour (0 = illimité)',
                'categorie' => self::CATEGORIE_CONDUCTEURS,
                'type' => self::TYPE_INTEGER,
                'valeur' => '2',
                'valeur_defaut' => '2',
                'actif' => true,
                'ordre' => 7,
            ],

            // === BUS ===
            [
                'cle' => 'verifier_disponibilite_bus',
                'libelle' => 'Vérifier disponibilité du bus',
                'description' => 'Ne programmer que les bus marqués comme disponibles',
                'categorie' => self::CATEGORIE_BUS,
                'type' => self::TYPE_BOOLEAN,
                'valeur' => '1',
                'valeur_defaut' => '1',
                'actif' => true,
                'ordre' => 1,
            ],
            [
                'cle' => 'verifier_position_bus',
                'libelle' => 'Vérifier position du bus',
                'description' => 'Le bus doit être à la ville de départ de la ligne',
                'categorie' => self::CATEGORIE_BUS,
                'type' => self::TYPE_BOOLEAN,
                'valeur' => '1',
                'valeur_defaut' => '1',
                'actif' => true,
                'ordre' => 2,
            ],
            [
                'cle' => 'autoriser_bus_autre_ville',
                'libelle' => 'Autoriser bus d\'une autre ville',
                'description' => 'Si aucun bus disponible à la ville de départ, autoriser un bus d\'une autre ville',
                'categorie' => self::CATEGORIE_BUS,
                'type' => self::TYPE_BOOLEAN,
                'valeur' => '1',
                'valeur_defaut' => '1',
                'actif' => true,
                'ordre' => 3,
            ],
            [
                'cle' => 'verifier_type_bus_ligne_nord',
                'libelle' => 'Vérifier type bus pour ligne Nord',
                'description' => 'Les lignes Nord nécessitent des bus adaptés (ligne_nord = true)',
                'categorie' => self::CATEGORIE_BUS,
                'type' => self::TYPE_BOOLEAN,
                'valeur' => '1',
                'valeur_defaut' => '1',
                'actif' => true,
                'ordre' => 4,
            ],

            // === REGLES DE PROGRAMMATION ===
            [
                'cle' => 'continuite_aller_retour',
                'libelle' => 'Continuité aller/retour',
                'description' => 'Les bus/conducteurs ayant fait un aller la veille font le retour le lendemain',
                'categorie' => self::CATEGORIE_REGLES,
                'type' => self::TYPE_BOOLEAN,
                'valeur' => '1',
                'valeur_defaut' => '1',
                'actif' => true,
                'ordre' => 1,
            ],
            [
                'cle' => 'jours_veille_programmation',
                'libelle' => 'Jours de veille pour continuité',
                'description' => 'Nombre de jours en arrière à vérifier pour la continuité (généralement 1)',
                'categorie' => self::CATEGORIE_REGLES,
                'type' => self::TYPE_INTEGER,
                'valeur' => '1',
                'valeur_defaut' => '1',
                'actif' => true,
                'ordre' => 2,
            ],
            [
                'cle' => 'eviter_doublons_ligne',
                'libelle' => 'Éviter les doublons par ligne',
                'description' => 'Ne pas programmer deux voyages sur la même ligne/période/date',
                'categorie' => self::CATEGORIE_REGLES,
                'type' => self::TYPE_BOOLEAN,
                'valeur' => '1',
                'valeur_defaut' => '1',
                'actif' => true,
                'ordre' => 3,
            ],
            [
                'cle' => 'statuts_consideres_veille',
                'libelle' => 'Statuts considérés pour la veille',
                'description' => 'Statuts des voyages de la veille à considérer (Planifié,En cours,Terminé)',
                'categorie' => self::CATEGORIE_REGLES,
                'type' => self::TYPE_STRING,
                'valeur' => 'Planifié,En cours,Terminé',
                'valeur_defaut' => 'Planifié,En cours,Terminé',
                'actif' => true,
                'ordre' => 4,
            ],

            // === VALIDATION ===
            [
                'cle' => 'maj_position_apres_validation',
                'libelle' => 'Mise à jour position après validation',
                'description' => 'Mettre à jour la ville_actuelle du conducteur et bus après validation',
                'categorie' => self::CATEGORIE_VALIDATION,
                'type' => self::TYPE_BOOLEAN,
                'valeur' => '1',
                'valeur_defaut' => '1',
                'actif' => true,
                'ordre' => 1,
            ],
            [
                'cle' => 'apercu_avant_generation',
                'libelle' => 'Aperçu avant génération',
                'description' => 'Afficher un aperçu modifiable avant de créer les voyages',
                'categorie' => self::CATEGORIE_VALIDATION,
                'type' => self::TYPE_BOOLEAN,
                'valeur' => '1',
                'valeur_defaut' => '1',
                'actif' => true,
                'ordre' => 2,
            ],
        ];
    }
}
