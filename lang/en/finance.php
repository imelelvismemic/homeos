<?php

return [

    'navigation_group' => 'Finances',

    'type' => [
        'income' => 'Income',
        'expense' => 'Expense',
    ],

    'recurrence' => [
        'none' => 'Does not repeat',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
        'yearly' => 'Yearly',
    ],

    'reminder' => [
        'bill_due' => 'Bill due: :title (:amount)',
    ],

    'transactions' => [
        'label' => 'Transaction',
        'plural_label' => 'Transactions',
        'quick_capture' => 'New expense',
        'navigation_label' => 'Transactions',
        'fields' => [
            'type' => 'Type',
            'title' => 'Title',
            'amount' => 'Amount',
            'date' => 'Date',
            'category' => 'Category',
            'paid_by' => 'Paid by',
            'participants' => 'Split between members',
            'participants_help' => 'The expense is split equally between the selected members (for "who owes what").',
        ],
        'filters' => [
            'this_month' => 'This month',
        ],
        'actions' => ['create' => 'Add transaction'],
        'headings' => ['create' => 'Add transaction', 'edit' => 'Edit transaction'],
        'delete' => 'Delete transaction',
        'delete_description' => 'Delete the transaction ":title"? This cannot be undone.',
        'empty' => [
            'heading' => 'No transactions yet',
            'description' => 'Add an income or an expense — by category, split between members.',
        ],
    ],

    'bills' => [
        'label' => 'Bill',
        'plural_label' => 'Bills',
        'quick_capture' => 'New bill',
        'navigation_label' => 'Bills and subscriptions',
        'fields' => [
            'title' => 'Title',
            'amount' => 'Amount',
            'due_date' => 'Due date',
            'category' => 'Category',
            'remind_days_before' => 'Remind (days before)',
            'remind_days_before_help' => 'How many days before the due date we should remind you.',
            'recurrence' => 'Repeats',
            'paid' => 'Paid',
        ],
        'filters' => ['unpaid' => 'Unpaid only'],
        'actions' => [
            'create' => 'Add bill',
            'mark_paid' => 'Mark as paid',
        ],
        'headings' => ['create' => 'Add bill', 'edit' => 'Edit bill'],
        'delete' => 'Delete bill',
        'delete_description' => 'Delete the bill ":title"? This cannot be undone.',
        'empty' => [
            'heading' => 'No bills yet',
            'description' => 'Add a bill or subscription with a due date — we will remind you before it is due, and it shows up on the calendar.',
        ],
    ],

    'categories' => [
        'label' => 'Category',
        'plural_label' => 'Categories',
        'navigation_label' => 'Categories',
        'fields' => [
            'name' => 'Name',
            'color' => 'Colour',
            'transactions' => 'Transactions',
        ],
        'actions' => ['create' => 'Add category'],
        'headings' => ['create' => 'Add category', 'edit' => 'Edit category'],
        'delete' => 'Delete category',
        'delete_description' => 'Delete the category ":name"? Its transactions stay without a category.',
        'empty' => [
            'heading' => 'No categories yet',
            'description' => 'Add categories (for example Food, Utilities, Transport) to organise expenses and budgets.',
        ],
    ],

    'budgets' => [
        'label' => 'Budget',
        'plural_label' => 'Budgets',
        'navigation_label' => 'Budgets',
        'fields' => [
            'category' => 'Category',
            'month' => 'Month',
            'amount' => 'Amount',
        ],
        'actions' => ['create' => 'Add budget'],
        'headings' => ['create' => 'Add budget', 'edit' => 'Edit budget'],
        'delete' => 'Delete budget',
        'delete_description' => 'Delete the budget for ":category" (:month)? This cannot be undone.',
        'empty' => [
            'heading' => 'No budgets yet',
            'description' => 'Set a monthly budget per category — the monthly overview shows spending against the budget.',
        ],
    ],

    'overview' => [
        'title' => 'Monthly overview',
        'previous_month' => 'Previous month',
        'next_month' => 'Next month',
        'income' => 'Income',
        'expense' => 'Expense',
        'net' => 'Balance',
        'by_category' => 'By category against budget',
        'category' => 'Category',
        'spent' => 'Spent',
        'budget' => 'Budget',
        'remaining' => 'Remaining',
        'no_expenses' => 'No expenses this month.',
        'uncategorized' => 'No category',
        'who_owes' => 'Who owes whom',
        'no_balances' => 'No split expenses this month.',
        'is_owed' => 'is owed :amount',
        'owes' => 'owes :amount',
        'settled' => 'settled',
    ],

    'widget' => [
        'heading' => 'Unpaid bills',
        'none' => 'No unpaid bills. 💸',
    ],

];
