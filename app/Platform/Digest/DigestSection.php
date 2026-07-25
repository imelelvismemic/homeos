<?php

namespace App\Platform\Digest;

/**
 * Jedna sekcija digest emaila koju vraća modul (npr. "Zadaci s rokom"). Modul ne
 * zna kako se digest renderuje — samo vrati naslov i linije. Core (DigestService)
 * ih agregira.
 */
class DigestSection
{
    /**
     * @param  array<int, string>  $lines
     */
    public function __construct(
        public string $title,
        public array $lines,
    ) {}

    public function isEmpty(): bool
    {
        return $this->lines === [];
    }
}
