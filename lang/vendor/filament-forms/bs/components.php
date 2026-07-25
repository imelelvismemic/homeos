<?php

// Dopuna nedostajućih ključeva iz vendor/filament/forms/resources/lang/bs/components.php.
// Paketski bs prijevod nema `file_upload` sekciju, pa je uređivač slike (profilna
// slika) ostajao na engleskom — docs/PRAVILA.md §1.
return [

    'text_input' => [

        'actions' => [

            'hide_password' => [
                'label' => 'Sakrij lozinku',
            ],

            'show_password' => [
                'label' => 'Prikaži lozinku',
            ],

        ],

    ],

    'file_upload' => [

        'editor' => [

            'actions' => [

                'cancel' => [
                    'label' => 'Zatvori',
                ],

                'drag_crop' => [
                    'label' => 'Način prevlačenja: isijecanje',
                ],

                'drag_move' => [
                    'label' => 'Način prevlačenja: pomjeranje',
                ],

                'flip_horizontal' => [
                    'label' => 'Prevrni sliku vodoravno',
                ],

                'flip_vertical' => [
                    'label' => 'Prevrni sliku uspravno',
                ],

                'move_down' => [
                    'label' => 'Pomjeri sliku dolje',
                ],

                'move_left' => [
                    'label' => 'Pomjeri sliku lijevo',
                ],

                'move_right' => [
                    'label' => 'Pomjeri sliku desno',
                ],

                'move_up' => [
                    'label' => 'Pomjeri sliku gore',
                ],

                'reset' => [
                    'label' => 'Poništi izmjene',
                ],

                'rotate_left' => [
                    'label' => 'Rotiraj ulijevo',
                ],

                'rotate_right' => [
                    'label' => 'Rotiraj udesno',
                ],

                'set_aspect_ratio' => [
                    'label' => 'Postavi omjer na :ratio',
                ],

                'save' => [
                    'label' => 'Sačuvaj',
                ],

                'zoom_100' => [
                    'label' => 'Uvećanje na 100%',
                ],

                'zoom_in' => [
                    'label' => 'Uvećaj',
                ],

                'zoom_out' => [
                    'label' => 'Umanji',
                ],

            ],

            'fields' => [

                'height' => [
                    'label' => 'Visina',
                    'unit' => 'px',
                ],

                'rotation' => [
                    'label' => 'Rotacija',
                    'unit' => '°',
                ],

                'width' => [
                    'label' => 'Širina',
                    'unit' => 'px',
                ],

                'x_position' => [
                    'label' => 'X',
                    'unit' => 'px',
                ],

                'y_position' => [
                    'label' => 'Y',
                    'unit' => 'px',
                ],

            ],

            'aspect_ratios' => [

                'label' => 'Omjeri stranica',

                'no_fixed' => [
                    'label' => 'Slobodno',
                ],

            ],

            'svg' => [

                'messages' => [
                    'confirmation' => 'Uređivanje SVG fajlova se ne preporučuje jer skaliranje može smanjiti kvalitet.\n Želite li nastaviti?',
                    'disabled' => 'Uređivanje SVG fajlova je onemogućeno jer skaliranje može smanjiti kvalitet.',
                ],

            ],

        ],

    ],

];
