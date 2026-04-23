<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Millennium — mensajes de validación en español (evitar claves en inglés en UI)
    |--------------------------------------------------------------------------
    */

    'required' => 'El campo :attribute es obligatorio.',
    'string' => 'El campo :attribute debe ser texto.',
    'max' => [
        'string' => 'El campo :attribute no puede superar :max caracteres.',
    ],
    'min' => [
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
    ],
    'regex' => 'El formato de :attribute no es válido.',

    'unique' => ':attribute ya está registrado en el sistema.',

    'attributes' => [
        'tipo_documento' => 'tipo de documento',
        'documento_numero' => 'número de documento',
        'nombre_razon_social' => 'nombre o razón social',
        'telefono' => 'teléfono',
        'zona' => 'zona o sector',
        'vendedor_id' => 'vendedor',
    ],

];
