<?php

return [

    'label' => 'Pet',
    'plural_label' => 'Pets',
    'navigation_label' => 'Pets',
    'navigation_group' => 'Administration',

    'fields' => [
        'name' => 'Name',
        'species' => 'Species',
        'birth_date' => 'Date of birth',
        'birth_date_help' => 'Optional — used to show the age.',
        'notes' => 'Note',
        'care_count' => 'Care entries',
    ],

    'species' => [
        'dog' => 'Dog',
        'cat' => 'Cat',
        'bird' => 'Bird',
        'fish' => 'Fish',
        'other' => 'Other',
    ],

    'headings' => [
        'create' => 'Add pet',
        'edit' => 'Edit pet',
        'delete' => 'Delete pet',
        'delete_description' => 'Delete the pet ":name"? All care entries are deleted as well. This cannot be undone.',
    ],

    'empty' => [
        'heading' => 'No pets yet',
        'description' => 'Add a pet, then record vaccinations and check-ups — we will remind you in time.',
    ],

    'care' => [
        'title' => 'Care and health',
        'create' => 'Add care entry',
        'complete' => 'Mark as done',
        'delete' => 'Delete care entry',
        'delete_description' => 'Delete ":title"? This cannot be undone.',
        'empty' => 'No care entries yet',
        'empty_description' => 'Record a vaccination, check-up or treatment with a date — the reminder and calendar take care of the rest.',
        'display_title' => ':type · :pet',
        'reminder' => 'Coming up: :type for :pet',

        'fields' => [
            'type' => 'Care type',
            'due_date' => 'Date',
            'remind_days_before' => 'Remind days before',
            'remind_days_before_help' => 'How many days before the date you want the reminder.',
            'notes' => 'Note',
            'status' => 'Status',
        ],

        'types' => [
            'vaccination' => 'Vaccination',
            'vet_visit' => 'Vet visit',
            'treatment' => 'Treatment',
            'grooming' => 'Grooming',
            'other' => 'Other',
        ],

        'status' => [
            'planned' => 'Planned',
            'done' => 'Done',
        ],
    ],

    'widget' => [
        'heading' => 'Pet care',
        'none' => 'No care entries in the coming days. 🐾',
    ],

    'digest' => [
        'line' => ':title — :date',
    ],

    'quick_capture' => 'New pet',

];
