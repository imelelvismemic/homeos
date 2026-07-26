<?php

namespace App\Platform\Filament\Pages;

use App\Platform\Filament\Concerns\InteractsWithMemberInbox;
use App\Platform\Models\HouseholdMember;
use Filament\Facades\Filament;
use Filament\Pages\Page;

/**
 * In-app sanduče obavještenja za trenutnog člana domaćinstva (Faza 6). Obavještenja
 * se šalju na HouseholdMember (per-domaćinstvo kontekst + email preferencije po
 * članu), pa Filament-ovo native zvonce (koje čita User) ne bi radilo — ovdje ih
 * prikazujemo scope-ovano na trenutnog člana, uz označavanje pročitanim.
 *
 * Od Faze 9c ista logika (trait) služi i panelu koji zvonce otvara s desne strane;
 * stranica ostaje puni prikaz, s dužom historijom.
 */
class NotificationsInbox extends Page
{
    use InteractsWithMemberInbox;

    protected static ?string $navigationIcon = 'heroicon-o-bell';

    // Sanduče se otvara zvoncetom u topbaru (s brojačem nepročitanih) — druga
    // stavka u meniju za istu stranicu bi bila suvišna.
    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.platform.pages.notifications-inbox';

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
        $household = Filament::getTenant();
        $member = $household?->members()->where('user_id', auth()->id())->first();

        $count = $member instanceof HouseholdMember
            ? $member->unreadNotifications()->count()
            : 0;

        return $count > 0 ? (string) $count : null;
    }
}
