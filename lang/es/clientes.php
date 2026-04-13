<?php

return [

    'validation' => [
        'documento_vacio' => 'Ingresá el número de documento (solo dígitos; el guión es opcional y se ignora al guardar).',
        'documento_solo_ceros' => 'El documento no puede ser solo ceros.',
        'documento_digitos_repetidos' => 'El número no puede ser una sola cifra repetida (ej. 0000000 o 1111111).',
        'rif_nueve_digitos' => 'Para RIF (tipo J o G) se requieren exactamente 9 dígitos numéricos.',
        'pasaporte_ocho_nueve' => 'El pasaporte (tipo P) debe tener entre 8 y 9 dígitos numéricos.',
        'tipo_invalido' => 'El tipo de documento no es válido.',
        'cedula_seis_ocho' => 'La cédula (tipo V o E) debe tener entre 6 y 8 dígitos numéricos.',
        'telefono_movil' => 'Si indicás teléfono, usá 11 dígitos comenzando por 04 (móvil Venezuela, ej. 04124567890).',
    ],

    'documento_ya_registrado' => 'Ya existe un cliente con este tipo y número de documento.',

];
