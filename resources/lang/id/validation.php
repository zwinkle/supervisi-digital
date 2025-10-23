<?php

return [
    'required' => ':Attribute wajib diisi.',
    'email' => ':Attribute harus berupa alamat email yang valid.',
    'unique' => ':Attribute sudah terdaftar.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'string' => ':Attribute harus berupa teks.',
    'integer' => ':Attribute harus berupa angka.',
    'date' => ':Attribute bukan tanggal yang valid.',
    'exists' => ':Attribute yang dipilih tidak valid.',
    'max' => [
        'string' => ':Attribute tidak boleh lebih dari :max karakter.',
    ],
    'min' => [
        'string' => ':Attribute minimal :min karakter.',
    ],

    'attributes' => [
        'name' => 'nama',
        'email' => 'email',
        'password' => 'kata sandi',
        'password_confirmation' => 'konfirmasi kata sandi',
        'title' => 'judul',
        'address' => 'alamat',
        'date' => 'tanggal',
        'school_id' => 'sekolah',
        'teacher_id' => 'guru',
    ],
];
