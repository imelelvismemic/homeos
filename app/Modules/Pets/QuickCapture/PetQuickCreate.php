<?php

namespace App\Modules\Pets\QuickCapture;

use App\Models\User;
use App\Modules\Pets\Enums\PetSpecies;
use App\Modules\Pets\Models\Pet;
use App\Platform\Models\Household;
use App\Platform\QuickCapture\QuickCreateContract;
use Illuminate\Validation\Rule;

/**
 * Brzo dodavanje ljubimca: ime i vrsta — oba su obavezna i na punoj formi, pa ih
 * traži i brzi unos. Datum rođenja, bilješka i termini njege se dopunjuju
 * kasnije: brzi unos mora ostati brz (CLAUDE.md §1, niska frikcija).
 */
class PetQuickCreate implements QuickCreateContract
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'species' => ['required', Rule::enum(PetSpecies::class)],
        ];
    }

    public function create(array $data, Household $household, User $user): void
    {
        Pet::create([
            'household_id' => $household->getKey(),
            'created_by' => $user->getKey(),
            'name' => $data['name'],
            'species' => $data['species'],
        ]);
    }
}
