<?php

namespace App\Platform\Filament\Livewire;

use App\Platform\Filament\Pages\NotificationsInbox;
use App\Platform\Models\Household;
use App\Platform\Models\HouseholdMember;
use Filament\Facades\Filament;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Zvonce obavještenja u topbaru (ROADMAP Faza 9, tačka 8).
 *
 * Ranije je bio običan server-renderovan link, pa je nova obavijest stizala tek
 * na sljedeće učitavanje stranice — npr. kad se podsjetnik okine s liste,
 * obavijest je već u sanducetu, a brojač i dalje pokazuje staro stanje.
 *
 * Sada je Livewire komponenta koja se osvježava sama:
 *  - `wire:poll.30s` — nove obavijesti stignu bez ijednog klika,
 *  - `homeos-notifications-read` — kad sanduče označi poruke pročitanim, brojač
 *    pada ODMAH, ne tek na sljedeći poll.
 *
 * Reverb (WebSocket) bi bio trenutan i bez pollinga, ali traži novi kontejner i
 * ručnu izmjenu Apache vhosta na serveru — odluka vlasnika je bila polling
 * (30s kašnjenja je za kućne podsjetnike sasvim dovoljno).
 */
class NotificationBell extends Component
{
    public int $unreadCount = 0;

    public function mount(): void
    {
        $this->refreshCount();
    }

    #[On('homeos-notifications-read')]
    public function refreshCount(): void
    {
        $this->unreadCount = $this->currentMember()?->unreadNotifications()->count() ?? 0;
    }

    public function render(): View
    {
        return view('filament.platform.notification-bell', [
            'inboxUrl' => $this->inboxUrl(),
        ]);
    }

    private function inboxUrl(): ?string
    {
        $household = Filament::getTenant();

        return $household instanceof Household
            ? NotificationsInbox::getUrl(tenant: $household)
            : null;
    }

    private function currentMember(): ?HouseholdMember
    {
        $household = Filament::getTenant();

        return $household?->members()->where('user_id', auth()->id())->first();
    }
}
