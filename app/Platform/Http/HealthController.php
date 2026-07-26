<?php

namespace App\Platform\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

/**
 * Health endpoint (ROADMAP Faza 8). Koristi ga deploy (`deploy.yml`) da potvrdi
 * da nova verzija stvarno radi prije nego se smatra uspješnom, a može ga zvati i
 * vanjski uptime monitor.
 *
 * Vraća 200 samo ako su svi provjereni dijelovi zdravi, inače 503 — monitor time
 * hvata i "stranica se učitava, ali baza je pala".
 *
 * Namjerno ne otkriva detalje o infrastrukturi (verzije, hostove, kredencijale) —
 * ruta je javna.
 */
class HealthController
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->check(fn () => DB::connection()->getPdo() !== null),
            'cache' => $this->check(function (): bool {
                // Vrijednost je STRING, i to nasumičan. Redis store vraća brojeve
                // kao string ("1" umjesto 1), pa je poređenje s brojem lažno
                // padalo u produkciji dok je u testovima (array store) prolazilo.
                $token = Str::random(16);

                Cache::put('homeos:health', $token, 10);

                return Cache::get('homeos:health') === $token;
            }),
            'storage' => $this->check(fn () => is_writable(storage_path('app'))),
        ];

        $healthy = ! in_array(false, $checks, true);

        return response()->json([
            'status' => $healthy ? 'ok' : 'degraded',
            'version' => config('homeos.version'),
            'checks' => $checks,
        ], $healthy ? 200 : 503);
    }

    private function check(callable $probe): bool
    {
        try {
            return (bool) $probe();
        } catch (Throwable) {
            return false;
        }
    }
}
