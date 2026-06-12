<?php

return [
    'gemini' => [
        'api_key' => getenv('GEMINI_API_KEY') ?: '',
        'model'   => 'gemini-2.0-flash',
        'endpoint' => 'https://generativelanguage.googleapis.com/v1beta/models',
        'timeout' => 30,
        'max_context_chars' => 8000,
        'max_history' => 6,
    ],
    'company' => [
        'name' => 'FMLider',
        'tagline' => 'Soluções de logística, transporte e transitário em Angola',
        'phone' => '+244 935 141 747',
        'email' => 'geral@fmlider.co.ao',
        'address' => 'FMLider Base, Estrada da Pedreira, Bairro da Vidrul, Cacuaco, Luanda',
        'maps_query' => 'FMLider+Base+Cacuaco+Luanda+Angola',
        'lat' => -8.769266,
        'lng' => 13.3984122,
        'website' => 'https://fmlider.co.ao',
    ],
];
