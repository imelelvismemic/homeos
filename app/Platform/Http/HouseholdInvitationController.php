<?php

namespace App\Platform\Http;

use App\Models\User;
use App\Platform\Services\HouseholdInvitationService;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use RuntimeException;

/**
 * Ulazna tačka pozivnice iz emaila (ROADMAP Faza 7c). Javna ruta — pozvana osoba
 * najčešće još nema nalog.
 *
 * Tri ishoda:
 *  - prijavljen korisnik s odgovarajućim emailom → odmah postaje član,
 *  - nema nalog → token ide u sesiju, vodimo ga na registraciju s popunjenim
 *    emailom; po registraciji se pozivnica prihvata sama (RegisterUser),
 *  - ima nalog ali nije prijavljen → vodimo ga na prijavu, token čeka u sesiji.
 */
class HouseholdInvitationController
{
    public const SESSION_KEY = 'homeos.invitation_token';

    public function __invoke(string $token, HouseholdInvitationService $invitations): RedirectResponse
    {
        $invitation = $invitations->findPending($token);

        if ($invitation === null) {
            Notification::make()->title(__('platform.invitations.invalid'))->danger()->send();

            return redirect(Filament::getLoginUrl());
        }

        $user = auth()->user();

        if ($user instanceof User) {
            return $this->acceptForLoggedInUser($invitation, $user, $invitations);
        }

        session([self::SESSION_KEY => $token]);

        $hasAccount = User::whereRaw('LOWER(email) = ?', [mb_strtolower($invitation->email)])->exists();

        Notification::make()
            ->title(__($hasAccount ? 'platform.invitations.please_log_in' : 'platform.invitations.please_register', [
                'household' => $invitation->household->name,
            ]))
            ->success()
            ->send();

        return redirect($hasAccount ? Filament::getLoginUrl() : Filament::getRegistrationUrl());
    }

    private function acceptForLoggedInUser(
        $invitation,
        User $user,
        HouseholdInvitationService $invitations,
    ): RedirectResponse {
        try {
            $invitations->accept($invitation, $user);
        } catch (RuntimeException $e) {
            Notification::make()->title($e->getMessage())->danger()->send();

            return redirect(Filament::getUrl());
        }

        session()->forget(self::SESSION_KEY);

        Notification::make()
            ->title(__('platform.invitations.joined', ['household' => $invitation->household->name]))
            ->success()
            ->send();

        return redirect(Filament::getUrl($invitation->household));
    }
}
