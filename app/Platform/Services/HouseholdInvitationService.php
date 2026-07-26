<?php

namespace App\Platform\Services;

use App\Models\User;
use App\Platform\Localization\Locales;
use App\Platform\Models\Household;
use App\Platform\Models\HouseholdInvitation;
use App\Platform\Models\HouseholdMember;
use App\Platform\Notifications\HouseholdInvited;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Pozivanje u domaćinstvo (ROADMAP Faza 7c). Logika je ovdje, ne u Filament
 * stranici (CLAUDE.md §15).
 *
 * Jedan ulaz, dva ishoda:
 *  - osoba VEĆ ima nalog → odmah postaje član (kao i do sada),
 *  - osoba nema nalog → dobija pozivnicu s jednokratnim linkom; kroz njega se
 *    registruje i odmah ulazi u domaćinstvo, bez kreiranja vlastitog.
 *
 * Token se generiše ovdje i vraća pozivaocu samo u linku — u bazi stoji hash.
 */
class HouseholdInvitationService
{
    public const EXPIRES_AFTER_DAYS = 7;

    /** Rezultat poziva: da li je korisnik odmah dodan ili mu je poslana pozivnica. */
    public function invite(Household $household, string $email, string $role, User $invitedBy): bool
    {
        $email = mb_strtolower(trim($email));
        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

        if ($user !== null) {
            if ($household->users()->whereKey($user->getKey())->exists()) {
                throw new RuntimeException(__('platform.members.already_member'));
            }

            $household->members()->create([
                'user_id' => $user->getKey(),
                'role' => $role,
                'joined_at' => now(),
            ]);

            return true;
        }

        $this->sendInvitation($household, $email, $role, $invitedBy);

        return false;
    }

    /** Kreira (ili obnavlja) pozivnicu i pošalje email s linkom. */
    private function sendInvitation(Household $household, string $email, string $role, User $invitedBy): void
    {
        $token = Str::random(48);

        $invitation = HouseholdInvitation::updateOrCreate(
            ['household_id' => $household->getKey(), 'email' => $email],
            [
                'invited_by' => $invitedBy->getKey(),
                'role' => $role,
                'token' => hash('sha256', $token),
                'expires_at' => now()->addDays(self::EXPIRES_AFTER_DAYS),
                'accepted_at' => null,
            ],
        );

        // Primalac još nema nalog (ni člana), pa notifikacija ide "on-demand" na
        // email adresu — i dalje kroz Notification sistem, nikad Mail::send (§10).
        //
        // Jezik primaoca ne postoji (nema nalog), pa pozivnica ide na jeziku
        // onoga ko poziva — najbliža pretpostavka: poziva nekoga iz svoje kuće.
        Notification::route('mail', $email)
            ->notify(
                (new HouseholdInvited($invitation, $token, $household->name, $invitedBy->name))
                    ->locale(Locales::sanitize($invitedBy->locale)),
            );
    }

    /**
     * Pronađi upotrebljivu pozivnicu po tokenu iz linka.
     */
    public function findPending(string $token): ?HouseholdInvitation
    {
        return HouseholdInvitation::query()
            ->pending()
            ->where('token', hash('sha256', $token))
            ->first();
    }

    /**
     * Prihvati pozivnicu za dati nalog. Email naloga mora odgovarati pozivnici —
     * inače bi proslijeđen link ulazio u tuđe domaćinstvo.
     */
    public function accept(HouseholdInvitation $invitation, User $user): HouseholdMember
    {
        if (! $invitation->isPending()) {
            throw new RuntimeException(__('platform.invitations.invalid'));
        }

        if (mb_strtolower($user->email) !== mb_strtolower($invitation->email)) {
            throw new RuntimeException(__('platform.invitations.wrong_account'));
        }

        $member = $invitation->household->members()->firstOrCreate(
            ['user_id' => $user->getKey()],
            ['role' => $invitation->role, 'joined_at' => now()],
        );

        $invitation->update(['accepted_at' => now()]);

        // Novi član ulazi pravo u to domaćinstvo, bez koraka "izaberi domaćinstvo".
        $user->update(['current_household_id' => $invitation->household_id]);

        return $member;
    }
}
