<?php

return [

    'label' => 'Zadatak',
    'plural_label' => 'Zadaci',
    'navigation_label' => 'Zadaci',
    'navigation_group' => 'Organizacija',

    'fields' => [
        'title' => 'Naslov',
        'description' => 'Opis',
        'priority' => 'Prioritet',
        'status' => 'Status',
        'due_date' => 'Rok',
        'assigned_to' => 'Odgovorna osoba',
        'board' => 'Tabla',
        'parent' => 'Nadređeni zadatak',
        'tags' => 'Oznake',
        'recurrence' => 'Ponavljanje',
        'completed_at' => 'Završeno',
        'subtasks' => 'Podzadaci',
    ],

    'priority' => [
        'low' => 'Nizak',
        'medium' => 'Srednji',
        'high' => 'Visok',
    ],

    'status' => [
        'todo' => 'Za uraditi',
        'in_progress' => 'U toku',
        'done' => 'Završeno',
    ],

    'recurrence' => [
        'none' => 'Ne ponavlja se',
        'daily' => 'Dnevno',
        'weekly' => 'Sedmično',
        'monthly' => 'Mjesečno',
        'yearly' => 'Godišnje',
    ],

    'filters' => [
        'only_mine' => 'Samo meni dodijeljeni',
        'overdue' => 'Zakašnjeli',
        'hide_done' => 'Sakrij završene',
    ],

    'actions' => [
        'complete' => 'Označi završenim',
        'add_subtask' => 'Dodaj podzadatak',
    ],

    'empty' => [
        'heading' => 'Još nema zadataka',
        'description' => 'Dodajte prvi zadatak — rok, prioritet i odgovornu osobu. Pojaviće se i na kalendaru i na kanban tabli.',
    ],

    'widget' => [
        'heading' => 'Zadaci za danas',
        'overdue' => 'zakašnjelih',
        'due_today' => 'danas',
        'none' => 'Nema zadataka s rokom za danas. 🎉',
    ],

    'kanban' => [
        'title' => 'Kanban',
        'all_boards' => 'Sve table',
        'no_board' => 'Bez table',
        'new_board' => 'Nova tabla',
        'board_name' => 'Naziv table',
        'add_task' => 'Dodaj zadatak',
        'move_to' => 'Premjesti u',
        'empty_column' => 'Prevucite zadatak ovdje',
    ],

    'subtasks' => [
        'title' => 'Podzadaci',
        'empty' => 'Nema podzadataka. Razložite zadatak na manje korake.',
    ],

    'notifications' => [
        'due_soon' => [
            'subject' => 'Zadatak uskoro ističe',
            'line' => 'Zadatak ":title" ističe :when.',
            'action' => 'Otvori zadatak',
        ],
        'assigned' => [
            'subject' => 'Dodijeljen vam je zadatak',
            'line' => 'Dodijeljen vam je zadatak ":title".',
            'action' => 'Otvori zadatak',
        ],
    ],

    'quick_capture' => 'Novi zadatak',

    'calendar_type' => 'Zadatak s rokom',

];
