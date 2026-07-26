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
        'settings_label' => 'Postavke domaćinstva',
        'currency' => 'Valuta',
        'currency_help' => 'Valuta u kojoj se prikazuju svi iznosi u domaćinstvu.',
    ],

    'members' => [
        'label' => 'Član',
        'plural_label' => 'Članovi',
        'navigation_label' => 'Članovi domaćinstva',
        'user' => 'Registrovani korisnik',
        'user_helper' => 'Unesite email adresu. Ako osoba već ima nalog, odmah postaje član; ako nema, dobija pozivnicu s linkom.',
        'added' => 'Član je dodan u domaćinstvo.',
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
        'account_section' => 'Podaci naloga',
        'avatar' => 'Profilna slika',
        'avatar_help' => 'Kvadratna slika izgleda najbolje. Za uklanjanje slike kliknite × na njoj, pa sačuvajte.',
        'name' => 'Ime i prezime',
        'email' => 'E-mail adresa',
        'password_section' => 'Promjena lozinke',
        'password_help' => 'Ostavite prazno ako ne mijenjate lozinku.',
        'new_password' => 'Nova lozinka',
        'new_password_confirmation' => 'Potvrdi novu lozinku',
        'current_password' => 'Trenutna lozinka',
        'current_password_help' => 'Radi sigurnosti potvrdite lozinku kojom se sada prijavljujete.',
        'save' => 'Sačuvaj',
        'saved' => 'Profil je sačuvan.',
    ],

    'invitations' => [
        'sent' => 'Pozivnica je poslana na :email.',
        'revoked' => 'Pozivnica je povučena.',
        'revoke' => 'Povuci',
        'pending_heading' => 'Poslane pozivnice',
        'pending_description' => 'Osobe koje su pozvane, a još nisu otvorile nalog. Pozivnica vrijedi 7 dana.',
        'expires_at' => 'Uloga: :role · vrijedi do :date',
        'invalid' => 'Pozivnica više ne vrijedi. Zatražite novu od vlasnika domaćinstva.',
        'wrong_account' => 'Ova pozivnica je poslana na drugu email adresu. Prijavite se s nalogom na koji je stigla.',
        'please_register' => 'Otvorite nalog da se pridružite domaćinstvu „:household“.',
        'please_log_in' => 'Prijavite se da se pridružite domaćinstvu „:household“.',
        'joined' => 'Pridružili ste se domaćinstvu „:household“.',
        'email_locked' => 'Email je preuzet iz pozivnice i ne može se mijenjati.',

        'mail' => [
            'subject' => 'Poziv u domaćinstvo :household',
            'greeting' => 'Zdravo,',
            'line' => ':inviter vas poziva u domaćinstvo „:household“ na Home OS-u.',
            'action' => 'Pridruži se domaćinstvu',
            'expires' => 'Link vrijedi još :days dana. Ako niste očekivali ovaj poziv, slobodno ga ignorišite.',
            'salutation' => 'Home OS',
        ],
    ],

    'backup' => [
        'failed_subject' => 'Home OS: noćni backup nije uspio',
        'failed_line' => 'Automatski backup baze i priloga nije uspio. Razlog:',
        'failed_hint' => 'Provjerite prostor na disku i log aplikacije. Backup se ponovo pokušava sutra u isto vrijeme.',
    ],

    'modules' => [
        'section' => 'Aplikacije',
        'description' => 'Isključena aplikacija nestaje iz menija, s početne strane, iz pretrage, kalendara i brzog dodavanja. Podaci ostaju sačuvani i vraćaju se čim je ponovo uključite.',
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
        // Dvije prazne situacije, dvije poruke: nema uključenih aplikacija vs.
        // aplikacije rade ali danas nemaju šta pokazati.
        'no_apps_heading' => 'Nema uključenih aplikacija',
        'no_apps' => 'U postavkama domaćinstva uključite aplikacije koje želite koristiti — njihovi sažeci se pojavljuju ovdje.',
        'nothing_yet_heading' => 'Danas nema šta prikazati',
        'nothing_yet' => 'Dodajte zadatak, podsjetnik ili račun — sažetak onoga što vas čeka pojaviće se ovdje.',
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
