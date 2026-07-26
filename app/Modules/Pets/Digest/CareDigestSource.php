<?php

namespace App\Modules\Pets\Digest;

use App\Models\User;
use App\Modules\Pets\Models\CareRecord;
use App\Platform\Contracts\DigestSourceContract;
use App\Platform\Digest\DigestSection;
use App\Platform\Models\Household;
use Carbon\CarbonInterface;

/**
 * Doprinos Ljubimaca digestu: nezavršena njega u periodu, vidljiva članu.
 */
class CareDigestSource implements DigestSourceContract
{
    public function digestSection(Household $household, User $user, CarbonInterface $from, CarbonInterface $to): ?DigestSection
    {
        $records = CareRecord::query()
            ->where('household_id', $household->id)
            ->visibleTo($user)
            ->whereNull('completed_at')
            ->whereBetween('due_date', [$from, $to])
            ->with('pet')
            ->orderBy('due_date')
            ->get();

        if ($records->isEmpty()) {
            return null;
        }

        return new DigestSection(
            __('pets.plural_label'),
            $records->map(fn (CareRecord $r) => '• '.__('pets.digest.line', [
                'title' => $r->displayTitle(),
                'date' => $r->due_date->translatedFormat('d.m.Y. H:i'),
            ]))->all(),
        );
    }
}
