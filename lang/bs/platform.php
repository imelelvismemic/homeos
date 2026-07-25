<?php

return [

    'household' => [
        'label' => 'Domaćinstvo',
        'plural_label' => 'Domaćinstva',
        'name' => 'Naziv domaćinstva',
        'name_placeholder' => 'npr. Memić domaćinstvo',
        'register_heading' => 'Kreirajte svoje domaćinstvo',
        'register_subheading' => 'Ovo je prostor u kojem ćete dijeliti zadatke, kalendar, bilješke i još mnogo toga sa članovima porodice.',
        'register_submit' => 'Kreiraj domaćinstvo',
    ],

    'members' => [
        'label' => 'Član',
        'plural_label' => 'Članovi',
        'navigation_label' => 'Članovi domaćinstva',
        'user' => 'Registrovani korisnik',
        'user_helper' => 'Unesite email adresu korisnika koji je već registrovan u sistemu.',
        'column_user' => 'Korisnik',
        'column_email' => 'E-mail adresa',
        'role' => 'Uloga',
        'role_owner' => 'Vlasnik',
        'role_member' => 'Član',
        'joined_at' => 'Datum pridruživanja',
        'invite_action' => 'Pozovi člana',
        'invite_modal_heading' => 'Pozovi člana u domaćinstvo',
        'invite_modal_submit' => 'Pozovi',
        'already_member' => 'Ovaj korisnik je već član ovog domaćinstva.',
        'user_not_found' => 'Nema registrovanog korisnika sa ovom email adresom. Korisnik se prvo mora registrovati u sistemu.',
        'empty_state_heading' => 'Još nema članova',
        'empty_state_description' => 'Pozovite prvog člana svog domaćinstva da biste počeli dijeliti obaveze.',
        'actions' => 'Radnje',
        'change_role' => 'Promijeni ulogu',
        'change_role_heading' => 'Promjena uloge člana',
        'change_role_submit' => 'Sačuvaj',
        'remove' => 'Ukloni',
        'remove_heading' => 'Uklanjanje člana',
        'remove_description' => 'Sigurno želite ukloniti člana ":name" iz domaćinstva? Gubi pristup dijeljenim podacima.',
        'transfer' => 'Prenesi vlasništvo',
        'transfer_heading' => 'Prijenos vlasništva',
        'transfer_description' => 'Vlasništvo nad domaćinstvom prelazi na ":name". Vi postajete običan član. Nastaviti?',
        'transfer_submit' => 'Prenesi vlasništvo',
        'transfer_target' => 'Novi vlasnik',
        'error_last_owner' => 'Domaćinstvo mora imati bar jednog vlasnika.',
        'error_self_remove' => 'Ne možete ukloniti sami sebe. Koristite prijenos vlasništva ili napuštanje.',
        'only_owner' => 'Samo vlasnik domaćinstva može uraditi ovu radnju.',
    ],

    'profile' => [
        'title' => 'Moj profil',
        'current_password' => 'Trenutna lozinka',
        'current_password_help' => 'Radi sigurnosti potvrdite lozinku kojom se sada prijavljujete.',
    ],

    'sharing' => [
        'action' => 'Podijeli',
        'modal_heading' => 'Privatnost i dijeljenje',
        'submit' => 'Sačuvaj',
        'visibility' => 'Vidljivost',
        'members' => 'Podijeli s članovima',
        'members_help' => 'Odaberite članove koji smiju vidjeti ovu stavku.',
    ],

    'dashboard' => [
        'title' => 'Danas',
        'greeting' => [
            'morning' => 'Dobro jutro, :name',
            'day' => 'Dobar dan, :name',
            'evening' => 'Dobro veče, :name',
        ],
        'summary_prefix' => 'Danas:',
        'empty_summary' => 'Danas nema ništa hitno — uživajte u mirnom danu kod kuće.',
        'no_widgets' => 'Još nema instaliranih aplikacija. Kako dodajete module (zadaci, kalendar, računi...), njihovi sažeci se pojavljuju ovdje.',
    ],

    'quick_capture' => [
        'button' => 'Brzo dodaj',
        'heading' => 'Šta želite dodati?',
        'close' => 'Zatvori',
        'back' => 'Nazad',
        'save' => 'Sačuvaj',
        'saved' => 'Sačuvano ✓',
        'error' => 'Greška. Pokušajte ponovo.',
        'empty' => 'Još nema opcija za brzo dodavanje. Kako instalirate module (zadaci, bilješke, računi...), pojavljuju se ovdje.',
    ],

    'visibility' => [
        'private' => 'Privatno',
        'household' => 'Cijelo domaćinstvo',
        'specific' => 'Određeni članovi',
    ],

    'notifications' => [
        'shared_with_you' => [
            'subject' => 'Nešto je podijeljeno sa vama',
            'line' => 'Podijeljeno je sa vama: :title',
        ],
        'categories' => [
            'task_assigned' => 'Dodijeljen zadatak',
            'task_due_soon' => 'Zadatak pred rokom',
            'reminder_fired' => 'Podsjetnik',
            'bill_due' => 'Račun pred dospijećem',
            'shared_with_you' => 'Nešto podijeljeno sa mnom',
        ],
    ],

    'settings' => [
        'navigation_label' => 'Obavještenja',
        'title' => 'Postavke obavještenja',
        'subheading' => 'Odaberite koja email obavještenja želite primati. Obavještenja u aplikaciji uvijek stižu.',
        'email_section' => 'Email obavještenja po kategoriji',
        'email_enabled' => 'Email',
        'category' => 'Kategorija',
        'digest_section' => 'Sažetak',
        'digest_help' => 'Povremeni email sa pregledom nadolazećeg (zadaci, računi, podsjetnici, istek dokumenata).',
        'digest_frequency' => 'Šalji sažetak',
        'saved' => 'Postavke su sačuvane.',
        'save' => 'Sačuvaj postavke',
    ],

    'inbox' => [
        'navigation_label' => 'Obavještenja',
        'title' => 'Obavještenja',
        'mark_read' => 'Pročitano',
        'mark_all_read' => 'Označi sve pročitanim',
        'show_read' => 'Prikaži i pročitane',
        'hide_read' => 'Sakrij pročitane',
        'empty_heading' => 'Nema obavještenja',
        'empty_description' => 'Ovdje stižu obavještenja iz aplikacije — dodijeljeni zadaci, podsjetnici, dijeljenje i drugo.',
        'empty_unread_heading' => 'Nema novih obavještenja',
        'empty_unread_description' => 'Sve ste pročitali. Kliknite „Prikaži i pročitane“ za raniju historiju.',
        'lines' => [
            'task_assigned' => 'Dodijeljen ti je zadatak „:title“',
            'task_due_soon' => 'Zadatak „:title“ uskoro ističe',
            'reminder_fired' => ':title',
            'bill_due' => ':title',
            'shared_with_you' => 'Podijeljeno je s tobom: :title',
        ],
    ],

    'digest' => [
        'frequency' => [
            'none' => 'Ne šalji',
            'daily' => 'Dnevno',
            'weekly' => 'Sedmično',
        ],
        'subject_daily' => 'Vaš dnevni sažetak — Home OS',
        'subject_weekly' => 'Vaš sedmični sažetak — Home OS',
        'greeting' => 'Zdravo, :name',
        'intro_daily' => 'Evo šta vas čeka danas i narednih dana:',
        'intro_weekly' => 'Evo šta vas čeka naredne sedmice:',
        'nothing' => 'Nema ništa hitno u ovom periodu — uživajte u miru.',
        'outro' => 'Ugodan dan želi vam Home OS.',
    ],

];
