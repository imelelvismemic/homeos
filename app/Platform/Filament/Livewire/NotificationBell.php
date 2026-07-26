<?php

namespace App\Platform\Filament\Livewire;

use App\Platform\Filament\Concerns\InteractsWithMemberInbox;
use App\Platform\Filament\Pages\NotificationsInbox;
use App\Platform\Models\Household;
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
 *
 * **Faza 9c:** na širokim ekranima klik na zvonce više ne vodi na stranicu, nego
 * otvara panel s desne strane — korisnik pročita i potvrdi obavijest, a ostaje
 * tamo gdje je bio. Na uskim ekranima panel te širine nije praktičan, pa zvonce
 * i dalje vodi na punu stranicu sandučeta.
 *
 * Stanje panela je Livewire property, ne Alpine: komponenta se osvježava
 * `wire:poll`-om, pa bi otvoreni panel držan samo u Alpine stanju preživio
 * poll, ali lista u njemu ne bi — ovako se oboje osvježi zajedno.
 */
class NotificationBell extends Component
{
    use InteractsWithMemberInbox;

    /** Koliko obavještenja panel prikazuje; puna historija je na stranici. */
    private const PANEL_LIMIT = 10;

    public int $unreadCount = 0;

    public bool $panelOpen = false;

    public function mount(): void
    {
        $this->refreshCount();
    }

    #[On('homeos-notifications-read')]
    public function refreshCount(): void
    {
        $this->unreadCount = $this->unreadCount();
    }

    public function openPanel(): void
    {
        $this->panelOpen = true;
        $this->refreshCount();
    }

    public function closePanel(): void
    {
        $this->panelOpen = false;
    }

    public function render(): View
    {
        return view('filament.platform.notification-bell', [
            'inboxUrl' => $this->inboxUrl(),
            'notifications' => $this->panelOpen ? $this->notifications(self::PANEL_LIMIT) : collect(),
        ]);
    }

    private function inboxUrl(): ?string
    {
        $household = Filament::getTenant();

        return $household instanceof Household
            ? NotificationsInbox::getUrl(tenant: $household)
            : null;
    }
}
