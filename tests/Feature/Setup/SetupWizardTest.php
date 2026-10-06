<?php

use App\Models\Academic\AcademicYear;
use App\Models\Academic\ClassRoom;
use App\Models\Finance\FeeStructure;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->school = School::factory()->create();
    $this->admin = User::factory()->create(['school_id' => $this->school->id]);
    Role::firstOrCreate(['name' => 'admin']);
    $this->admin->assignRole('admin');
});

it('shows the setup wizard with progress', function () {
    $this->actingAs($this->admin)->get(route('admin.setup.wizard'))
        ->assertOk()->assertSee('Setup Wizard');
});

it('walks the wizard: profile, year, class, fee', function () {
    $this->actingAs($this->admin)->post(route('admin.setup.wizard.profile'), [
        'name' => 'SDN Demo 1', 'phone' => '0811', 'address' => 'Jl. A',
    ])->assertRedirect(route('admin.setup.wizard', ['step' => 'year']));

    $this->actingAs($this->admin)->post(route('admin.setup.wizard.year'), [
        'name' => '2026/2027', 'start_date' => '2026-07-01', 'end_date' => '2027-06-30',
    ])->assertRedirect(route('admin.setup.wizard', ['step' => 'class']));

    expect(AcademicYear::where('school_id', $this->school->id)->where('is_active', true)->exists())->toBeTrue();

    $this->actingAs($this->admin)->post(route('admin.setup.wizard.class'), ['name' => 'Kelas 7A'])
        ->assertRedirect(route('admin.setup.wizard', ['step' => 'students']));
    expect(ClassRoom::where('school_id', $this->school->id)->exists())->toBeTrue();

    $this->actingAs($this->admin)->post(route('admin.setup.wizard.fee'), [
        'name' => 'SPP Bulanan', 'amount_rupiah' => '250000',
    ])->assertRedirect(route('admin.setup.wizard', ['step' => 'done']));
    $fee = FeeStructure::where('school_id', $this->school->id)->first();
    expect($fee)->not->toBeNull()->and($fee->amount)->toBe(25000000);
});

it('previews an xlsx student file', function () {
    $xlsxPath = sys_get_temp_dir().'/students-'.uniqid().'.xlsx';
    buildTestXlsx($xlsxPath, [
        ['admission_no', 'name', 'email', 'gender', 'password'],
        ['ADM-X1', 'Budi X', 'budi-x@example.test', 'male', 'Siswa123!'],
    ]);
    $file = new UploadedFile($xlsxPath, 'students.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

    $response = $this->actingAs($this->admin)->post(route('admin.import.students'), ['file' => $file]);
    $response->assertOk()->assertViewIs('school-admin.import.preview');
    expect($response->viewData('payload')['valid_count'])->toBe(1);
    @unlink($xlsxPath);
});

function buildTestXlsx(string $path, array $rows): void
{
    $strings = [];
    $indexOf = function ($v) use (&$strings) {
        $v = (string) $v;
        $i = array_search($v, $strings, true);
        if ($i === false) {
            $strings[] = $v;

            return count($strings) - 1;
        }

        return $i;
    };
    $sheetRows = '';
    foreach ($rows as $r => $cols) {
        $sheetRows .= '<row r="'.($r + 1).'">';
        foreach ($cols as $c => $val) {
            $col = chr(65 + $c).($r + 1);
            $sheetRows .= '<c r="'.$col.'" t="s"><v>'.$indexOf($val).'</v></c>';
        }
        $sheetRows .= '</row>';
    }
    $sst = '';
    foreach ($strings as $s) {
        $sst .= '<si><t>'.htmlspecialchars($s).'</t></si>';
    }
    $zip = new ZipArchive();
    $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    $zip->addFromString('[Content_Types].xml', '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/></Types>');
    $zip->addFromString('xl/worksheets/sheet1.xml', '<?xml version="1.0"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>'.$sheetRows.'</sheetData></worksheet>');
    $zip->addFromString('xl/sharedStrings.xml', '<?xml version="1.0"?><sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="'.count($strings).'" uniqueCount="'.count($strings).'">'.$sst.'</sst>');
    $zip->close();
}
