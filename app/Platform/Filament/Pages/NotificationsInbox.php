<?php

namespace App\Platform\Filament\Pages;

use App\Platform\Models\HouseholdMember;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;

/**
 * In-app sanduče obavještenja za trenutnog člana domaćinstva (Faza 6). Obavještenja
 * se šalju na HouseholdMember (per-domaćinstvo kontekst + email preferencije po
 * članu), pa Filament-ovo native zvonce (koje čita User) ne bi radilo — ovdje ih
 * prikazujemo scope-ovano na trenutnog člana, uz označavanje pročitanim.
 */
class NotificationsInbox extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-bell';

    protected static ?int $navigationSort = 89;

    protected static string $view = 'filament.platform.pages.notifications-inbox';

    /** Pročitana obavještenja su po defaultu skrivena — sanduče pokazuje šta je novo. */
    public bool $showRead = false;

    public static function getNavigationLabel(): string
    {
        return __('platform.inbox.navigation_label');
    }

    public function getTitle(): string
    {
        return __('platform.inbox.title');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::unreadCountFor(static::currentMember());

        return $count > 0 ? (string) $count : null;
    }

    /** @return Collection<int, DatabaseNotification> */
    public function notifications(): Collection
    {
        $member = static::currentMember();

        if ($member === null) {
            return collect();
        }

        return $member->notifications()
            ->when(! $this->showRead, fn ($query) => $query->whereNull('read_at'))
            ->latest()
            ->limit(50)
            ->get();
    }

    public function unreadCount(): int
    {
        return static::unreadCountFor(static::currentMember());
    }

    public function toggleShowRead(): void
    {
        $this->showRead = ! $this->showRead;
    }

    public function markAsRead(string $id): void
    {
        static::currentMember()?->notifications()->whereKey($id)->first()?->markAsRead();

        $this->announceUnreadCount();
    }

    public function markAllRead(): void
    {
        static::currentMember()?->unreadNotifications->markAsRead();

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

    /** Javi zvoncetu u topbaru novi broj nepročitanih (bez ponovnog učitavanja). */
    private function announceUnreadCount(): void
    {
        $this->dispatch('homeos-notifications-read', count: $this->unreadCount());
    }

    private static function currentMember(): ?HouseholdMember
    {
        $household = Filament::getTenant();

        return $household?->members()->where('user_id', auth()->id())->first();
    }

    private static function unreadCountFor(?HouseholdMember $member): int
    {
        return $member?->unreadNotifications()->count() ?? 0;
    }
}
