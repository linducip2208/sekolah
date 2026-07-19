# Module 10 — Admission Management

## Depends On
Module 04 (academic structure — class_rooms, sections), Module 03 (school setup)

## What to Build
Penerimaan siswa baru: formulir pendaftaran online, review & approval oleh receptionist/admin,
konversi ke siswa aktif + pembuatan akun user, dan manajemen daftar tunggu.

---

## Database Schema

```php
// admission_inquiries table (awal: sekedar tanya-tanya)
Schema::create('admission_inquiries', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->string('student_name');
    $table->string('parent_name');
    $table->string('phone');
    $table->string('email')->nullable();
    $table->foreignId('class_room_id')->constrained(); // kelas yang diminati
    $table->string('source')->nullable();              // "website", "walk-in", "referral"
    $table->text('message')->nullable();
    $table->enum('status', ['new', 'contacted', 'converted', 'dropped'])->default('new');
    $table->timestamp('follow_up_at')->nullable();
    $table->timestamps();
    $table->softDeletes();
    $table->index(['school_id', 'status']);
});

// admission_forms table (formulir lengkap)
Schema::create('admission_forms', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
    $table->foreignId('class_room_id')->constrained();
    $table->string('admission_no')->nullable();        // diberikan saat approve
    $table->string('student_name');
    $table->date('date_of_birth');
    $table->string('gender');
    $table->string('blood_group')->nullable();
    $table->string('religion')->nullable();
    $table->text('address');
    $table->string('city')->nullable();
    $table->string('state')->nullable();
    $table->string('zip_code')->nullable();
    $table->string('previous_school')->nullable();
    $table->string('previous_class')->nullable();
    $table->string('photo')->nullable();               // S3 path
    $table->string('birth_certificate')->nullable();   // S3 path dokumen
    $table->string('previous_marksheet')->nullable();  // S3 path
    // Guardian info
    $table->string('guardian_name');
    $table->string('guardian_relation');               // father|mother|guardian
    $table->string('guardian_phone');
    $table->string('guardian_email')->nullable();
    $table->string('guardian_occupation')->nullable();
    $table->string('guardian_photo')->nullable();      // S3 path
    // Status
    $table->enum('status', ['pending', 'under_review', 'approved', 'rejected', 'enrolled'])->default('pending');
    $table->text('rejection_reason')->nullable();
    $table->foreignId('reviewed_by')->nullable()->constrained('users');
    $table->timestamp('reviewed_at')->nullable();
    $table->timestamps();
    $table->softDeletes();
    $table->index(['school_id', 'academic_year_id', 'status']);
    $table->unique(['school_id', 'admission_no']);
});
```

---

## API Endpoints

| Method | URI                                                  | Role              | Deskripsi                            |
|--------|------------------------------------------------------|-------------------|--------------------------------------|
| POST   | `/api/v1/admission/inquiries`                        | public            | Submit inquiry (dari website)        |
| GET    | `/api/v1/admission/inquiries`                        | admin, recept     | List semua inquiry                   |
| PUT    | `/api/v1/admission/inquiries/{id}`                   | admin, recept     | Update status inquiry                |
| POST   | `/api/v1/admission/forms`                            | public, recept    | Submit formulir pendaftaran          |
| GET    | `/api/v1/admission/forms`                            | admin, recept     | List semua formulir (filter status)  |
| GET    | `/api/v1/admission/forms/{id}`                       | admin, recept     | Detail formulir                      |
| PUT    | `/api/v1/admission/forms/{id}`                       | admin, recept     | Update formulir                      |
| POST   | `/api/v1/admission/forms/{id}/approve`               | admin             | Approve + generate admission_no      |
| POST   | `/api/v1/admission/forms/{id}/reject`                | admin             | Tolak dengan alasan                  |
| POST   | `/api/v1/admission/forms/{id}/enroll`                | admin             | Enroll: buat student + user accounts |
| GET    | `/api/v1/admission/stats`                            | admin, recept     | Statistik penerimaan                 |

---

## Files to Create

