<?php

return [
    'required' => ':attribute is verplicht.',
    'email' => ':attribute moet een geldig e-mailadres zijn.',
    'url' => ':attribute moet een geldige URL zijn.',
    'string' => ':attribute moet tekst zijn.',
    'max' => [
        'string' => ':attribute mag maximaal :max tekens bevatten.',
        'file' => ':attribute mag niet groter zijn dan :max kilobytes.',
    ],
    'min' => ['string' => ':attribute moet minimaal :min tekens bevatten.'],
    'same' => ':attribute moet overeenkomen.',
    'in' => 'De gekozen waarde voor :attribute is ongeldig.',
    'file' => ':attribute moet een bestand zijn.',
    'mimes' => ':attribute moet een bestand zijn van het type: :values.',
    'attributes' => [
        'email' => 'e-mailadres',
        'login' => 'gebruikersnaam of e-mailadres',
        'username' => 'gebruikersnaam',
        'password' => 'wachtwoord',
        'password_confirmation' => 'wachtwoordbevestiging',
        'title' => 'naam',
        'work_process' => 'werkproces',
        'category' => 'categorie',
        'file' => 'bestand',
        'status' => 'status',
    ],
];
