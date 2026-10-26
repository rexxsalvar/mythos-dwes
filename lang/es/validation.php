<?php

return [
    'required' => 'El campo :attribute es obligatorio.',
    'string' => 'El campo :attribute debe ser un texto.',
    'email' => 'Introduce un correo electrónico válido.',
    'unique' => 'Ese :attribute ya está registrado.',
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'boolean' => 'El campo :attribute debe ser una opción válida.',
    'integer' => 'El campo :attribute debe ser un número entero.',
    'in' => 'Selecciona una opción válida para :attribute.',
    'exists' => 'La opción seleccionada para :attribute no existe.',
    'date_format' => 'El campo :attribute debe contener una fecha válida.',
    'after_or_equal' => 'La fecha hasta no puede ser anterior a la fecha desde.',
    'regex' => 'El formato de :attribute no es válido.',
    'max' => ['string' => 'El campo :attribute no puede superar :max caracteres.'],
    'min' => ['string' => 'El campo :attribute debe tener al menos :min caracteres.'],
    'password' => [
        'letters' => 'La contraseña debe contener al menos una letra.',
        'numbers' => 'La contraseña debe contener al menos un número.',
    ],
    'attributes' => [
        'name' => 'nombre', 'email' => 'correo electrónico', 'password' => 'contraseña',
        'phone' => 'teléfono', 'preferred_locale' => 'idioma preferido', 'remember' => 'recordarme',
        'from' => 'desde', 'until' => 'hasta', 'room_id' => 'sala',
    ],
];
