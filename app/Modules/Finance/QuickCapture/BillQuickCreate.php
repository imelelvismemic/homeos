<?php

namespace App\Modules\Finance\QuickCapture;

use App\Models\User;
use App\Modules\Finance\Models\Bill;
use App\Platform\Models\Household;
use App\Platform\QuickCapture\QuickCreateContract;
use Illuminate\Support\Carbon;

/**
 * Brzo dodavanje računa: minimalno što račun mora imati (naziv, iznos, rok).
 * Kategorija, ponavljanje i broj dana za podsjetnik ostaju na formi računa —
 * ovdje se koristi podrazumijevanih 3 dana ranije, kao i na formi.
 *
 * Kreiranje računa emituje BillCreated → podsjetnik i email dolaze sami
 * (DoD Faze 5), pa brzi unos nije "manje vrijedan" zapis od onog s forme.
 */
class BillQuickCreate implements QuickCreateContract
{
    private const DEFAULT_REMIND_DAYS_BEFORE = 3;

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'due_date' => ['required', 'date'],
        ];
    }

    public function create(array $data, Household $household, User $user): void
    {
        Bill::create([
            'household_id' => $household->getKey(),
            'created_by' => $user->getKey(),
            'title' => $data['title'],
            'amount' => $data['amount'],
            'due_date' => Carbon::parse($data['due_date'])->toDateString(),
            'remind_days_before' => self::DEFAULT_REMIND_DAYS_BEFORE,
        ]);
    }
}
