<?php

namespace Tests\Unit\Level;

use App\Services\LevelService;
use App\Repositories\Interfaces\LevelRepositoryInterface;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Mockery\MockInterface;
use Mockery;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class LevelServiceTest extends TestCase
{

    // ============================
    // Tests for getAllLevels()
    // ============================

    /**
     * Test: getAllLevels() harus mengembalikan semua data level ketika tidak ada error.
     */
    public function testGetAllLevelsReturnsAllLevels()
    {
        $expectedLevels = [
            ['id' => 1, 'name' => 'Level 1'],
            ['id' => 2, 'name' => 'Level 2'],
        ];

        $mockRepo = Mockery::mock(LevelRepositoryInterface::class);
        $mockRepo->shouldReceive('all')
            ->once()
            ->andReturn($expectedLevels);

        $levelService = new LevelService($mockRepo);

        $actualLevels = $levelService->getAllLevels();

        $this->assertEquals($expectedLevels, $actualLevels);
        $this->assertIsArray($actualLevels);
    }

    /**
     * Test: getAllLevels() harus melempar RuntimeException jika terjadi error di repository.
     */
    public function testGetAllLevelsThrowsRuntimeExceptionOnError()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Level data could not be retrieved.');

        Log::shouldReceive('error')
            ->once()
            ->with('Gagal mengambil semua level', Mockery::on(function ($arg) {
                return isset($arg['error']) && $arg['error'] === 'DB error';
            }));

        $mockRepo = Mockery::mock(LevelRepositoryInterface::class);
        $mockRepo->shouldReceive('all')
            ->once()
            ->andThrow(new \Exception('DB error'));

        $levelService = new LevelService($mockRepo);

        $levelService->getAllLevels();
    }

    /**
     * Test: getAllLevels() harus mengembalikan array kosong jika tidak ada data level.
     */
    public function testGetAllLevelsReturnsEmptyArrayWhenNoLevelsExist()
    {
        $mockRepo = Mockery::mock(LevelRepositoryInterface::class);
        $mockRepo->shouldReceive('all')
            ->once()
            ->andReturn([]);

        $levelService = new LevelService($mockRepo);

        $actualLevels = $levelService->getAllLevels();

        $this->assertEquals([], $actualLevels);
        $this->assertIsArray($actualLevels);
    }

    // ============================
    // Tests for getListLevels()
    // ============================

    /**
     * Test: getListLevels() harus mengembalikan data level sesuai filter ketika tidak ada error.
     */
    public function testGetListLevelsReturnsFilteredLevels()
    {
        $filter = ['name' => 'Level 1'];

        $expectedLevels = [
            ['id' => 1, 'name' => 'Level 1']
        ];

        $mockRepo = Mockery::mock(LevelRepositoryInterface::class);
        $mockRepo->shouldReceive('getList')
            ->once()
            ->with($filter)
            ->andReturn($expectedLevels);

        $levelService = new LevelService($mockRepo);

        $actualLevels = $levelService->getListLevels($filter);

        $this->assertEquals($expectedLevels, $actualLevels);
        $this->assertIsArray($actualLevels);
    }

    /**
     * Test: getListLevels() harus mengembalikan semua level ketika tidak ada filter.
     */
    public function testGetListLevelsReturnsAllLevelsWhenNoFilterGiven()
    {
        $expectedLevels = [
            ['id' => 1, 'name' => 'Level 1'],
            ['id' => 2, 'name' => 'Level 2'],
        ];

        $mockRepo = Mockery::mock(LevelRepositoryInterface::class);
        $mockRepo->shouldReceive('getList')
            ->once()
            ->with([])
            ->andReturn($expectedLevels);

        $levelService = new LevelService($mockRepo);

        $actualLevels = $levelService->getListLevels();

        $this->assertEquals($expectedLevels, $actualLevels);
        $this->assertIsArray($actualLevels);
    }

    /**
     * Test: getListLevels() harus melempar RuntimeException jika terjadi error di repository.
     */
    public function testGetListLevelsThrowsRuntimeExceptionOnError()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('List level data could not be retrieved.');

        Log::shouldReceive('error')
            ->once()
            ->with('Gagal mengambil daftar level', Mockery::on(function ($arg) {
                return isset($arg['error']) && $arg['error'] === 'DB error';
            }));

        $mockRepo = Mockery::mock(LevelRepositoryInterface::class);
        $mockRepo->shouldReceive('getList')
            ->once()
            ->with([])
            ->andThrow(new \Exception('DB error'));

        $levelService = new LevelService($mockRepo);

        $levelService->getListLevels();
    }

    /**
     * Test: getListLevels() harus mengembalikan array kosong jika tidak ada data level yang cocok.
     */
    public function testGetListLevelsReturnsEmptyArrayWhenNoMatchFound()
    {
        $filter = ['name' => 'Nonexistent Level'];

        $mockRepo = Mockery::mock(LevelRepositoryInterface::class);
        $mockRepo->shouldReceive('getList')
            ->once()
            ->with($filter)
            ->andReturn([]);

        $levelService = new LevelService($mockRepo);

        $actualLevels = $levelService->getListLevels($filter);

        $this->assertEquals([], $actualLevels);
        $this->assertIsArray($actualLevels);
    }

    // ============================
    // Tests for getLevelById()
    // ============================

    /**
     * Test: getLevelById() harus mengembalikan data level jika ID ditemukan.
     */
    public function testGetLevelByIdReturnsLevelIfFound()
    {
        $id = '1';  // ID valid dalam bentuk string
        $expectedLevel = ['id' => 1, 'name' => 'Level 1'];

        $mockRepo = Mockery::mock(LevelRepositoryInterface::class);
        $mockRepo->shouldReceive('find')
            ->once()
            ->with($id)
            ->andReturn($expectedLevel);

        $levelService = new LevelService($mockRepo);

        $actualLevel = $levelService->getLevelById($id);

        $this->assertEquals($expectedLevel, $actualLevel);
    }

    /**
     * Test: getLevelById() harus melempar InvalidArgumentException jika ID bukan angka.
     */
    public function testGetLevelByIdThrowsInvalidArgumentExceptionIfIdIsNotInteger()
    {
        $id = 'non-numeric-id';  // ID tidak valid

        // Menangkap dan memastikan exception yang dilempar sesuai
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ID must be an integer.');

        // Tidak perlu mock repository karena kita hanya menguji validasi ID
        $mockRepo = Mockery::mock(LevelRepositoryInterface::class);

        $levelService = new LevelService($mockRepo);

        // Panggil method yang akan melempar exception
        $levelService->getLevelById($id);
    }

    /**
     * Test: getLevelById() harus mengembalikan null jika ID tidak ditemukan.
     */
    public function testGetLevelByIdReturnsNullIfNotFound()
    {
        $id = '999';  // ID tidak ada

        $mockRepo = Mockery::mock(LevelRepositoryInterface::class);
        $mockRepo->shouldReceive('find')
            ->once()
            ->with($id)
            ->andReturn(null);

        $levelService = new LevelService($mockRepo);

        $actualLevel = $levelService->getLevelById($id);

        $this->assertNull($actualLevel);
    }

    // =================================
    // Tests for getLevelByKodeOrNama()
    // =================================

    /**
     * Test: getLevelByKodeOrNama() harus mengembalikan data level kalau ditemukan.
     */
    public function testGetLevelByKodeOrNamaReturnsLevelIfFound()
    {
        $kode = 'L001';
        $nama = 'Admin';
        $expected = ['level_id' => 1, 'level_kode' => $kode, 'level_nama' => $nama];

        $mockRepo = Mockery::mock(LevelRepositoryInterface::class);
        $mockRepo->shouldReceive('findByKodeOrNama')
            ->once()
            ->with($kode, $nama)
            ->andReturn($expected);

        $service = new LevelService($mockRepo);
        $result  = $service->getLevelByKodeOrNama($kode, $nama);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test: getLevelByKodeOrNama() harus mengembalikan null jika tidak ada yang match.
     */
    public function testGetLevelByKodeOrNamaReturnsNullIfNotFound()
    {
        $kode = 'X999';
        $nama = 'Nonexistent';

        $mockRepo = Mockery::mock(LevelRepositoryInterface::class);
        $mockRepo->shouldReceive('findByKodeOrNama')
            ->once()
            ->with($kode, $nama)
            ->andReturn(null);

        $service = new LevelService($mockRepo);
        $result  = $service->getLevelByKodeOrNama($kode, $nama);

        $this->assertNull($result);
    }

    /**
     * Test: getLevelByKodeOrNama() harus melempar RuntimeException jika repository error.
     */
    public function testGetLevelByKodeOrNamaThrowsRuntimeExceptionOnError()
    {
        $kode = 'LERR';
        $nama = 'Error';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Level data could not be retrieved.');

        Log::shouldReceive('error')
            ->once()
            ->with(
                "Gagal mengambil level dengan kode: {$kode} atau nama: {$nama}",
                Mockery::on(fn($arg) => isset($arg['error']) && $arg['error'] === 'DB failure')
            );

        $mockRepo = Mockery::mock(LevelRepositoryInterface::class);
        $mockRepo->shouldReceive('findByKodeOrNama')
            ->once()
            ->with($kode, $nama)
            ->andThrow(new \Exception('DB failure'));

        $service = new LevelService($mockRepo);
        $service->getLevelByKodeOrNama($kode, $nama);
    }

    // =================================
    // Tests for storeLevel()
    // =================================

    /**
     * Test: storeLevel() harus mengembalikan status true jika penyimpanan berhasil.
     */
    public function testStoreLevelReturnsSuccessOnCreate()
    {
        $data = [
            'level_kode' => 'L001',
            'level_nama' => 'Administrator',
        ];

        $mockRepo = Mockery::mock(LevelRepositoryInterface::class);
        $mockRepo->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn(true);

        $service = new LevelService($mockRepo);
        $result = $service->storeLevel($data);

        $this->assertTrue($result['status']);
        $this->assertEquals('Data level berhasil disimpan.', $result['message']);
    }

    /**
     * Test: storeLevel() harus mengembalikan status false jika terjadi exception.
     */
    public function testStoreLevelReturnsFailureOnException()
    {
        $data = [
            'level_kode' => 'L002',
            'level_nama' => 'Operator',
        ];

        Log::shouldReceive('error')
            ->once()
            ->with('Gagal menyimpan data level', Mockery::on(function ($arg) use ($data) {
                return isset($arg['data'], $arg['error']) && $arg['data'] === $data;
            }));

        $mockRepo = Mockery::mock(LevelRepositoryInterface::class);
        $mockRepo->shouldReceive('create')
            ->once()
            ->with($data)
            ->andThrow(new \Exception('DB error'));

        $service = new LevelService($mockRepo);
        $result = $service->storeLevel($data);

        $this->assertFalse($result['status']);
        $this->assertEquals('Gagal menyimpan data level.', $result['message']);
    }

    // =================================
    // Tests for importFromExcel()
    // =================================

    /**
     * Helper untuk membuat mock file Excel
     */
    private function getMockExcelFile(array $rows)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($rows as $rowIndex => $row) {
            foreach ($row as $col => $value) {
                $sheet->setCellValue("{$col}" . ($rowIndex + 1), $value);
            }
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempFile);

        $mockFile = Mockery::mock(UploadedFile::class);
        $mockFile->shouldReceive('isValid')->andReturn(true);
        $mockFile->shouldReceive('getClientOriginalExtension')->andReturn('xlsx');
        $mockFile->shouldReceive('getRealPath')->andReturn($tempFile);

        return $mockFile;
    }

    /**
     * Test: importFromExcel() harus mengembalikan status false jika file tidak valid atau bukan file Excel (.xlsx).
     */
    public function testImportFromExcelReturnsFailureIfFileIsInvalid()
    {
        $mockFile = Mockery::mock(UploadedFile::class);
        $mockFile->shouldReceive('isValid')->once()->andReturn(false);

        $service = new LevelService(Mockery::mock(LevelRepositoryInterface::class));
        $result = $service->importFromExcel($mockFile);

        $this->assertFalse($result['status']);
        $this->assertStringContainsString('tidak valid', $result['message']);
    }

    // lewati dulu
    /**
     * Test: importFromExcel() harus mengembalikan status false jika terjadi kesalahan saat membaca file Excel.
 *public function testImportFromExcelReturnsFailureIfReaderFails()
     * {
     * $mockFile = Mockery::mock(UploadedFile::class);
     * $mockFile->shouldReceive('isValid')->once()->andReturn(true);
     * $mockFile->shouldReceive('getClientOriginalExtension')->once()->andReturn('xlsx');
     * $mockFile->shouldReceive('getRealPath')->once()->andReturn('/invalid/path/to/file.xlsx');
     *
     * Log::shouldReceive('error')->once();
     *
     * IOFactory::shouldReceive('createReader')->andThrow(new \PhpOffice\PhpSpreadsheet\Reader\Exception('Read error'));
     *
     * $service = new LevelService(Mockery::mock(LevelRepositoryInterface::class));
     * $result = $service->importFromExcel($mockFile);
     *
     * $this->assertFalse($result['status']);
     * $this->assertStringContainsString('Gagal membaca file Excel', $result['message']);
     * }
     */

    /**
     * Test: importFromExcel() harus mengembalikan status false jika hanya header yang ada di file (tidak ada data level).
     */
    public function testImportFromExcelReturnsFailureIfOnlyHeaderPresent()
    {
        $mockFile = $this->getMockExcelFile([
            ['A' => 'level_kode', 'B' => 'level_nama'],
        ]);

        $service = new LevelService(Mockery::mock(LevelRepositoryInterface::class));
        $result = $service->importFromExcel($mockFile);

        $this->assertFalse($result['status']);
        $this->assertStringContainsString('Tidak ada data yang diimport', $result['message']);
    }

    /**
     * Test: importFromExcel() harus mengembalikan status false jika header file tidak sesuai dengan yang diharapkan.
     */
    public function testImportFromExcelReturnsFailureIfHeaderIsInvalid()
    {
        $mockFile = $this->getMockExcelFile([
            ['A' => 'kode_level', 'B' => 'nama_level'], // Header salah
            ['A' => 'L001', 'B' => 'Admin'],
        ]);

        $service = new LevelService(Mockery::mock(LevelRepositoryInterface::class));
        $result = $service->importFromExcel($mockFile);

        $this->assertFalse($result['status']);
        $this->assertStringContainsString('Header file Excel tidak sesuai', $result['message']);
    }


    /**
     * Test: importFromExcel() harus mengembalikan status false jika ditemukan data yang duplikat di database.
     */
    public function testImportFromExcelReturnsFailureIfDuplicateFound()
    {
        $repo = Mockery::mock(LevelRepositoryInterface::class);
        $repo->shouldReceive('findByKodeOrNama')->once()->andReturn(['id' => 1]); // Data duplikat

        $mockFile = $this->getMockExcelFile([
            ['A' => 'level_kode', 'B' => 'level_nama'],
            ['A' => 'L001', 'B' => 'Admin'],
        ]);

        $service = new LevelService($repo);
        $result = $service->importFromExcel($mockFile);

        $this->assertFalse($result['status']);
        $this->assertStringContainsString('sudah ada', $result['message']);
    }

    /**
     * Test: importFromExcel() harus mengembalikan status true jika data valid berhasil diimport.
     */
    public function testImportFromExcelReturnsSuccessIfDataValid()
    {
        $repo = Mockery::mock(LevelRepositoryInterface::class);
        $repo->shouldReceive('findByKodeOrNama')->once()->andReturn(null);
        $repo->shouldReceive('insertMany')->once(); // Simulasi insert data ke database

        $mockFile = $this->getMockExcelFile([
            ['A' => 'level_kode', 'B' => 'level_nama'],
            ['A' => 'L001', 'B' => 'Admin'],
        ]);

        $service = new LevelService($repo);
        $result = $service->importFromExcel($mockFile);

        $this->assertTrue($result['status']);
        $this->assertEquals('Data berhasil diimport', $result['message']);
    }


    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
