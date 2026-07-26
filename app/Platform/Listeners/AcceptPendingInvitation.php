<?php

namespace App\Platform\Listeners;

use App\Models\User;
use App\Platform\Http\HouseholdInvitationController;
use App\Platform\Services\HouseholdInvitationService;
use Filament\Notifications\Notification;
use Illuminate\Auth\Events\Login;
use Throwable;

/**
 * Pozivnica se prihvata u trenutku prijave (ROADMAP Faza 7c).
 *
 * Time je pokriveno oboje jednim mehanizmom: i onaj ko se tek registrovao kroz
 * link (Filament ga po registraciji prijavi), i onaj ko je već imao nalog pa se
 * prijavio. Token čeka u sesiji od klika na link; ako ga nema, listener ne radi
 * ništa.
 */
class AcceptPendingInvitation
{
    public function __construct(private HouseholdInvitationService $invitations) {}

    public function handle(Login $event): void
    {
        $token = session(HouseholdInvitationController::SESSION_KEY);

        if (! is_string($token) || $token === '' || ! $event->user instanceof User) {
            return;
        }

        session()->forget(HouseholdInvitationController::SESSION_KEY);

        $invitation = $this->invitations->findPending($token);

        if ($invitation === null) {
            return;
        }

        try {
            $this->invitations->accept($invitation, $event->user);
        } catch (Throwable) {
            // Istekla pozivnica ili prijava s drugog naloga — prijava ostaje
            // uspješna, korisnik samo nije dodan u domaćinstvo.
            Notification::make()->title(__('platform.invitations.wrong_account'))->danger()->send();

            return;
        }

        Notification::make()
            ->title(__('platform.invitations.joined', ['household' => $invitation->household->name]))
            ->success()
            ->send();
    }
}
