<?php

namespace App\Platform\Calendar;

/**
 * Jedan događaj za kalendar (CLAUDE.md §5, DATA_MODEL.md §10). Vraća ga modul
 * kroz CalendarSourceContract; Kalendar ga renderuje (FullCalendar) bez znanja
 * odakle dolazi.
 */
class CalendarEvent
{
    public function __construct(
        public string $type,
        public int|string $id,
        public string $title,
        public string $start,
        public ?string $end = null,
        public ?string $url = null,
        public ?string $color = null,
        public bool $allDay = false,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toFullCalendar(): array
    {
        return array_filter([
            'id' => "{$this->type}-{$this->id}",
            'title' => $this->title,
            'start' => $this->start,
            'end' => $this->end,
            'url' => $this->url,
            'color' => $this->color,
            'allDay' => $this->allDay,
        ], fn ($v) => $v !== null);
    }
}
