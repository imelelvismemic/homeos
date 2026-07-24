<?php

namespace App\Platform\Enums;

/**
 * Vidljivost dijeljenog objekta (DATA_MODEL.md §2).
 */
enum Visibility: string
{
    case Private = 'private';
    case Household = 'household';
    case Specific = 'specific';

    public function label(): string
    {
        return __("platform.visibility.{$this->value}");
    }
}
