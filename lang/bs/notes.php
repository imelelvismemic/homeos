<?php

return [

    'label' => 'Bilješka',
    'plural_label' => 'Bilješke',
    'navigation_label' => 'Bilješke',
    'navigation_group' => 'Organizacija',
    'untitled' => 'Bez naslova',

    'fields' => [
        'title' => 'Naslov',
        'body' => 'Sadržaj',
        'body_help' => 'Za dokumente i slike koristite Administraciju → Dokumenti; ovdje ide tekst.',
        'journal_date' => 'Datum dnevnika',
        'journal_date_help' => 'Postavite datum da bilješka postane unos dnevnika. Unosi dnevnika se prikazuju i u kalendaru.',
        'tags' => 'Oznake',
        'updated_at' => 'Zadnja izmjena',
    ],

    'tabs' => [
        'all' => 'Sve bilješke',
        'journal' => 'Dnevnik',
    ],

    'actions' => [
        'create' => 'Dodaj bilješku',
        'create_journal' => 'Dnevnik za danas',
    ],

    'headings' => [
        'create' => 'Dodaj bilješku',
        'edit' => 'Uredi bilješku',
        'delete' => 'Brisanje bilješke',
        'delete_description' => 'Sigurno želite obrisati bilješku ":title"? Ova radnja je nepovratna.',
    ],

    'empty' => [
        'heading' => 'Još nema bilješki',
        'description' => 'Zapišite ideju, dogovor ili unos dnevnika — možete je dijeliti s domaćinstvom ili držati privatnom.',
    ],

    'widget' => [
        'heading' => 'Nedavne bilješke',
        'none' => 'Još nema bilješki.',
    ],

    'quick_capture' => 'Nova bilješka',

];
