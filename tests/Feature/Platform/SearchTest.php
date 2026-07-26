<?php

use App\Platform\Search\SearchService;
use Tests\Fixtures\FakeSearchProvider;

it('returns empty gracefully when no modules are registered', function () {
    config()->set('homeos-apps', []);
    [$household] = makeHousehold();

    $results = app(SearchService::class)->search('bilo šta', $household);

    expect($results)->toBeEmpty();
});

it('aggregates results from a registered provider, scoped to household', function () {
    // Modul postaje pretraživ samo registracijom u config/homeos-apps.php —
    // SearchService se ne mijenja.
    config()->set('homeos-apps', [
        'fake' => [
            'enabled' => true,
            'search_provider' => FakeSearchProvider::class,
        ],
    ]);
    [$household] = makeHousehold();

    $results = app(SearchService::class)->search('zdravo', $household);

    expect($results)->toHaveCount(1);
    expect($results->first()->type)->toBe('fake');
    expect($results->first()->title)->toContain('zdravo');
});

it('skips disabled modules', function () {
    config()->set('homeos-apps', [
        'fake' => [
            'enabled' => false,
            'search_provider' => FakeSearchProvider::class,
        ],
    ]);
    [$household] = makeHousehold();

    expect(app(SearchService::class)->search('zdravo', $household))->toBeEmpty();
});

it('finds household members in the universal search, scoped to the current household', function () {
    [$household, $owner, $members] = makeHousehold(extraMembers: 1);
    $members[0]->user->update(['name' => 'Amina Memić']);
    test()->actingAs($owner->user);

    [$otherHousehold, $otherOwner] = makeHousehold();
    $otherOwner->user->update(['name' => 'Amina Tuđa']);

    $results = app(SearchService::class)->search('Amina', $household);

    expect($results->pluck('title'))->toContain('Amina Memić');
    expect($results->pluck('title'))->not->toContain('Amina Tuđa');
    expect($results->firstWhere('title', 'Amina Memić')->type)->toBe('member');
});
