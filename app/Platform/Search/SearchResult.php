<?php

namespace App\Platform\Search;

/**
 * Jedan rezultat pretrage koji vraća SearchProviderContract (CLAUDE.md §8).
 */
class SearchResult
{
    public function __construct(
        public string $type,
        public int|string $id,
        public string $title,
        public ?string $url = null,
        public ?string $icon = null,
    ) {}
}
