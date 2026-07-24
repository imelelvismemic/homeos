<?php

namespace App\Modules\Tasks\Services;

use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Minimalni RRULE (RFC5545) podskup: FREQ (DAILY/WEEKLY/MONTHLY/YEARLY) + INTERVAL.
 * Dovoljno za "svaki dan/sedmicu/mjesec/godinu" iz UI-ja; može se proširiti
 * pravom RRULE bibliotekom bez mijenjanja pozivatelja.
 */
class RecurrenceService
{
    /** UI izbor ('daily'...) → RRULE string ili null. */
    public static function ruleFromChoice(?string $choice): ?string
    {
        return match ($choice) {
            'daily' => 'FREQ=DAILY',
            'weekly' => 'FREQ=WEEKLY',
            'monthly' => 'FREQ=MONTHLY',
            'yearly' => 'FREQ=YEARLY',
            default => null,
        };
    }

    /** RRULE string → UI izbor ('none'/'daily'...). */
    public static function choiceFromRule(?string $rule): string
    {
        return match (true) {
            $rule === null => 'none',
            str_contains($rule, 'FREQ=DAILY') => 'daily',
            str_contains($rule, 'FREQ=WEEKLY') => 'weekly',
            str_contains($rule, 'FREQ=MONTHLY') => 'monthly',
            str_contains($rule, 'FREQ=YEARLY') => 'yearly',
            default => 'none',
        };
    }

    public function nextDueDate(string $rule, CarbonInterface $from): ?Carbon
    {
        $parts = $this->parse($rule);
        $interval = max(1, (int) ($parts['INTERVAL'] ?? 1));
        $next = Carbon::instance($from)->copy();

        return match ($parts['FREQ'] ?? null) {
            'DAILY' => $next->addDays($interval),
            'WEEKLY' => $next->addWeeks($interval),
            'MONTHLY' => $next->addMonthsNoOverflow($interval),
            'YEARLY' => $next->addYears($interval),
            default => null,
        };
    }

    /**
     * @return array<string, string>
     */
    private function parse(string $rule): array
    {
        return collect(explode(';', $rule))
            ->mapWithKeys(function (string $part) {
                [$key, $value] = array_pad(explode('=', $part, 2), 2, '');

                return [strtoupper(trim($key)) => strtoupper(trim($value))];
            })
            ->all();
    }
}
