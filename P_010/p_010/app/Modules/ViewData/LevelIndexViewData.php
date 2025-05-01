<?php

namespace App\Modules\ViewData;

class LevelIndexViewData
{
    public static function get()
    {
        return [
            'breadcrumbs' => (object) [
                'title' => 'Daftar Level',
                'list' => ['Home', 'Level'],
            ],
            'page' => (object) [
                'title' => 'Daftar level dalam sistem',
            ],
            'activeMenu' => 'level',
        ];
    }
}
