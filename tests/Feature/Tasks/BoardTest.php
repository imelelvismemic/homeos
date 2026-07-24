<?php

use App\Modules\Tasks\Filament\Pages\KanbanBoard;
use App\Modules\Tasks\Models\Board;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
});

it('creates a board from the kanban page, scoped to the household', function () {
    [$household, $owner] = makeHousehold();
    test()->actingAs($owner->user);
    Filament::setTenant($household);

    Livewire::test(KanbanBoard::class)
        ->callAction('newBoard', data: ['name' => 'Kuća'])
        ->assertHasNoActionErrors();

    $board = Board::firstWhere('name', 'Kuća');

    expect($board)->not->toBeNull();
    expect($board->household_id)->toBe($household->id);
    expect($board->created_by)->toBe($owner->user_id);
});
