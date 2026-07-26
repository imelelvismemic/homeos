<?php

namespace App\Platform\Filament\Pages;

use App\Platform\Http\HouseholdInvitationController;
use App\Platform\Services\HouseholdInvitationService;
use Filament\Forms\Components\Component;
use Filament\Pages\Auth\Register as BaseRegister;

/**
 * Registracija koja zna za pozivnicu (ROADMAP Faza 7c).
 *
 * Kad korisnik dođe s linka iz pozivnice, token čeka u sesiji: email je unaprijed
 * popunjen i **zaključan**, da se pozivnica ne iskoristi za drugu adresu.
 *
 * Samo prihvatanje pozivnice NIJE ovdje — radi ga listener na Login eventu
 * (`AcceptPendingInvitation`), pa isti mehanizam pokriva i onoga ko se registruje
 * kroz link i onoga ko već ima nalog pa se prijavi. Filament po registraciji sam
 * prijavi korisnika, tako da je i ovaj tok pokriven.
 *
 * Bez pozivnice se ponaša kao obična Filament registracija.
 */
class RegisterUser extends BaseRegister
{
    protected function getEmailFormComponent(): Component
    {
        $email = $this->invitedEmail();

        return parent::getEmailFormComponent()
            ->default($email)
            ->readOnly(filled($email))
            ->helperText(filled($email) ? __('platform.invitations.email_locked') : null);
    }

    private function invitedEmail(): ?string
    {
        $token = session(HouseholdInvitationController::SESSION_KEY);

        if (! is_string($token) || $token === '') {
            return null;
        }

        return app(HouseholdInvitationService::class)->findPending($token)?->email;
    }
}
