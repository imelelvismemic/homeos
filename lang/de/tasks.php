<?php

return [

    'label' => 'Aufgabe',
    'plural_label' => 'Aufgaben',
    'navigation_label' => 'Aufgaben',
    'navigation_group' => 'Organisation',

    'fields' => [
        'title' => 'Titel',
        'description' => 'Beschreibung',
        'priority' => 'Priorität',
        'status' => 'Status',
        'due_date' => 'Fällig am',
        'due_date_now' => 'Jetzt',
        'assigned_to' => 'Zuständige Person',
        'board' => 'Board',
        'parent' => 'Übergeordnete Aufgabe',
        'tags' => 'Schlagwörter',
        'recurrence' => 'Wiederholung',
        'completed_at' => 'Erledigt',
        'subtasks' => 'Teilaufgaben',
    ],

    'headings' => [
        'create' => 'Aufgabe hinzufügen',
        'edit' => 'Aufgabe bearbeiten',
        'delete' => 'Aufgabe löschen',
        'delete_description' => 'Die Aufgabe „:title“ wirklich löschen? Das kann nicht rückgängig gemacht werden.',
    ],

    'priority' => [
        'low' => 'Niedrig',
        'medium' => 'Mittel',
        'high' => 'Hoch',
    ],

    'status' => [
        'todo' => 'Zu erledigen',
        'in_progress' => 'In Arbeit',
        'done' => 'Erledigt',
    ],

    'recurrence' => [
        'none' => 'Keine Wiederholung',
        'daily' => 'Täglich',
        'weekly' => 'Wöchentlich',
        'monthly' => 'Monatlich',
        'yearly' => 'Jährlich',
    ],

    'filters' => [
        'only_mine' => 'Mir zugewiesen',
        'overdue' => 'Überfällig',
        'hide_done' => 'Erledigte ausblenden',
    ],

    'actions' => [
        'create' => 'Aufgabe hinzufügen',
        'complete' => 'Als erledigt markieren',
        'add_subtask' => 'Teilaufgabe hinzufügen',
        'remind' => 'Erinnere mich',
        'add_note' => 'Notiz hinzufügen',
    ],

    'remind' => [
        'when' => 'Wann sollen wir Sie erinnern?',
        'title' => 'Erinnerung: :title',
    ],

    'note' => [
        'body' => 'Notiztext',
        'title' => 'Notiz zur Aufgabe: :title',
    ],

    'empty' => [
        'heading' => 'Noch keine Aufgaben',
        'description' => 'Fügen Sie die erste Aufgabe hinzu — mit Fälligkeit, Priorität und zuständiger Person. Sie erscheint auch im Kalender und auf dem Kanban-Board.',
    ],

    'widget' => [
        'heading' => 'Aufgaben für heute',
        'overdue' => 'überfällig',
        'due_today' => 'heute',
        'none' => 'Heute ist nichts fällig. 🎉',
    ],

    'kanban' => [
        'title' => 'Kanban',
        'all_boards' => 'Alle Boards',
        'no_board' => 'Ohne Board',
        'new_board' => 'Neues Board',
        'board_name' => 'Board-Name',
        'add_task' => 'Aufgabe hinzufügen',
        'move_to' => 'Verschieben nach',
        'empty_column' => 'Aufgabe hierher ziehen',
    ],

    'subtasks' => [
        'title' => 'Teilaufgaben',
        'create' => 'Teilaufgabe erstellen',
        'empty' => 'Noch keine Teilaufgaben',
        'empty_description' => 'Zerlegen Sie die Aufgabe in kleinere Schritte.',
    ],

    'notifications' => [
        'due_soon' => [
            'subject' => 'Aufgabe wird bald fällig',
            'line' => 'Die Aufgabe „:title“ ist :when fällig.',
            'action' => 'Aufgabe öffnen',
        ],
        'assigned' => [
            'subject' => 'Ihnen wurde eine Aufgabe zugewiesen',
            'line' => 'Ihnen wurde die Aufgabe „:title“ zugewiesen.',
            'action' => 'Aufgabe öffnen',
        ],
    ],

    'quick_capture' => 'Neue Aufgabe',

    'calendar_type' => 'Aufgabe mit Fälligkeit',

];
