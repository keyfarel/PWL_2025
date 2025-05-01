<?php

namespace App\Services\Interfaces;

interface LevelServiceInterface
{
    public function getAllLevels();
    public function getListLevels(array $filter = []);
    public function getLevelById(string $id);
    public function getLevelByKodeOrNama(string $kode, string $nama);
    public function storeLevel(array $data): array;
    public function importFromExcel(array $file);
    public function exportToExcel();
    public function exportToPDF();
}
