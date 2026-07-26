<x-filament-panels::page>
    <x-filament-panels::form id="form" wire:submit="save">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>

    {{-- Članovi domaćinstva su dio postavki domaćinstva, ne zasebna stavka menija.
         Svaki član vidi listu; radnje nad članovima vidi samo vlasnik. --}}
    {{ $this->table }}

    {{-- Poslane pozivnice (Faza 7c) — osobe koje još nemaju nalog, pa nisu članovi.
         Vidi ih samo vlasnik i može ih povući. --}}
    @php($invitations = $this->pendingInvitations())

    @if ($invitations->isNotEmpty())
        <x-filament::section>
            <x-slot name="heading">{{ __('platform.invitations.pending_heading') }}</x-slot>
            <x-slot name="description">{{ __('platform.invitations.pending_description') }}</x-slot>

            <ul class="divide-y divide-gray-100 dark:divide-white/10">
                @foreach ($invitations as $invitation)
                    <li class="flex items-center justify-between gap-3 py-2">
                        <div class="min-w-0">
                            <p class="truncate text-sm text-gray-950 dark:text-white">{{ $invitation->email }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ __('platform.invitations.expires_at', [
                                    'date' => $invitation->expires_at->translatedFormat('d.m.Y.'),
                                    'role' => __('platform.members.role_' . $invitation->role),
                                ]) }}
                            </p>
                        </div>

                        <x-filament::button
                            color="danger"
                            size="xs"
                            wire:click="revokeInvitation({{ $invitation->getKey() }})"
                        >
                            {{ __('platform.invitations.revoke') }}
                        </x-filament::button>
                    </li>
                @endforeach
            </ul>
        </x-filament::section>
    @endif
</x-filament-panels::page>
