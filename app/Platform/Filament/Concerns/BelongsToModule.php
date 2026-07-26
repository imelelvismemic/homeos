<?php

namespace App\Platform\Filament\Concerns;

use App\Platform\Modules\ModuleRegistry;

/**
 * Veže Filament Resource/Page za modul kojem pripada, pa isključen modul nestane
 * i iz **navigacije** i s **rute** — ne samo s dashboarda, pretrage i kalendara
 * (ROADMAP Faza 7, graceful degradation).
 *
 * Ključ modula se izvodi iz namespace-a (`App\Modules\Tasks\…` → `tasks`), istom
 * konvencijom foldera kojom Filament auto-discoveruje resurse (CLAUDE.md §4/§5) —
 * zato core ne mora držati listu klasa po modulu.
 *
 * Koristi ga SVAKI Resource i svaka Page unutar `app/Modules/*` (CLAUDE.md §14).
 */
trait BelongsToModule
{
    public static function shouldRegisterNavigation(): bool
    {
        return static::moduleIsEnabled() && parent::shouldRegisterNavigation();
    }

    public static function canAccess(): bool
    {
        return static::moduleIsEnabled() && parent::canAccess();
    }

    protected static function moduleIsEnabled(): bool
    {
        return app(ModuleRegistry::class)->classIsEnabled(static::class);
    }
}
