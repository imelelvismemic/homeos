<?php

return [

    'navigation_group' => 'Verwaltung',

    'reminder' => [
        'document_expiring' => 'Läuft ab: :title',
    ],

    'calendar' => [
        'expiry' => 'Ablauf: :title',
    ],

    'digest' => [
        'line' => ':title läuft am :date ab',
    ],

    'widget' => [
        'heading' => 'Läuft bald ab',
        'none' => 'Keine Dokumente laufen demnächst ab.',
    ],

    'documents' => [
        'label' => 'Dokument',
        'plural_label' => 'Dokumente',
        'navigation_label' => 'Dokumente',
        'headings' => ['create' => 'Dokument hinzufügen', 'edit' => 'Dokument bearbeiten'],
        'delete' => 'Dokument löschen',
        'delete_description' => 'Das Dokument „:title“ wirklich löschen? Der Anhang wird zusammen mit dem Eintrag gelöscht. Das kann nicht rückgängig gemacht werden.',
        'types' => [
            'id_document' => 'Ausweisdokument',
            'warranty' => 'Garantie',
            'renewal' => 'Verlängerung/Zulassung',
            'contract' => 'Vertrag',
            'other' => 'Sonstiges',
        ],
        'fields' => [
            'type' => 'Art',
            'title' => 'Bezeichnung',
            'expiry_date' => 'Ablaufdatum',
            'expiry_date_help' => 'Leer lassen, wenn das Dokument nicht abläuft. Für das Ablaufdatum wird automatisch eine Erinnerung erstellt.',
            'remind_days_before' => 'Tage vorher erinnern',
            'remind_days_before_help' => 'Wie viele Tage vor dem Ablauf die Erinnerung kommen soll.',
            'file' => 'Anhang',
            'file_help' => 'PDF oder Bild (JPG/PNG), bis 10 MB. Der Anhang ist privat und nur für Haushaltsmitglieder zugänglich.',
            'notes' => 'Notiz',
        ],
        'actions' => [
            'download' => 'Herunterladen',
        ],
        'empty' => [
            'heading' => 'Noch keine Dokumente',
            'description' => 'Fügen Sie Ausweise, Garantien, Verträge und Verlängerungen hinzu — für Ablaufdaten kommen die Erinnerungen automatisch.',
        ],
    ],

    'contacts' => [
        'label' => 'Kontakt',
        'plural_label' => 'Kontakte',
        'navigation_label' => 'Kontakte',
        'headings' => ['create' => 'Kontakt hinzufügen', 'edit' => 'Kontakt bearbeiten'],
        'delete' => 'Kontakt löschen',
        'delete_description' => 'Den Kontakt „:name“ wirklich löschen? Das kann nicht rückgängig gemacht werden.',
        'fields' => [
            'name' => 'Name',
            'relationship' => 'Rolle',
            'relationship_help' => 'Zum Beispiel Installateur, Arzt, Nachbar.',
            'phone' => 'Telefon',
            'email' => 'E-Mail',
            'notes' => 'Notiz',
        ],
        'empty' => [
            'heading' => 'Noch keine Kontakte',
            'description' => 'Fügen Sie die wichtigen Kontakte des Haushalts hinzu — Handwerker, Ärzte, Nachbarn.',
        ],
    ],

    'lists' => [
        'label' => 'Liste',
        'plural_label' => 'Einkaufslisten',
        'navigation_label' => 'Einkauf',
        'headings' => ['create' => 'Liste hinzufügen', 'edit' => 'Liste bearbeiten'],
        'delete' => 'Liste löschen',
        'delete_description' => 'Die Liste „:name“ wirklich löschen? Alle Positionen werden mitgelöscht. Das kann nicht rückgängig gemacht werden.',
        'fields' => [
            'name' => 'Name der Liste',
            'open_items' => 'Einzukaufen',
        ],
        'empty' => [
            'heading' => 'Noch keine Listen',
            'description' => 'Erstellen Sie eine gemeinsame Einkaufsliste — alle Haushaltsmitglieder sehen sie und haken Gekauftes ab.',
        ],
    ],

    'items' => [
        'label' => 'Position',
        'plural_label' => 'Positionen',
        'headings' => ['create' => 'Position hinzufügen', 'edit' => 'Position bearbeiten'],
        'delete' => 'Position löschen',
        'delete_description' => 'Die Position „:name“ wirklich löschen? Das kann nicht rückgängig gemacht werden.',
        'fields' => [
            'name' => 'Position',
            'is_done' => 'Gekauft',
        ],
        'actions' => [
            'add' => 'Position hinzufügen',
        ],
        'empty' => [
            'heading' => 'Die Liste ist leer',
            'description' => 'Fügen Sie hinzu, was eingekauft werden soll.',
        ],
    ],

];
