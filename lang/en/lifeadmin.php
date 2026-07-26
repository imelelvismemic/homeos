<?php

return [

    'navigation_group' => 'Administration',

    'reminder' => [
        'document_expiring' => 'Expiring: :title',
    ],

    'calendar' => [
        'expiry' => 'Expires: :title',
    ],

    'digest' => [
        'line' => ':title expires :date',
    ],

    'widget' => [
        'heading' => 'Expiring soon',
        'none' => 'No documents are expiring soon.',
    ],

    'documents' => [
        'label' => 'Document',
        'plural_label' => 'Documents',
        'navigation_label' => 'Documents',
        'headings' => ['create' => 'Add document', 'edit' => 'Edit document'],
        'delete' => 'Delete document',
        'delete_description' => 'Delete the document ":title"? The attachment is deleted together with the record. This cannot be undone.',
        'types' => [
            'id_document' => 'ID document',
            'warranty' => 'Warranty',
            'renewal' => 'Renewal/registration',
            'contract' => 'Contract',
            'other' => 'Other',
        ],
        'fields' => [
            'type' => 'Type',
            'title' => 'Title',
            'expiry_date' => 'Expiry date',
            'expiry_date_help' => 'Leave empty if the document does not expire. A reminder is created automatically for the expiry date.',
            'remind_days_before' => 'Remind days before',
            'remind_days_before_help' => 'How many days before the expiry the reminder should arrive.',
            'file' => 'Attachment',
            'file_help' => 'PDF or image (JPG/PNG), up to 10 MB. The attachment is private and available only to household members.',
            'notes' => 'Note',
        ],
        'actions' => [
            'download' => 'Download',
        ],
        'empty' => [
            'heading' => 'No documents yet',
            'description' => 'Add ID documents, warranties, contracts and renewals — reminders arrive automatically for expiry dates.',
        ],
    ],

    'contacts' => [
        'label' => 'Contact',
        'plural_label' => 'Contacts',
        'navigation_label' => 'Contacts',
        'headings' => ['create' => 'Add contact', 'edit' => 'Edit contact'],
        'delete' => 'Delete contact',
        'delete_description' => 'Delete the contact ":name"? This cannot be undone.',
        'fields' => [
            'name' => 'Name',
            'relationship' => 'Role',
            'relationship_help' => 'For example plumber, doctor, neighbour.',
            'phone' => 'Phone',
            'email' => 'Email',
            'notes' => 'Note',
        ],
        'empty' => [
            'heading' => 'No contacts yet',
            'description' => 'Add the household’s important contacts — tradespeople, doctors, neighbours.',
        ],
    ],

    'lists' => [
        'label' => 'List',
        'plural_label' => 'Shopping lists',
        'navigation_label' => 'Shopping',
        'headings' => ['create' => 'Add list', 'edit' => 'Edit list'],
        'delete' => 'Delete list',
        'delete_description' => 'Delete the list ":name"? All items are deleted with it. This cannot be undone.',
        'fields' => [
            'name' => 'List name',
            'open_items' => 'To buy',
        ],
        'empty' => [
            'heading' => 'No lists yet',
            'description' => 'Create a shared shopping list — every household member sees it and ticks off what is bought.',
        ],
    ],

    'items' => [
        'label' => 'Item',
        'plural_label' => 'Items',
        'headings' => ['create' => 'Add item', 'edit' => 'Edit item'],
        'delete' => 'Delete item',
        'delete_description' => 'Delete the item ":name"? This cannot be undone.',
        'fields' => [
            'name' => 'Item',
            'is_done' => 'Bought',
        ],
        'actions' => [
            'add' => 'Add item',
        ],
        'empty' => [
            'heading' => 'The list is empty',
            'description' => 'Add the items you need to buy.',
        ],
    ],

];
