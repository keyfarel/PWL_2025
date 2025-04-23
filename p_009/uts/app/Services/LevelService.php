<?php

namespace App\Services;

use App\Repositories\Interfaces\LevelRepositoryInterface;
use App\Services\Interfaces\LevelServiceInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;


class LevelService implements LevelServiceInterface
{
    protected $levelRepository;

    public function __construct(LevelRepositoryInterface $levelRepository)
    {
        $this->levelRepository = $levelRepository;
    }

    public function getAllLevels()
    {
        return $this->levelRepository->all();
    }

    public function getListLevels(array $filter = [])
    {
        return $this->levelRepository->getList($filter);
    }

    public function getLevelById(string $id)
    {
        return $this->levelRepository->find($id);
    }

    public function getLevelBykodeOrNama(string $kode, string $nama)
    {
        return $this->levelRepository->findByKodeOrNama($kode, $nama);
    }

    public function storeLevel(array $data): array
    {
        // Proses menyimpan data lewat repository
        $this->levelRepository->create([
            'level_kode' => $data['level_kode'],
            'level_nama' => $data['level_nama'],
        ]);

        return [
            'status' => true,
            'message' => 'Data level berhasil disimpan.',
        ];
    }

    public function importFromExcel($file): array
    {
        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray(null, true, true, true);

        if (count($data) <= 1) {
            return [
                'status' => false,
                'message' => 'Tidak ada data yang diimport.' . "\n" . 'Mohon ikuti instruksi di template.',
            ];
        }

        $headerA = strtolower(str_replace(' ', '_', trim($data[1]['A'] ?? '')));
        $headerB = strtolower(str_replace(' ', '_', trim($data[1]['B'] ?? '')));
        $expectedHeader = ['level_kode', 'level_nama'];
        if (!($headerA === $expectedHeader[0] && $headerB === $expectedHeader[1])) {
            return [
                'status' => false,
                'message' => 'Header file Excel tidak sesuai. Pastikan kolom A dan B berturut-turut: ' .
                    implode(', ', $expectedHeader) . '.' . "\n" . 'Mohon ikuti instruksi di template.',
            ];
        }

        $insert = [];

        foreach ($data as $rowIndex => $rowValue) {
            if ($rowIndex == 1) continue;

            $levelKode = trim($rowValue['A'] ?? '');
            $levelNama = trim($rowValue['B'] ?? '');

            if ($levelKode === '' || $levelNama === '') continue;

            $existing = $this->levelRepository->findByKodeOrNama($levelKode, $levelNama);
            if ($existing) {
                return [
                    'status' => false,
                    'message' => "Level dengan kode '{$levelKode}' atau nama '{$levelNama}' sudah ada." .
                        "\n" . 'Mohon ikuti instruksi di template.',
                ];
            }

            $insert[] = [
                'level_kode' => $levelKode,
                'level_nama' => $levelNama,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (count($insert) > 0) {
            $this->levelRepository->insertMany($insert); // kamu bisa buat fungsi insertBulk di repo
            return [
                'status' => true,
                'message' => 'Data berhasil diimport',
            ];
        }

        return [
            'status' => false,
            'message' => 'Tidak ada data valid yang diimport.' . "\n" . 'Mohon ikuti instruksi di template.',
        ];
    }

    public function exportToExcel()
    {
        $level = $this->levelRepository->getAllLevelsOrderedByKode();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Kode Level');
        $sheet->setCellValue('C1', 'Nama Level');
        $sheet->getStyle('A1:C1')->getFont()->setBold(true);

        // Data
        $row = 2;
        $no = 1;
        foreach ($level as $item) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $item->level_kode);
            $sheet->setCellValue('C' . $row, $item->level_nama);
            $row++;
        }

        // Auto-size
        foreach (range('A', 'C') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->setTitle('Data Level');

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = 'Data Level ' . date('Y-m-d H:i:s') . '.xlsx';

        // Set header 
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');

        $writer->save('php://output');
        exit;
    }

    public function exportToPDF()
    {
        $data = $this->levelRepository->getAllLevelsOrderedByKode();

        $pdf = Pdf::loadView('level.export_pdf', ['level' => $data]);
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption("isRemoteEnabled", true);

        return $pdf->stream('Data Level ' . date('Y-m-d H:i:s') . '.pdf');
    }
}
