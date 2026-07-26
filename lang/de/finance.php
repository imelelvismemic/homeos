<?php

return [

    'navigation_group' => 'Finanzen',

    'type' => [
        'income' => 'Einnahme',
        'expense' => 'Ausgabe',
    ],

    'recurrence' => [
        'none' => 'Keine Wiederholung',
        'weekly' => 'Wöchentlich',
        'monthly' => 'Monatlich',
        'yearly' => 'Jährlich',
    ],

    'reminder' => [
        'bill_due' => 'Rechnung fällig: :title (:amount)',
    ],

    'transactions' => [
        'label' => 'Buchung',
        'plural_label' => 'Buchungen',
        'quick_capture' => 'Neue Ausgabe',
        'navigation_label' => 'Buchungen',
        'fields' => [
            'type' => 'Art',
            'title' => 'Bezeichnung',
            'amount' => 'Betrag',
            'date' => 'Datum',
            'category' => 'Kategorie',
            'paid_by' => 'Bezahlt von',
            'participants' => 'Aufteilung unter Mitgliedern',
            'participants_help' => 'Die Ausgabe wird gleichmäßig unter den ausgewählten Mitgliedern aufgeteilt (für „wer schuldet wem“).',
        ],
        'filters' => [
            'this_month' => 'Dieser Monat',
        ],
        'actions' => ['create' => 'Buchung hinzufügen'],
        'headings' => ['create' => 'Buchung hinzufügen', 'edit' => 'Buchung bearbeiten'],
        'delete' => 'Buchung löschen',
        'delete_description' => 'Die Buchung „:title“ wirklich löschen? Das kann nicht rückgängig gemacht werden.',
        'empty' => [
            'heading' => 'Noch keine Buchungen',
            'description' => 'Fügen Sie eine Einnahme oder Ausgabe hinzu — nach Kategorie, mit Aufteilung unter den Mitgliedern.',
        ],
    ],

    'bills' => [
        'label' => 'Rechnung',
        'plural_label' => 'Rechnungen',
        'quick_capture' => 'Neue Rechnung',
        'navigation_label' => 'Rechnungen und Abos',
        'fields' => [
            'title' => 'Bezeichnung',
            'amount' => 'Betrag',
            'due_date' => 'Fällig am',
            'category' => 'Kategorie',
            'remind_days_before' => 'Erinnern (Tage vorher)',
            'remind_days_before_help' => 'Wie viele Tage vor der Fälligkeit wir Sie erinnern sollen.',
            'recurrence' => 'Wiederholung',
            'paid' => 'Bezahlt',
        ],
        'filters' => ['unpaid' => 'Nur unbezahlte'],
        'actions' => [
            'create' => 'Rechnung hinzufügen',
            'mark_paid' => 'Als bezahlt markieren',
        ],
        'headings' => ['create' => 'Rechnung hinzufügen', 'edit' => 'Rechnung bearbeiten'],
        'delete' => 'Rechnung löschen',
        'delete_description' => 'Die Rechnung „:title“ wirklich löschen? Das kann nicht rückgängig gemacht werden.',
        'empty' => [
            'heading' => 'Noch keine Rechnungen',
            'description' => 'Fügen Sie eine Rechnung oder ein Abo mit Frist hinzu — wir erinnern Sie vor der Fälligkeit, und sie erscheint im Kalender.',
        ],
    ],

    'categories' => [
        'label' => 'Kategorie',
        'plural_label' => 'Kategorien',
        'navigation_label' => 'Kategorien',
        'fields' => [
            'name' => 'Name',
            'color' => 'Farbe',
            'transactions' => 'Buchungen',
        ],
        'actions' => ['create' => 'Kategorie hinzufügen'],
        'headings' => ['create' => 'Kategorie hinzufügen', 'edit' => 'Kategorie bearbeiten'],
        'delete' => 'Kategorie löschen',
        'delete_description' => 'Die Kategorie „:name“ wirklich löschen? Die Buchungen bleiben ohne Kategorie.',
        'empty' => [
            'heading' => 'Noch keine Kategorien',
            'description' => 'Fügen Sie Kategorien hinzu (z. B. Lebensmittel, Nebenkosten, Verkehr), um Ausgaben und Budgets zu ordnen.',
        ],
    ],

    'budgets' => [
        'label' => 'Budget',
        'plural_label' => 'Budgets',
        'navigation_label' => 'Budgets',
        'fields' => [
            'category' => 'Kategorie',
            'month' => 'Monat',
            'amount' => 'Betrag',
        ],
        'actions' => ['create' => 'Budget hinzufügen'],
        'headings' => ['create' => 'Budget hinzufügen', 'edit' => 'Budget bearbeiten'],
        'delete' => 'Budget löschen',
        'delete_description' => 'Das Budget für „:category“ (:month) wirklich löschen? Das kann nicht rückgängig gemacht werden.',
        'empty' => [
            'heading' => 'Noch keine Budgets',
            'description' => 'Legen Sie ein Monatsbudget pro Kategorie fest — die Monatsübersicht zeigt Ausgaben im Vergleich zum Budget.',
        ],
    ],

    'overview' => [
        'title' => 'Monatsübersicht',
        'previous_month' => 'Vorheriger Monat',
        'next_month' => 'Nächster Monat',
        'income' => 'Einnahmen',
        'expense' => 'Ausgaben',
        'net' => 'Saldo',
        'by_category' => 'Nach Kategorie im Vergleich zum Budget',
        'category' => 'Kategorie',
        'spent' => 'Ausgegeben',
        'budget' => 'Budget',
        'remaining' => 'Verbleibend',
        'no_expenses' => 'Keine Ausgaben in diesem Monat.',
        'uncategorized' => 'Ohne Kategorie',
        'who_owes' => 'Wer schuldet wem',
        'no_balances' => 'Keine geteilten Ausgaben in diesem Monat.',
        'is_owed' => 'bekommt :amount zurück',
        'owes' => 'schuldet :amount',
        'settled' => 'ausgeglichen',
    ],

    'widget' => [
        'heading' => 'Unbezahlte Rechnungen',
        'none' => 'Keine unbezahlten Rechnungen. 💸',
    ],

];
