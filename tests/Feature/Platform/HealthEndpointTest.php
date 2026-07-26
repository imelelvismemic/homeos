<?php

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

it('reports healthy with the app version', function () {
    test()->getJson('/health')
        ->assertOk()
        ->assertJson([
            'status' => 'ok',
            'version' => config('homeos.version'),
            'checks' => ['database' => true, 'cache' => true, 'storage' => true],
        ]);
});

it('is reachable without logging in (deploy and uptime monitor use it)', function () {
    test()->get('/health')->assertOk();
});

it('answers 503 when a dependency is down, not a cheerful 200', function () {
    // Deploy i monitor moraju razlikovati "stranica se renderuje" od "sistem radi".
    DB::shouldReceive('connection')->andThrow(new RuntimeException('baza nedostupna'));

    test()->getJson('/health')
        ->assertStatus(503)
        ->assertJson(['status' => 'degraded', 'checks' => ['database' => false]]);
});

/**
 * Cache store koji vrijednosti vraća kao STRING — tako se ponaša Redis
 * (`RedisStore::unserialize` numeričke vrijednosti vraća kao string). Bez ovoga
 * je test s `array` store-om prolazio, a produkcija javljala "cache: false".
 */
class StringifyingStore extends ArrayStore
{
    public function get($key): mixed
    {
        $value = parent::get($key);

        return is_null($value) ? null : (string) $value;
    }
}

it('treats the cache as healthy on drivers that return values as strings (Redis)', function () {
    Cache::swap(new Repository(new StringifyingStore));

    test()->getJson('/health')
        ->assertOk()
        ->assertJson(['checks' => ['cache' => true]]);
});
