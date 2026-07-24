<?php

namespace App\Platform\Http;

use App\Platform\Models\Household;
use App\Platform\Search\SearchResult;
use App\Platform\Search\SearchService;
use Filament\Facades\Filament;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * JSON endpoint univerzalne pretrage (command palette). Registrovan kao ruta
 * PANELA (App\Providers\Filament\HomePanelProvider ->routes()), pa prolazi kroz
 * Filament auth + tenant + serving middleware — dakle Filament::getTenant(),
 * auth()->user() i "current panel" (za TaskResource::getUrl) su postavljeni.
 *
 * Namjerno OBIČAN GET (fetch iz Alpinea), ne Livewire — custom Livewire
 * komponenta u render hooku je iza proxyja obarala /livewire/update na 419
 * (snapshot/checksum). Ovako nema Livewire round-tripa.
 */
class SearchController
{
    public function __invoke(Request $request): JsonResponse
    {
        // Ruta panela ima SetUpPanel (current panel → getUrl radi) i sesiju, ali
        // ne i auth/tenant middleware — pa auth i pripadnost domaćinstvu provjeravamo
        // ovdje, i tenant postavljamo ručno iz `h` parametra.
        $user = auth()->user();
        abort_unless($user !== null, 403);

        $household = Household::find((int) $request->query('h'));
        abort_unless(
            $household instanceof Household
                && $household->members()->where('user_id', $user->getKey())->exists(),
            404,
        );

        Filament::setTenant($household);

        $query = trim((string) $request->query('q', ''));

        if (Str::length($query) < 2) {
            return response()->json(['groups' => []]);
        }

        $groups = app(SearchService::class)->searchGrouped($query, $household)
            ->map(fn ($results, string $type): array => [
                'type' => $type,
                'label' => $this->typeLabel($type),
                'results' => $results->map(fn (SearchResult $r): array => [
                    'id' => $r->id,
                    'title' => $r->title,
                    'url' => $r->url,
                ])->values(),
            ])
            ->values();

        return response()->json(['groups' => $groups]);
    }

    private function typeLabel(string $type): string
    {
        $key = "search.types.{$type}";

        return __($key) === $key ? Str::headline($type) : __($key);
    }
}
