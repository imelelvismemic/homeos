<?php

return [

    'label' => 'Ljubimac',
    'plural_label' => 'Kućni ljubimci',
    'navigation_label' => 'Ljubimci',
    'navigation_group' => 'Administracija',

    'fields' => [
        'name' => 'Ime',
        'species' => 'Vrsta',
        'birth_date' => 'Datum rođenja',
        'birth_date_help' => 'Opcionalno — koristi se za prikaz starosti.',
        'notes' => 'Bilješka',
        'care_count' => 'Broj termina njege',
    ],

    'species' => [
        'dog' => 'Pas',
        'cat' => 'Mačka',
        'bird' => 'Ptica',
        'fish' => 'Riba',
        'other' => 'Ostalo',
    ],

    'headings' => [
        'create' => 'Dodaj ljubimca',
        'edit' => 'Uredi ljubimca',
        'delete' => 'Brisanje ljubimca',
        'delete_description' => 'Sigurno želite obrisati ljubimca ":name"? Brišu se i svi termini njege. Ova radnja je nepovratna.',
    ],

    'empty' => [
        'heading' => 'Još nema ljubimaca',
        'description' => 'Dodajte ljubimca, pa mu upišite vakcine i preglede — na vrijeme ćemo vas podsjetiti.',
    ],

    'care' => [
        'title' => 'Njega i zdravlje',
        'create' => 'Dodaj termin njege',
        'complete' => 'Označi obavljenim',
        'delete' => 'Brisanje termina njege',
        'delete_description' => 'Sigurno želite obrisati ":title"? Ova radnja je nepovratna.',
        'empty' => 'Još nema termina njege',
        'empty_description' => 'Upišite vakcinu, pregled ili terapiju s datumom — podsjetnik i kalendar se pobrinu za ostalo.',
        'display_title' => ':type · :pet',
        'reminder' => 'Uskoro: :type za ljubimca :pet',

        'fields' => [
            'type' => 'Vrsta njege',
            'due_date' => 'Termin',
            'remind_days_before' => 'Podsjeti dana ranije',
            'remind_days_before_help' => 'Koliko dana prije termina želite podsjetnik.',
            'notes' => 'Bilješka',
            'status' => 'Status',
        ],

        'types' => [
            'vaccination' => 'Vakcina',
            'vet_visit' => 'Veterinarski pregled',
            'treatment' => 'Terapija',
            'grooming' => 'Njega dlake',
            'other' => 'Ostalo',
        ],

        'status' => [
            'planned' => 'Planirano',
            'done' => 'Obavljeno',
        ],
    ],

    'widget' => [
        'heading' => 'Njega ljubimaca',
        'none' => 'Nema termina njege u narednim danima. 🐾',
    ],

    'digest' => [
        'line' => ':title — :date',
    ],

    'quick_capture' => 'Novi ljubimac',

];
