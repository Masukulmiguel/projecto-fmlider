<?php

return [
    'groq' => [
        'api_key' => getenv('GROQ_API_KEY') ?: '',
        'model'   => 'llama-3.3-70b-versatile',
        'endpoint' => 'https://api.groq.com/openai/v1/chat/completions',
        'timeout' => 30,
        'max_tokens' => 1024,
        'temperature' => 0.8,
        'max_history' => 8,
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
