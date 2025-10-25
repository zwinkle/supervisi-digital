<?php

namespace App\Support;

class TeacherOptions
{
    public static function teacherTypes(): array
    {
        return [
            'subject' => 'Guru Mata Pelajaran',
            'class' => 'Guru Kelas',
        ];
    }

    public static function subjects(): array
    {
        return [
            'Pendidikan Agama dan Budi Pekerti',
            'Pendidikan Pancasila',
            'Bahasa Indonesia',
            'Matematika',
            'IPAS',
            'Pendidikan Jasmani Olahraga dan Kesehatan',
            'Seni Musik',
            'Seni Rupa',
            'Seni Teater',
            'Seni Tari',
            'Bahasa Inggris',
            'Koding dan Kecerdasan Artifisial',
        ];
    }

    public static function classes(): array
    {
        return ['1', '2', '3', '4', '5', '6'];
    }
}