```
Modules/Finance/
  Http/
    Controllers/
      AdmissionInquiryController.php
      AdmissionFormController.php
    Requests/
      AdmissionInquiryRequest.php
      AdmissionFormRequest.php
      ApproveAdmissionRequest.php
      RejectAdmissionRequest.php
    Resources/
      AdmissionInquiryResource.php
      AdmissionFormResource.php
  Models/
    AdmissionInquiry.php
    AdmissionForm.php
  Services/
    AdmissionService.php
  Repositories/
    AdmissionRepository.php
  Policies/
    AdmissionPolicy.php
```

---

## AdmissionService — Enroll Implementation

```php
// Modules/Finance/Services/AdmissionService.php
class AdmissionService
{
    public function enroll(AdmissionForm $form, int $classSectionId): Student
    {
        return DB::transaction(function () use ($form, $classSectionId) {
            // 1. Buat user account untuk siswa
            $studentUser = User::create([
                'school_id' => $form->school_id,
                'name'      => $form->student_name,
                'email'     => $this->generateEmail($form),
                'password'  => Hash::make($this->generatePassword()),
                'is_active' => true,
            ]);
            $studentUser->assignRole('student');

            // 2. Buat user account untuk parent
            $parentUser = User::firstOrCreate(
                ['email' => $form->guardian_email, 'school_id' => $form->school_id],
                [
                    'name'     => $form->guardian_name,
                    'phone'    => $form->guardian_phone,
                    'password' => Hash::make($this->generatePassword()),
                    'is_active'=> true,
                ]
            );
            $parentUser->assignRole('parent');

            // 3. Buat profil siswa
            $activeYear = AcademicYear::where('school_id', $form->school_id)
                ->where('is_active', true)->firstOrFail();

            $student = Student::create([
                'school_id'        => $form->school_id,
                'user_id'          => $studentUser->id,
                'class_section_id' => $classSectionId,
                'academic_year_id' => $activeYear->id,
                'admission_no'     => $form->admission_no,
                'admission_date'   => today(),
                'date_of_birth'    => $form->date_of_birth,
                'gender'           => $form->gender,
                'blood_group'      => $form->blood_group,
                'address'          => $form->address,
                'guardian_name'    => $form->guardian_name,
                'guardian_phone'   => $form->guardian_phone,
                'guardian_email'   => $form->guardian_email,
                'guardian_relation'=> $form->guardian_relation,
            ]);

            // 4. Link parent ke student
            $student->parents()->attach($parentUser->id, ['relation' => $form->guardian_relation]);

            // 5. Update status form
            $form->update(['status' => 'enrolled']);

            // 6. Kirim email welcome + credentials
            SendAdmissionWelcomeJob::dispatch($studentUser, $parentUser, $student);

            return $student->load('user', 'classSection.classRoom', 'classSection.section');
        });
    }

    public function approve(AdmissionForm $form, User $admin): AdmissionForm
    {
        $admissionNo = $this->generateAdmissionNo($form->school_id, $form->academic_year_id);

        $form->update([
            'status'       => 'approved',
            'admission_no' => $admissionNo,
            'reviewed_by'  => $admin->id,
            'reviewed_at'  => now(),
        ]);

        SendAdmissionApprovedJob::dispatch($form);

        return $form;
    }

    private function generateAdmissionNo(int $schoolId, int $yearId): string
    {
        $year  = AcademicYear::find($yearId);
        $count = AdmissionForm::where('school_id', $schoolId)
            ->where('academic_year_id', $yearId)
            ->where('status', '!=', 'rejected')
            ->count();

        return sprintf('ADM-%s-%04d', substr($year->name, 0, 4), $count + 1);
    }
}
```

---

## Acceptance Criteria

- [ ] Formulir pendaftaran bisa disubmit oleh publik (tanpa auth)
- [ ] Approval otomatis generate admission_no yang unik per tahun ajaran
- [ ] Enroll membuat 2 user accounts (student + parent) dengan password acak
- [ ] Email welcome dikirim dengan kredensial login
- [ ] Formulir yang di-reject tidak bisa di-enroll
- [ ] Statistik menampilkan jumlah per status per kelas

## Tests to Write

```
tests/Feature/Admission/
  InquirySubmitTest.php
  FormSubmitTest.php
  ApproveAdmissionTest.php
  RejectAdmissionTest.php
  EnrollStudentTest.php
  AdmissionNoGenerationTest.php
  PublicSubmitTest.php
```
