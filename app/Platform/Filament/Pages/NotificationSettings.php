<?php

namespace App\Platform\Filament\Pages;

use App\Platform\Enums\DigestFrequency;
use App\Platform\Models\HouseholdMember;
use App\Platform\Models\NotificationPreference;
use App\Platform\Notifications\NotificationCategoryRegistry;
use Filament\Facades\Filament;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Postavke obavještenja po članu (Faza 6): email uključi/isključi po kategoriji +
 * ritam digesta. In-app obavještenja uvijek stižu (HouseholdNotification). DoD:
 * član može isključiti sve emailove osim npr. `bill_due` i to se poštuje sistemski.
 */
class NotificationSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';

    protected static ?int $navigationSort = 90;

    protected static string $view = 'filament.platform.pages.notification-settings';

    /** @var array<string, mixed> */
    public array $data = [];

    public static function getNavigationLabel(): string
    {
        return __('platform.settings.navigation_label');
    }

    public function getTitle(): string
    {
        return __('platform.settings.title');
    }

    public function mount(): void
    {
        $member = $this->currentMember();

        $prefs = $member !== null
            ? $member->notificationPreferences()->pluck('email_enabled', 'category')
            : collect();

        $state = [
            'digest_frequency' => $member?->digest_frequency?->value ?? DigestFrequency::None->value,
        ];

        foreach (NotificationCategoryRegistry::keys() as $category) {
            $state["email_{$category}"] = (bool) ($prefs[$category] ?? true);
        }

        $this->form->fill($state);
    }

    public function form(Form $form): Form
    {
        $toggles = collect(NotificationCategoryRegistry::labelled())
            ->map(fn (string $label, string $category) => Toggle::make("email_{$category}")->label($label))
            ->values()
            ->all();

        return $form
            ->schema([
                Section::make(__('platform.settings.email_section'))
                    ->description(__('platform.settings.subheading'))
                    ->schema($toggles),

                Section::make(__('platform.settings.digest_section'))
                    ->description(__('platform.settings.digest_help'))
                    ->schema([
                        Select::make('digest_frequency')
                            ->label(__('platform.settings.digest_frequency'))
                            ->options(DigestFrequency::options())
                            ->required(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $member = $this->currentMember();

        if ($member === null) {
            return;
        }

        $data = $this->form->getState();

        $member->update(['digest_frequency' => $data['digest_frequency']]);

        foreach (NotificationCategoryRegistry::keys() as $category) {
            NotificationPreference::updateOrCreate(
                ['household_member_id' => $member->id, 'category' => $category],
                ['email_enabled' => (bool) ($data["email_{$category}"] ?? true)],
            );
        }

        Notification::make()->title(__('platform.settings.saved'))->success()->send();
    }

    private function currentMember(): ?HouseholdMember
    {
        $household = Filament::getTenant();

        return $household?->members()->where('user_id', auth()->id())->first();
    }
}
