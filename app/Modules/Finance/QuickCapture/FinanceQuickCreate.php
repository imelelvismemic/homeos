<?php

namespace App\Modules\Finance\QuickCapture;

use App\Models\User;
use App\Modules\Finance\Enums\TransactionType;
use App\Modules\Finance\Models\Transaction;
use App\Platform\Models\Household;
use App\Platform\QuickCapture\QuickCreateContract;
use Illuminate\Support\Carbon;

class FinanceQuickCreate implements QuickCreateContract
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            // Datum kliknut na kalendaru (opciono).
            'date' => ['nullable', 'date'],
        ];
    }

    public function create(array $data, Household $household, User $user): void
    {
        // Brzi unos = trošak; detalji (kategorija, podjela) u resource-u. Datum je
        // onaj kliknut na kalendaru, a inače današnji.
        Transaction::create([
            'household_id' => $household->getKey(),
            'created_by' => $user->getKey(),
            'type' => TransactionType::Expense,
            'title' => $data['title'],
            'amount' => $data['amount'],
            'date' => isset($data['date']) ? Carbon::parse($data['date'])->toDateString() : now()->toDateString(),
        ]);
    }
}
