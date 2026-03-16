<?php

return [
    'secret' => env('JWT_SECRET', 'tu_clave_secreta_jwt_para_la_tienda'),
    'ttl' => env('JWT_TTL', 60), // Tiempo de vida en minutos
    'algo' => 'HS256',
];