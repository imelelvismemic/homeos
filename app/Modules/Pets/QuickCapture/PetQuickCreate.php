<?php

namespace App\Modules\Pets\QuickCapture;

use App\Models\User;
use App\Modules\Pets\Enums\PetSpecies;
use App\Modules\Pets\Models\Pet;
use App\Platform\Models\Household;
use App\Platform\QuickCapture\QuickCreateContract;

/**
 * Brzo dodavanje ljubimca: samo ime. Vrsta, datum rođenja i njega se dopunjuju
 * na formi — brzi unos mora ostati brz (CLAUDE.md §1, niska frikcija).
 */
class PetQuickCreate implements QuickCreateContract
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }

    public function create(array $data, Household $household, User $user): void
    {
        Pet::create([
            'household_id' => $household->getKey(),
            'created_by' => $user->getKey(),
            'name' => $data['name'],
            'species' => PetSpecies::Other,
        ]);
    }
}
