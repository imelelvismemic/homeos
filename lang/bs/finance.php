<?php

return [

    'navigation_group' => 'Finansije',

    'type' => [
        'income' => 'Prihod',
        'expense' => 'Rashod',
    ],

    'recurrence' => [
        'none' => 'Ne ponavlja se',
        'weekly' => 'Sedmično',
        'monthly' => 'Mjesečno',
        'yearly' => 'Godišnje',
    ],

    'reminder' => [
        'bill_due' => 'Račun dospijeva: :title (:amount KM)',
    ],

    'transactions' => [
        'label' => 'Transakcija',
        'plural_label' => 'Transakcije',
        'navigation_label' => 'Transakcije',
        'fields' => [
            'type' => 'Vrsta',
            'title' => 'Naziv',
            'amount' => 'Iznos',
            'date' => 'Datum',
            'category' => 'Kategorija',
            'paid_by' => 'Platio/la',
            'participants' => 'Podjela među članovima',
            'participants_help' => 'Trošak se dijeli jednako među odabranim članovima (za "ko duguje").',
        ],
        'filters' => [
            'this_month' => 'Ovaj mjesec',
        ],
        'actions' => ['create' => 'Dodaj transakciju'],
        'delete' => 'Brisanje transakcije',
        'delete_description' => 'Sigurno želite obrisati transakciju ":title"? Ova radnja je nepovratna.',
        'empty' => [
            'heading' => 'Još nema transakcija',
            'description' => 'Dodajte prihod ili rashod — po kategorijama, s podjelom među članovima.',
        ],
    ],

    'bills' => [
        'label' => 'Račun',
        'plural_label' => 'Računi',
        'navigation_label' => 'Računi i pretplate',
        'fields' => [
            'title' => 'Naziv',
            'amount' => 'Iznos',
            'due_date' => 'Dospijeće',
            'category' => 'Kategorija',
            'remind_days_before' => 'Podsjeti (dana prije)',
            'remind_days_before_help' => 'Koliko dana prije dospijeća da vas podsjetimo.',
            'recurrence' => 'Ponavljanje',
            'paid' => 'Plaćeno',
        ],
        'filters' => ['unpaid' => 'Samo neplaćeni'],
        'actions' => [
            'create' => 'Dodaj račun',
            'mark_paid' => 'Označi plaćenim',
        ],
        'delete' => 'Brisanje računa',
        'delete_description' => 'Sigurno želite obrisati račun ":title"? Ova radnja je nepovratna.',
        'empty' => [
            'heading' => 'Još nema računa',
            'description' => 'Dodajte račun ili pretplatu s rokom — podsjetićemo vas prije dospijeća, i pojaviće se na kalendaru.',
        ],
    ],

    'categories' => [
        'label' => 'Kategorija',
        'plural_label' => 'Kategorije',
        'navigation_label' => 'Kategorije',
        'fields' => [
            'name' => 'Naziv',
            'color' => 'Boja',
            'transactions' => 'Transakcija',
        ],
        'actions' => ['create' => 'Dodaj kategoriju'],
        'delete' => 'Brisanje kategorije',
        'delete_description' => 'Sigurno želite obrisati kategoriju ":name"? Transakcije ostaju bez kategorije.',
        'empty' => [
            'heading' => 'Još nema kategorija',
            'description' => 'Dodajte kategorije (npr. Hrana, Režije, Prevoz) za organizaciju troškova i budžeta.',
        ],
    ],

    'budgets' => [
        'label' => 'Budžet',
        'plural_label' => 'Budžeti',
        'navigation_label' => 'Budžeti',
        'fields' => [
            'category' => 'Kategorija',
            'month' => 'Mjesec',
            'amount' => 'Iznos',
        ],
        'actions' => ['create' => 'Dodaj budžet'],
        'empty' => [
            'heading' => 'Još nema budžeta',
            'description' => 'Postavite mjesečni budžet po kategoriji — mjesečni pregled pokazuje potrošeno naspram budžeta.',
        ],
    ],

    'overview' => [
        'title' => 'Mjesečni pregled',
        'income' => 'Prihod',
        'expense' => 'Rashod',
        'net' => 'Saldo',
        'by_category' => 'Po kategoriji naspram budžeta',
        'category' => 'Kategorija',
        'spent' => 'Potrošeno',
        'budget' => 'Budžet',
        'remaining' => 'Preostalo',
        'no_expenses' => 'Nema troškova ovaj mjesec.',
        'uncategorized' => 'Bez kategorije',
        'who_owes' => 'Ko duguje kome',
        'no_balances' => 'Nema podijeljenih troškova ovaj mjesec.',
        'is_owed' => 'treba mu se vratiti :amount',
        'owes' => 'duguje :amount',
        'settled' => 'poravnato',
    ],

    'widget' => [
        'heading' => 'Neplaćeni računi',
        'none' => 'Nema neplaćenih računa. 💸',
    ],

];
