<?php

return [

    'label' => 'Note',
    'plural_label' => 'Notes',
    'navigation_label' => 'Notes',
    'navigation_group' => 'Organisation',
    'untitled' => 'Untitled',

    'fields' => [
        'title' => 'Title',
        'body' => 'Content',
        'body_help' => 'For documents and images use Administration → Documents; text goes here.',
        'journal_date' => 'Journal date',
        'journal_date_help' => 'Set a date to turn the note into a journal entry. Journal entries also appear on the calendar.',
        'tags' => 'Tags',
        'updated_at' => 'Last edited',
    ],

    'tabs' => [
        'all' => 'All notes',
        'journal' => 'Journal',
    ],

    'actions' => [
        'create' => 'Add note',
        'create_journal' => "Today's journal",
    ],

    'headings' => [
        'create' => 'Add note',
        'edit' => 'Edit note',
        'delete' => 'Delete note',
        'delete_description' => 'Delete the note ":title"? This cannot be undone.',
    ],

    'empty' => [
        'heading' => 'No notes yet',
        'description' => 'Write down an idea, an agreement or a journal entry — share it with the household or keep it private.',
    ],

    'widget' => [
        'heading' => 'Recent notes',
        'none' => 'No notes yet.',
    ],

    'quick_capture' => 'New note',

];
