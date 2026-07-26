<?php

return [

    'label' => 'Reminder',
    'plural_label' => 'Reminders',
    'navigation_label' => 'Reminders',
    'navigation_group' => 'Organisation',

    'fields' => [
        'title' => 'Title',
        'description' => 'Description',
        'due_date' => 'Time',
        'due_date_now' => 'Now',
        'recurrence' => 'Repeats',
        'assigned_to' => 'Assignee',
        'assigned_to_help' => 'Who the reminder is for. Leave empty and it goes to you.',
        'status' => 'Status',
    ],

    'recurrence' => [
        'none' => 'Does not repeat',
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
        'yearly' => 'Yearly',
    ],

    'status' => [
        'pending' => 'Active',
        'done' => 'Fired',
    ],

    'filters' => [
        'hide_done' => 'Hide fired',
    ],

    'actions' => [
        'create' => 'Add reminder',
        'complete' => 'Mark as fired',
        'completed_notice' => 'The reminder fired — the notification has been sent.',
    ],

    'headings' => [
        'create' => 'Add reminder',
        'edit' => 'Edit reminder',
        'delete' => 'Delete reminder',
        'delete_description' => 'Delete the reminder ":title"? This cannot be undone.',
    ],

    'empty' => [
        'heading' => 'No reminders yet',
        'description' => 'Add a reminder with a time — we will notify you when it comes. It shows up on the calendar too.',
    ],

    'widget' => [
        'heading' => 'Reminders for today',
        'none' => 'No reminders for today. 🔔',
    ],

    'notifications' => [
        'due' => [
            'subject' => 'Reminder',
            'line' => 'Reminder: ":title".',
            'action' => 'Open reminder',
        ],
    ],

    'quick_capture' => 'New reminder',

    'calendar_type' => 'Reminder',

];
