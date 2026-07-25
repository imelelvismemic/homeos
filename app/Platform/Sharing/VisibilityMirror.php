<?php

namespace App\Platform\Sharing;

use App\Platform\Enums\Visibility;
use Illuminate\Database\Eloquent\Model;

/**
 * Prenosi vidljivost (privatnost/dijeljenje) s jednog Shareable objekta na drugi
 * izvedeni objekat (CLAUDE.md §11). Primjer: podsjetnik/bilješka izvedeni iz
 * privatnog računa moraju naslijediti privatnost izvora — inače bi naslov (koji
 * nosi naziv izvora) procurio cijelom domaćinstvu.
 *
 * Izvedeni objekat je kroz Shareable već kreiran kao household-vidljiv, pa ovdje
 * samo suzimo/proširimo ako je izvor uži.
 */
class VisibilityMirror
{
    public static function mirror(Model $source, Model $target): void
    {
        if (! method_exists($source, 'share') || ! method_exists($target, 'share')) {
            return;
        }

        $share = $source->share;

        if ($share === null) {
            return;
        }

        match ($share->visibility) {
            Visibility::Private => $target->makePrivate(),
            Visibility::Specific => static::mirrorSpecific($share, $target),
            Visibility::Household => $target->shareWithHousehold(),
        };
    }

    private static function mirrorSpecific(Model $share, Model $target): void
    {
        $memberIds = $share->recipients()->pluck('household_member_id')->all();

        if ($memberIds !== []) {
            $target->shareWith($memberIds);
        }
    }
}
