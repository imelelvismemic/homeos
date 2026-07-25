<?php

return [

    'navigation_group' => 'Administracija',

    'reminder' => [
        'document_expiring' => 'Ističe rok: :title',
    ],

    'calendar' => [
        'expiry' => 'Ističe: :title',
    ],

    'widget' => [
        'heading' => 'Uskoro ističe',
        'none' => 'Nema dokumenata kojima uskoro ističe rok.',
    ],

    'documents' => [
        'label' => 'Dokument',
        'plural_label' => 'Dokumenti',
        'navigation_label' => 'Dokumenti',
        'headings' => ['create' => 'Dodaj dokument', 'edit' => 'Uredi dokument'],
        'delete' => 'Brisanje dokumenta',
        'delete_description' => 'Sigurno želite obrisati dokument ":title"? Prilog se briše zajedno sa zapisom. Ova radnja je nepovratna.',
        'types' => [
            'id_document' => 'Lična isprava',
            'warranty' => 'Garancija',
            'renewal' => 'Obnova/registracija',
            'contract' => 'Ugovor',
            'other' => 'Ostalo',
        ],
        'fields' => [
            'type' => 'Vrsta',
            'title' => 'Naziv',
            'expiry_date' => 'Datum isteka',
            'expiry_date_help' => 'Ostavite prazno ako dokument ne ističe. Za datum isteka se automatski pravi podsjetnik.',
            'remind_days_before' => 'Podsjeti dana ranije',
            'remind_days_before_help' => 'Koliko dana prije isteka da stigne podsjetnik.',
            'file' => 'Prilog',
            'file_help' => 'PDF ili slika (JPG/PNG), do 10 MB. Prilog je privatan i dostupan samo članovima domaćinstva.',
            'notes' => 'Napomena',
        ],
        'actions' => [
            'download' => 'Preuzmi',
        ],
        'empty' => [
            'heading' => 'Još nema dokumenata',
            'description' => 'Dodajte lične isprave, garancije, ugovore i obnove — za datume isteka automatski stižu podsjetnici.',
        ],
    ],

    'contacts' => [
        'label' => 'Kontakt',
        'plural_label' => 'Kontakti',
        'navigation_label' => 'Kontakti',
        'headings' => ['create' => 'Dodaj kontakt', 'edit' => 'Uredi kontakt'],
        'delete' => 'Brisanje kontakta',
        'delete_description' => 'Sigurno želite obrisati kontakt ":name"? Ova radnja je nepovratna.',
        'fields' => [
            'name' => 'Ime',
            'relationship' => 'Uloga',
            'relationship_help' => 'Npr. vodoinstalater, ljekar, komšija.',
            'phone' => 'Telefon',
            'email' => 'Email',
            'notes' => 'Napomena',
        ],
        'empty' => [
            'heading' => 'Još nema kontakata',
            'description' => 'Dodajte važne kontakte domaćinstva — majstore, ljekare, komšije.',
        ],
    ],

    'lists' => [
        'label' => 'Lista',
        'plural_label' => 'Liste za kupovinu',
        'navigation_label' => 'Kupovina',
        'headings' => ['create' => 'Dodaj listu', 'edit' => 'Uredi listu'],
        'delete' => 'Brisanje liste',
        'delete_description' => 'Sigurno želite obrisati listu ":name"? Sve stavke se brišu zajedno s njom. Ova radnja je nepovratna.',
        'fields' => [
            'name' => 'Naziv liste',
            'open_items' => 'Za kupiti',
        ],
        'empty' => [
            'heading' => 'Još nema listi',
            'description' => 'Napravite zajedničku listu za kupovinu — svi članovi domaćinstva je vide i štikliraju kupljeno.',
        ],
    ],

    'items' => [
        'label' => 'Stavka',
        'plural_label' => 'Stavke',
        'headings' => ['create' => 'Dodaj stavku', 'edit' => 'Uredi stavku'],
        'delete' => 'Brisanje stavke',
        'delete_description' => 'Sigurno želite obrisati stavku ":name"? Ova radnja je nepovratna.',
        'fields' => [
            'name' => 'Stavka',
            'is_done' => 'Kupljeno',
        ],
        'actions' => [
            'add' => 'Dodaj stavku',
        ],
        'empty' => [
            'heading' => 'Lista je prazna',
            'description' => 'Dodajte stavke koje treba kupiti.',
        ],
    ],

];
