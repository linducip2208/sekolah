<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Demo parent account untuk verifikasi visual portal (idempotent).
$student = DB::table('students')->orderBy('id')->first();
if (!$student) { echo "no students\n"; exit(1); }

$schoolId = $student->school_id;
$existing = DB::table('users')->where('email', 'ortu.demo@sman1demo.sch.id')->first();
if ($existing) {
    $parentId = $existing->id;
} else {
    $parentId = DB::table('users')->insertGetId([
        'name' => 'Orang Tua Demo', 'email' => 'ortu.demo@sman1demo.sch.id',
        'password' => bcrypt('Ortu123!'), 'school_id' => $schoolId, 'is_active' => true,
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $role = DB::table('roles')->where('name', 'parent')->first();
    if ($role) {
        DB::table('model_has_roles')->insert([
            'role_id' => $role->id, 'model_type' => 'App\Models\User', 'model_id' => $parentId,
        ]);
    }
}

// Link pivot parent_student bila tabelnya ada dan belum terlink.
if (Schema::hasTable('parent_student')) {
    $exists = DB::table('parent_student')->where('parent_id', $parentId)->where('student_id', $student->id)->exists();
    if (!$exists) {
        DB::table('parent_student')->insert(['parent_id' => $parentId, 'student_id' => $student->id]);
    }
    echo "parent ready: ortu.demo@sman1demo.sch.id / Ortu123! linked to student #{$student->id}\n";
} else {
    echo "parent_student pivot table not found — check relation name\n";
}
