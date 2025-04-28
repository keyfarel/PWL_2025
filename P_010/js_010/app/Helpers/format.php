<?php

if (! function_exists('format_rupiah')) {
    function format_rupiah($angka, $prefix = 'Rp ')
    {
        if ($angka === null) return '-';
        return $prefix . number_format($angka, 0, ',', '.');
    }
}

if (! function_exists('format_ribuan')) {
    function format_ribuan($angka)
    {
        if ($angka === null) return '-';
        return number_format($angka, 0, ',', '.');
    }
}
