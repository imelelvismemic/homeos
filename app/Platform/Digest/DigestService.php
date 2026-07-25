<?php

namespace App\Platform\Digest;

use App\Models\User;
use App\Platform\Contracts\DigestSourceContract;
use App\Platform\Enums\DigestFrequency;
use App\Platform\Models\Household;
use App\Platform\Models\HouseholdMember;
use App\Platform\Notifications\DigestNotification;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Notification;

/**
 * Agregira digest sekcije svih registrovanih modula (config/homeos-apps.php →
 * digest_source) i šalje digest email članovima koji su odabrali dati ritam
 * (Faza 6). Core ne zna pojedinačno za module — čita samo registry i contract.
 */
class DigestService
{
    /**
     * Sekcije za jednog člana u periodu (samo neprazne, samo vidljivo tom članu).
     *
     * @return array<int, DigestSection>
     */
    public function sectionsFor(Household $household, User $user, CarbonInterface $from, CarbonInterface $to): array
    {
        $sections = [];

        foreach (config('homeos-apps', []) as $module) {
            if (! ($module['enabled'] ?? true) || empty($module['digest_source'])) {
                continue;
            }

            $source = app($module['digest_source']);

            if (! $source instanceof DigestSourceContract) {
                continue;
            }

            $section = $source->digestSection($household, $user, $from, $to);

            if ($section !== null && ! $section->isEmpty()) {
                $sections[] = $section;
            }
        }

        return $sections;
    }

    /**
     * Pošalji digest svim članovima čiji je odabrani ritam jednak zadanom. Prazan
     * digest (nema ništa u periodu) se NE šalje — bez email šuma.
     */
    public function sendDue(DigestFrequency $frequency, ?CarbonInterface $now = null): void
    {
        if ($frequency === DigestFrequency::None) {
            return;
        }

        $from = $now?->copy() ?? now();
        $to = $from->copy()->addDays($frequency->windowDays());

        HouseholdMember::query()
            ->where('digest_frequency', $frequency->value)
            ->with(['user', 'household'])
            ->get()
            ->each(function (HouseholdMember $member) use ($frequency, $from, $to) {
                if ($member->user === null || $member->household === null) {
                    return;
                }

                $sections = $this->sectionsFor($member->household, $member->user, $from, $to);

                if ($sections === []) {
                    return;
                }

                Notification::send($member, new DigestNotification($sections, $frequency));
            });
    }
}
