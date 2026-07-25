<?php

namespace App\Platform\Http;

use App\Platform\Calendar\CalendarService;
use App\Platform\Models\Household;
use Carbon\CarbonImmutable;
use Filament\Facades\Filament;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * JSON feed kalendara (FullCalendar `events` URL). Prikaz sam javlja raspon
 * (`start`/`end`) koji trenutno gleda, pa se događaji mogu ponovo dohvatiti bez
 * ponovnog učitavanja stranice — zato kalendar nakon brzog dodavanja pokaže novi
 * zapis, a ostane na istom mjesecu.
 *
 * Agregira sve registrovane CalendarSourceContract izvore (CalendarService) —
 * ne zna pojedinačno za module (CLAUDE.md §12/§18). Isti obrazac kao
 * SearchController: ruta panela, auth i pripadnost domaćinstvu provjereni ovdje.
 */
class CalendarEventsController
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = auth()->user();
        abort_unless($user !== null, 403);

        $household = Household::find((int) $request->query('h'));
        abort_unless(
            $household instanceof Household
                && $household->members()->where('user_id', $user->getKey())->exists(),
            404,
        );

        Filament::setTenant($household);

        $start = $this->parse($request->query('start'), CarbonImmutable::now()->startOfMonth()->subMonth());
        $end = $this->parse($request->query('end'), CarbonImmutable::now()->endOfMonth()->addMonths(2));

        return response()->json(
            app(CalendarService::class)->fullCalendarEvents($start, $end, $household),
        );
    }

    private function parse(?string $value, CarbonImmutable $fallback): CarbonImmutable
    {
        if (blank($value)) {
            return $fallback;
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (\Throwable) {
            return $fallback;
        }
    }
}
