<?php

namespace App\Platform\Notifications;

/**
 * Jedinstveni izvor liste email-kategorija (DATA_MODEL.md §5) za postavke
 * obavještenja. Platform nosi generičku kategoriju `shared_with_you`; svaki modul
 * dopisuje svoje kroz config/homeos-apps.php pod `notification_categories`
 * (lista ključeva). Core čita samo odavde — nema hardkodovane liste (CLAUDE.md §12).
 * Digest se NE nalazi ovdje — kontroliše se zasebno (ritam po članu).
 */
class NotificationCategoryRegistry
{
    /** @return array<int, string> ključevi kategorija */
    public static function keys(): array
    {
        $keys = ['shared_with_you']; // platform-generička

        foreach (config('homeos-apps', []) as $module) {
            if (($module['enabled'] ?? true) && ! empty($module['notification_categories'])) {
                $keys = array_merge($keys, $module['notification_categories']);
            }
        }

        return array_values(array_unique($keys));
    }

    /** @return array<string, string> ključ => prikazna labela */
    public static function labelled(): array
    {
        return collect(static::keys())
            ->mapWithKeys(fn (string $key) => [$key => __("platform.notifications.categories.{$key}")])
            ->all();
    }
}
