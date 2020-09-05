<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attribute må aksepteres.',
    'active_url' => ':attribute er ikke en gyldig URL.',
    'after' => ':attribute må være en dato etter :date.',
    'after_or_equal' => ':attribute må være en dato etter eller lik :date.',
    'alpha' => ':attribute kan bare inneholde bokstaver.',
    'alpha_dash' => ':attribute kan bare inneholde bokstaver, tall, streker og understrek.',
    'alpha_num' => ':attribute kan bare inneholde bokstaver og tall.',
    'array' => ':attribute må være en matrise.',
    'before' => ':attribute må være en dato før :date.',
    'before_or_equal' => ':attribute må være en dato før eller lik :date.',
    'between' => [
        'numeric' => ':attribute må være mellom :min og :max.',
        'file' => ':attribute må være mellom :min og :max kilobyte.',
        'string' => ':attribute må være mellom :min og :max tegn.',
        'array' => ':attribute må være mellom :min og :max elementer.',
    ],
    'boolean' => ':attribute feltet må være sant eller usant.',
    'confirmed' => ':attribute bekreftelsen stemmer ikke.',
    'date' => ':attribute er ikke en gyldig dato.',
    'date_equals' => ':attribute må være en dato lik :date.',
    'date_format' => ':attribute samsvarer ikke med formatet :format.',
    'different' => ':attribute og :other må være forskjellige.',
    'digits' => ':attribute må være :digits sifre.',
    'digits_between' => ':attribute må være mellom :min og :max digits.',
    'dimensions' => ':attribute har ugyldige bildedimensjoner.',
    'distinct' => ':attribute feltet har en duplikatverdi.',
    'email' => ':attribute må være en gyldig e-postadresse.',
    'ends_with' => ':attribute må slutte med ett av følgende: :values.',
    'exists' => 'Den valgte :attribute er ugyldig.',
    'file' => ':attribute må være en fil.',
    'filled' => ':attribute feltet må ha en verdi.',
    'gt' => [
        'numeric' => ':attribute må være større enn :value.',
        'file' => ':attribute må være større enn :value kilobyte.',
        'string' => ':attribute må være større enn :value tegn.',
        'array' => ':attribute må ha mer enn :value elementer.',
    ],
    'gte' => [
        'numeric' => ':attribute må være større enn eller lik :value.',
        'file' => ':attribute må være større enn eller lik :value kilobytes.',
        'string' => ':attribute må være større enn eller lik :value characters.',
        'array' => ':attribute må ha :value varer eller mer.',
    ],
    'image' => ':attribute må være et bilde.',
    'in' => 'Den valgte :attribute er ugyldig.',
    'in_array' => ':attribute feltet eksisterer ikke i :other.',
    'integer' => ':attribute må være et helt tall.',
    'ip' => ':attribute må være en gyldig IP-adresse.',
    'ipv4' => ':attribute må være en gyldig IPv4-adresse.',
    'ipv6' => ':attribute må være en gyldig IPv6-adresse.',
    'json' => ':attribute må være en gyldig JSON-streng.',
    'lt' => [
        'numeric' => ':attribute må være mindre enn :value.',
        'file' => ':attribute må være mindre enn :value kilobyte.',
        'string' => ':attribute må være mindre enn :value tegn.',
        'array' => ':attribute må ha mindre enn :value elementer.',
    ],
    'lte' => [
        'numeric' => ':attribute må være mindre enn or equal :value.',
        'file' => ':attribute må være mindre enn or equal :value kilobyte.',
        'string' => ':attribute må være mindre enn or equal :value tegn.',
        'array' => ':attribute må ikke ha mer enn :value elementer.',
    ],
    'max' => [
        'numeric' => ':attribute er kanskje ikke større enn :max.',
        'file' => ':attribute er kanskje ikke større enn :max kilobyte.',
        'string' => ':attribute er kanskje ikke større enn :max tegn.',
        'array' => ':attribute kan ikke ha mer enn :max elementer.',
    ],
    'mimes' => ':attribute må være en fil av typen: :values.',
    'mimetypes' => ':attribute må være en fil av typen: :values.',
    'min' => [
        'numeric' => ':attribute må være minst :min.',
        'file' => ':attribute må være minst :min kilobyte.',
        'string' => ':attribute må være minst :min tegn.',
        'array' => ':attribute must have at least :min elementer.',
    ],
    'not_in' => 'Den valgte :attribute er ugyldig.',
    'not_regex' => ':attribute formatet er ugyldig.',
    'numeric' => ':attribute må være et tall.',
    'password' => 'Passordet er feil.',
    'present' => ':attribute felt må være til stede.',
    'regex' => ':attribute formatet er ugyldig.',
    'required' => ':attribute felt er påkrevd.',
    'required_if' => ':attribute felt kreves når :other er :value.',
    'required_unless' => ':attribute felt er påkrevd med mindre :other er i :values.',
    'required_with' => ':attribute felt kreves når :values er til stede.',
    'required_with_all' => ':attribute felt kreves når :values er til stede.',
    'required_without' => ':attribute felt kreves når :values ikke er til stede.',
    'required_without_all' => ':attribute felt er påkrevd når ingen av :values er til stede.',
    'same' => ':attribute og :other må samsvare.',
    'size' => [
        'numeric' => ':attribute må være :size.',
        'file' => ':attribute må være :size kilobyte.',
        'string' => ':attribute må være :size tegn.',
        'array' => ':attribute must contain :size elementer.',
    ],
    'starts_with' => ':attribute må starte med ett av følgende: :values.',
    'string' => ':attribute må være en streng.',
    'timezone' => ':attribute må være en gyldig sone.',
    'unique' => ':attribute er allerede tatt.',
    'uploaded' => ':attribute kunne ikke laste opp.',
    'url' => ':attribute formatet er ugyldig.',
    'uuid' => ':attribute må være en gyldig UUID.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
