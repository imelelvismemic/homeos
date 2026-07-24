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
    ],

];
