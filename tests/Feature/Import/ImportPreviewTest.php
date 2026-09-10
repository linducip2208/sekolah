<?php

use App\Models\Academic\Student;
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

it('previews a student CSV without writing records and confirms only valid rows', function () {
    $csv = implode("\n", [
        'admission_no,name,email,phone,gender,date_of_birth,address,guardian_name,guardian_phone,password',
        'ADM-100,Budi,budi-import@example.test,081234,male,2010-01-01,Jalan A,Ayah Budi,081235,Siswa123!',
        'ADM-101,Sari,budi-import@example.test,081235,female,2010-02-01,Jalan B,Ibu Sari,081236,Siswa123!',
    ]);
    $file = UploadedFile::fake()->createWithContent('students.csv', $csv);

    $response = $this->actingAs($this->admin)->post(route('admin.import.students'), [
        'file' => $file,
    ]);

    $response->assertOk()
        ->assertViewIs('school-admin.import.preview')
        ->assertSee('Preview Import Siswa')
        ->assertSee('Email sudah digunakan atau duplikat di file.');
    expect(Student::where('school_id', $this->school->id)->count())->toBe(0);

    $token = $response->viewData('token');
    $this->actingAs($this->admin)->post(route('admin.import.students.confirm'), ['token' => $token])
        ->assertRedirect(route('admin.import.index'));

    expect(Student::where('school_id', $this->school->id)->count())->toBe(1)
        ->and(User::where('email', 'budi-import@example.test')->exists())->toBeTrue();
});

it('does not accept a preview token from another school or user', function () {
    $file = UploadedFile::fake()->createWithContent('students.csv', "admission_no,name,email,gender,password\nADM-200,Andi,andi-import@example.test,male,Siswa123!");
    $response = $this->actingAs($this->admin)->post(route('admin.import.students'), ['file' => $file]);
    $token = $response->viewData('token');

    $otherSchool = School::factory()->create();
    $otherAdmin = User::factory()->create(['school_id' => $otherSchool->id]);
    $otherAdmin->assignRole('admin');

    $this->actingAs($otherAdmin)->post(route('admin.import.students.confirm'), ['token' => $token])
        ->assertRedirect(route('admin.import.index'))
        ->assertSessionHasErrors();

    expect(Student::where('school_id', $this->school->id)->count())->toBe(0);
});
