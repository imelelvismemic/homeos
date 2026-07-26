<?php

namespace App\Platform\Filament\Concerns;

use App\Platform\Models\HouseholdMember;
use Filament\Facades\Filament;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;

/**
 * Zajedničko ponašanje sandučeta obavještenja (ROADMAP Faza 9c).
 *
 * Obavještenja se šalju na `HouseholdMember` (per-domaćinstvo kontekst + email
 * preferencije po članu), pa se čitaju kroz trenutnog člana, ne kroz `User`.
 *
 * Trait postoji jer sanduče od Faze 9c ima DVA prikaza: punu stranicu i panel
 * koji zvonce otvara s desne strane. Logika (šta se prikazuje, šta znači
 * pročitano, kako se javlja novi broj) mora biti jedna — inače se dva prikaza
 * razilaze u ponašanju, a korisnik vidi različit broj nepročitanih na dva mjesta.
 */
trait InteractsWithMemberInbox
{
    /** Pročitana obavještenja su po defaultu skrivena — sanduče pokazuje šta je novo. */
    public bool $showRead = false;

    /** @return Collection<int, DatabaseNotification> */
    public function notifications(int $limit = 50): Collection
    {
        $member = $this->currentMember();

        if ($member === null) {
            return collect();
        }

        return $member->notifications()
            ->when(! $this->showRead, fn ($query) => $query->whereNull('read_at'))
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function unreadCount(): int
    {
        return $this->currentMember()?->unreadNotifications()->count() ?? 0;
    }

    public function toggleShowRead(): void
    {
        $this->showRead = ! $this->showRead;
    }

    public function markAsRead(string $id): void
    {
        $this->currentMember()?->notifications()->whereKey($id)->first()?->markAsRead();

        $this->announceUnreadCount();
    }

    public function markAllRead(): void
    {
        $this->currentMember()?->unreadNotifications->markAsRead();

        $this->announceUnreadCount();
    }

    /** Prikazna linija obavještenja iz njegovog `data` (kategorija + naslov). */
    public function line(array $data): string
    {
        $category = $data['category'] ?? 'shared_with_you';
        $key = "platform.inbox.lines.{$category}";
        $line = __($key, ['title' => $data['title'] ?? '']);

        return $line === $key ? (string) ($data['title'] ?? '') : $line;
    }

    /** Javi ostalim komponentama (zvonce, stranica) novi broj nepročitanih. */
    protected function announceUnreadCount(): void
    {
        $this->dispatch('homeos-notifications-read', count: $this->unreadCount());
    }

    protected function currentMember(): ?HouseholdMember
    {
        $household = Filament::getTenant();

        return $household?->members()->where('user_id', auth()->id())->first();
    }
}
