<?php

namespace App\Platform\Enums;

/**
 * Koliko često član prima digest email (Faza 6). Opt-in — default None.
 */
enum DigestFrequency: string
{
    case None = 'none';
    case Daily = 'daily';
    case Weekly = 'weekly';

    public function label(): string
    {
        return __("platform.digest.frequency.{$this->value}");
    }

    /** Prozor koji digest pokriva (koliko unaprijed gleda). */
    public function windowDays(): int
    {
        return match ($this) {
            self::Daily => 1,
            self::Weekly => 7,
            self::None => 0,
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $f) => [$f->value => $f->label()])
            ->all();
    }
}
