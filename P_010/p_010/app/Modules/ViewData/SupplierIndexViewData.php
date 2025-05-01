<?php

namespace App\Modules\ViewData;

class SupplierIndexViewData
{
    public static function get()
    {
        return [
            'breadcrumbs' => (object) [
                'title' => 'Daftar Supplier',
                'list' => ['Home', 'Supplier'],
            ],
            'page' => (object) [
                'title' => 'Daftar supplier yang terdaftar dalam sistem',
            ],
            'activeMenu' => 'supplier',
        ];
    }
}
