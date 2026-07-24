<?php

/**
 * Bosanski prijevodi validacijskih poruka (Laravel). Bez ovog fajla Laravel
 * pada nazad na engleski, pa se npr. na registraciji i obnovi šifre miješao
 * engleski tekst (CLAUDE.md §13 — sav korisnički tekst na bosanskom).
 */

return [

    'accepted' => 'Polje :attribute mora biti prihvaćeno.',
    'active_url' => 'Polje :attribute nije ispravan URL.',
    'after' => 'Polje :attribute mora biti datum nakon :date.',
    'after_or_equal' => 'Polje :attribute mora biti datum nakon ili jednak :date.',
    'alpha' => 'Polje :attribute smije sadržavati samo slova.',
    'alpha_dash' => 'Polje :attribute smije sadržavati samo slova, brojeve i crtice.',
    'alpha_num' => 'Polje :attribute smije sadržavati samo slova i brojeve.',
    'array' => 'Polje :attribute mora biti niz.',
    'before' => 'Polje :attribute mora biti datum prije :date.',
    'before_or_equal' => 'Polje :attribute mora biti datum prije ili jednak :date.',
    'between' => [
        'numeric' => 'Polje :attribute mora biti između :min i :max.',
        'file' => 'Polje :attribute mora biti između :min i :max kilobajta.',
        'string' => 'Polje :attribute mora imati između :min i :max znakova.',
        'array' => 'Polje :attribute mora imati između :min i :max stavki.',
    ],
    'boolean' => 'Polje :attribute mora biti tačno ili netačno.',
    'confirmed' => 'Potvrda polja :attribute se ne podudara.',
    'current_password' => 'Šifra nije ispravna.',
    'date' => 'Polje :attribute nije ispravan datum.',
    'date_equals' => 'Polje :attribute mora biti datum jednak :date.',
    'date_format' => 'Polje :attribute ne odgovara formatu :format.',
    'different' => 'Polja :attribute i :other moraju biti različita.',
    'digits' => 'Polje :attribute mora imati :digits cifara.',
    'digits_between' => 'Polje :attribute mora imati između :min i :max cifara.',
    'email' => 'Polje :attribute mora biti ispravna email adresa.',
    'ends_with' => 'Polje :attribute mora završavati sa jednim od: :values.',
    'exists' => 'Odabrana vrijednost za :attribute nije ispravna.',
    'file' => 'Polje :attribute mora biti datoteka.',
    'filled' => 'Polje :attribute mora imati vrijednost.',
    'gt' => [
        'numeric' => 'Polje :attribute mora biti veće od :value.',
        'string' => 'Polje :attribute mora imati više od :value znakova.',
    ],
    'gte' => [
        'numeric' => 'Polje :attribute mora biti veće ili jednako :value.',
        'string' => 'Polje :attribute mora imati najmanje :value znakova.',
    ],
    'image' => 'Polje :attribute mora biti slika.',
    'in' => 'Odabrana vrijednost za :attribute nije ispravna.',
    'integer' => 'Polje :attribute mora biti cijeli broj.',
    'ip' => 'Polje :attribute mora biti ispravna IP adresa.',
    'lt' => [
        'numeric' => 'Polje :attribute mora biti manje od :value.',
        'string' => 'Polje :attribute mora imati manje od :value znakova.',
    ],
    'lte' => [
        'numeric' => 'Polje :attribute mora biti manje ili jednako :value.',
        'string' => 'Polje :attribute smije imati najviše :value znakova.',
    ],
    'max' => [
        'numeric' => 'Polje :attribute ne smije biti veće od :max.',
        'file' => 'Polje :attribute ne smije biti veće od :max kilobajta.',
        'string' => 'Polje :attribute ne smije imati više od :max znakova.',
        'array' => 'Polje :attribute ne smije imati više od :max stavki.',
    ],
    'min' => [
        'numeric' => 'Polje :attribute mora biti najmanje :min.',
        'file' => 'Polje :attribute mora biti najmanje :min kilobajta.',
        'string' => 'Polje :attribute mora imati najmanje :min znakova.',
        'array' => 'Polje :attribute mora imati najmanje :min stavki.',
    ],
    'not_in' => 'Odabrana vrijednost za :attribute nije ispravna.',
    'numeric' => 'Polje :attribute mora biti broj.',
    'password' => 'Šifra nije ispravna.',
    'present' => 'Polje :attribute mora biti prisutno.',
    'regex' => 'Format polja :attribute nije ispravan.',
    'required' => 'Polje :attribute je obavezno.',
    'required_if' => 'Polje :attribute je obavezno kada je :other jednako :value.',
    'required_with' => 'Polje :attribute je obavezno kada je prisutno :values.',
    'same' => 'Polja :attribute i :other se moraju podudarati.',
    'size' => [
        'numeric' => 'Polje :attribute mora biti :size.',
        'file' => 'Polje :attribute mora biti :size kilobajta.',
        'string' => 'Polje :attribute mora imati :size znakova.',
        'array' => 'Polje :attribute mora sadržavati :size stavki.',
    ],
    'starts_with' => 'Polje :attribute mora počinjati sa jednim od: :values.',
    'string' => 'Polje :attribute mora biti tekst.',
    'unique' => 'Vrijednost za :attribute je već zauzeta.',
    'url' => 'Format polja :attribute nije ispravan.',

    // Nazivi polja koji se umeću u poruke iznad.
    'attributes' => [
        'name' => 'ime',
        'email' => 'email adresa',
        'password' => 'šifra',
        'passwordConfirmation' => 'potvrda šifre',
        'password_confirmation' => 'potvrda šifre',
        'title' => 'naslov',
    ],

    'custom' => [
        'password' => [
            'same' => 'Šifra i potvrda šifre se moraju podudarati.',
        ],
    ],

];
