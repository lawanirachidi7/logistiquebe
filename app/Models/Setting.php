<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key', 'value', 'type', 'group', 'label', 'description'
    ];

    /**
     * Obtenir une valeur de paramètre
     */
    public static function get(string $key, $default = null)
    {
        $setting = Cache::remember("setting.{$key}", 3600, function () use ($key) {
            return self::where('key', $key)->first();
        });

        if (!$setting) {
            return $default;
        }

        return self::castValue($setting->value, $setting->type);
    }

    /**
     * Définir une valeur de paramètre
     */
    public static function set(string $key, $value, string $type = 'string', string $group = 'general', ?string $label = null)
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_array($value) ? json_encode($value) : $value,
                'type' => $type,
                'group' => $group,
                'label' => $label ?? $key,
            ]
        );

        Cache::forget("setting.{$key}");

        return $setting;
    }

    /**
     * Obtenir tous les paramètres d'un groupe
     */
    public static function getGroup(string $group)
    {
        return self::where('group', $group)->get()->pluck('value', 'key')->toArray();
    }

    /**
     * Cast la valeur selon son type
     */
    protected static function castValue($value, string $type)
    {
        return match($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Obtenir les paramètres par défaut de l'application
     */
    public static function getDefaults(): array
    {
        return [
            // Informations de l'entreprise
            ['key' => 'company_name', 'value' => 'BAOBAB Express', 'type' => 'string', 'group' => 'company', 'label' => 'Nom de l\'entreprise'],
            ['key' => 'company_slogan', 'value' => 'Votre partenaire de voyage', 'type' => 'string', 'group' => 'company', 'label' => 'Slogan'],
            ['key' => 'company_phone', 'value' => '+229 00 00 00 00', 'type' => 'string', 'group' => 'company', 'label' => 'Téléphone'],
            ['key' => 'company_email', 'value' => 'contact@baobabexpress.org', 'type' => 'string', 'group' => 'company', 'label' => 'Email'],
            ['key' => 'company_address', 'value' => 'Parakou, Bénin', 'type' => 'string', 'group' => 'company', 'label' => 'Adresse'],
            ['key' => 'company_website', 'value' => 'www.baobabexpress.org', 'type' => 'string', 'group' => 'company', 'label' => 'Site web'],
            
            // Paramètres d'affichage
            ['key' => 'items_per_page', 'value' => '25', 'type' => 'integer', 'group' => 'display', 'label' => 'Éléments par page'],
            ['key' => 'date_format', 'value' => 'd/m/Y', 'type' => 'string', 'group' => 'display', 'label' => 'Format de date'],
            ['key' => 'time_format', 'value' => 'H:i', 'type' => 'string', 'group' => 'display', 'label' => 'Format d\'heure'],
            
            // Paramètres de programmation
            ['key' => 'night_start_hour', 'value' => '19', 'type' => 'integer', 'group' => 'programming', 'label' => 'Heure début nuit'],
            ['key' => 'night_end_hour', 'value' => '6', 'type' => 'integer', 'group' => 'programming', 'label' => 'Heure fin nuit'],
            ['key' => 'require_second_driver_night', 'value' => '1', 'type' => 'boolean', 'group' => 'programming', 'label' => '2ème conducteur obligatoire la nuit'],
        ];
    }

    /**
     * Initialiser les paramètres par défaut
     */
    public static function initDefaults()
    {
        foreach (self::getDefaults() as $setting) {
            self::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
