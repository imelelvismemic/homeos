<?php

return [

    'label' => 'Task',
    'plural_label' => 'Tasks',
    'navigation_label' => 'Tasks',
    'navigation_group' => 'Organisation',

    'fields' => [
        'title' => 'Title',
        'description' => 'Description',
        'priority' => 'Priority',
        'status' => 'Status',
        'due_date' => 'Due date',
        'due_date_now' => 'Now',
        'assigned_to' => 'Assignee',
        'board' => 'Board',
        'parent' => 'Parent task',
        'tags' => 'Tags',
        'recurrence' => 'Repeats',
        'completed_at' => 'Completed',
        'subtasks' => 'Subtasks',
    ],

    'headings' => [
        'create' => 'Add task',
        'edit' => 'Edit task',
        'delete' => 'Delete task',
        'delete_description' => 'Delete the task ":title"? This cannot be undone.',
    ],

    'priority' => [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
    ],

    'status' => [
        'todo' => 'To do',
        'in_progress' => 'In progress',
        'done' => 'Done',
    ],

    'recurrence' => [
        'none' => 'Does not repeat',
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
        'yearly' => 'Yearly',
    ],

    'filters' => [
        'only_mine' => 'Assigned to me',
        'overdue' => 'Overdue',
        'hide_done' => 'Hide completed',
    ],

    'actions' => [
        'create' => 'Add task',
        'complete' => 'Mark as done',
        'add_subtask' => 'Add subtask',
        'remind' => 'Remind me',
        'add_note' => 'Add note',
    ],

    'remind' => [
        'when' => 'When should we remind you?',
        'title' => 'Reminder: :title',
    ],

    'note' => [
        'body' => 'Note text',
        'title' => 'Note on task: :title',
    ],

    'empty' => [
        'heading' => 'No tasks yet',
        'description' => 'Add your first task — due date, priority and assignee. It shows up on the calendar and the kanban board too.',
    ],

    'widget' => [
        'heading' => 'Tasks for today',
        'overdue' => 'overdue',
        'due_today' => 'today',
        'none' => 'Nothing due today. 🎉',
    ],

    'kanban' => [
        'title' => 'Kanban',
        'all_boards' => 'All boards',
        'no_board' => 'No board',
        'new_board' => 'New board',
        'board_name' => 'Board name',
        'add_task' => 'Add task',
        'move_to' => 'Move to',
        'empty_column' => 'Drag a task here',
    ],

    'subtasks' => [
        'title' => 'Subtasks',
        'create' => 'Create subtask',
        'empty' => 'No subtasks yet',
        'empty_description' => 'Break the task down into smaller steps.',
    ],

    'notifications' => [
        'due_soon' => [
            'subject' => 'Task due soon',
            'line' => 'The task ":title" is due :when.',
            'action' => 'Open task',
        ],
        'assigned' => [
            'subject' => 'A task was assigned to you',
            'line' => 'You have been assigned the task ":title".',
            'action' => 'Open task',
        ],
    ],

    'quick_capture' => 'New task',

    'calendar_type' => 'Task with a due date',

];
