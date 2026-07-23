<?php

namespace App\Platform\Filament\Resources\HouseholdMemberResource\Pages;

use App\Models\User;
use App\Platform\Filament\Resources\HouseholdMemberResource;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;

class ListHouseholdMembers extends ListRecords
{
    protected static string $resource = HouseholdMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('invite')
                ->label(__('platform.members.invite_action'))
                ->modalHeading(__('platform.members.invite_modal_heading'))
                ->modalSubmitActionLabel(__('platform.members.invite_modal_submit'))
                ->form([
                    TextInput::make('email')
                        ->label(__('platform.members.user'))
                        ->helperText(__('platform.members.user_helper'))
                        ->email()
                        ->required()
                        ->exists(table: 'users', column: 'email')
                        ->validationMessages([
                            'exists' => __('platform.members.user_not_found'),
                        ])
                        ->rule(function () {
                            return function (string $attribute, $value, \Closure $fail): void {
                                $household = Filament::getTenant();
                                $user = User::where('email', $value)->first();

                                if ($user && $household->users()->whereKey($user->id)->exists()) {
                                    $fail(__('platform.members.already_member'));
                                }
                            };
                        }),
                    Select::make('role')
                        ->label(__('platform.members.role'))
                        ->options([
                            'member' => __('platform.members.role_member'),
                            'owner' => __('platform.members.role_owner'),
                        ])
                        ->default('member')
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $household = Filament::getTenant();
                    $user = User::where('email', $data['email'])->firstOrFail();

                    $household->members()->create([
                        'user_id' => $user->id,
                        'role' => $data['role'],
                        'joined_at' => now(),
                    ]);
                }),
        ];
    }
}
