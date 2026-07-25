<?php

namespace App\Modules\LifeAdmin\Digest;

use App\Models\User;
use App\Modules\LifeAdmin\Models\Document;
use App\Platform\Contracts\DigestSourceContract;
use App\Platform\Digest\DigestSection;
use App\Platform\Models\Household;
use Carbon\CarbonInterface;

/**
 * Doprinos Life admina digestu (Faza 6): dokumenti kojima rok ističe u periodu,
 * vidljivi članu.
 */
class DocumentDigestSource implements DigestSourceContract
{
    public function digestSection(Household $household, User $user, CarbonInterface $from, CarbonInterface $to): ?DigestSection
    {
        $documents = Document::query()
            ->where('household_id', $household->id)
            ->visibleTo($user)
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [$from, $to])
            ->orderBy('expiry_date')
            ->get();

        if ($documents->isEmpty()) {
            return null;
        }

        return new DigestSection(
            __('lifeadmin.documents.plural_label'),
            $documents->map(fn (Document $d) => '• '.__('lifeadmin.digest.line', [
                'title' => $d->title,
                'date' => $d->expiry_date->translatedFormat('d.m.Y.'),
            ]))->all(),
        );
    }
}
