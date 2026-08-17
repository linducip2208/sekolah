<?php

use App\Models\Academic\Student;
use App\Models\School;
use App\Models\User;
use App\Services\AI\DataChatService;

beforeEach(function () {
    $this->service = app(DataChatService::class);
});

it('maps keywords to the correct metric', function () {
    $s = $this->service;

    expect($s->fallbackMetricKey('berapa tingkat kehadiran siswa bulan ini'))->toBe('attendance_rate');
    expect($s->fallbackMetricKey('berapa tunggakan spp yang belum dibayar'))->toBe('unpaid_invoices');
    expect($s->fallbackMetricKey('total pendapatan per bulan'))->toBe('revenue_by_month');
    expect($s->fallbackMetricKey('jumlah siswa laki-laki dan perempuan'))->toBe('students_by_gender');
    expect($s->fallbackMetricKey('rata-rata nilai per mapel'))->toBe('average_marks_by_subject');
    expect($s->fallbackMetricKey('berapa buku di perpustakaan'))->toBe('library_books');
    expect($s->fallbackMetricKey('jumlah guru per departemen'))->toBe('staff_count');
    expect($s->fallbackMetricKey('siswa per kelas'))->toBe('students_by_class');
});

it('exposes a metric registry', function () {
    expect($this->service->metrics())->toHaveCount(8);
});

it('runs students_by_class metric and returns structured rows', function () {
    $school = School::factory()->create();
    $user = User::factory()->create(['school_id' => $school->id]);

    Student::create(['user_id' => $user->id, 'school_id' => $school->id, 'admission_no' => 'A1']);
    Student::create(['user_id' => $user->id, 'school_id' => $school->id, 'admission_no' => 'A2']);

    $result = $this->service->runMetric($school->id, 'students_by_class');

    expect($result['columns'])->toBe(['Rombel', 'Jumlah Siswa']);
    expect(array_sum(array_column($result['rows'], 'value')))->toBe(2);
});

it('answers without AI configured using rule-based fallback and logs the chat', function () {
    $school = School::factory()->create();
    $user = User::factory()->create(['school_id' => $school->id]);

    Student::create(['user_id' => $user->id, 'school_id' => $school->id, 'admission_no' => 'A1', 'gender' => 'male']);

    $result = $this->service->ask($school->id, $user->id, 'berapa jumlah siswa laki-laki dan perempuan');

    expect($result['metric_key'])->toBe('students_by_gender');
    expect($result['used_ai'])->toBeFalse();
    expect($result['answer'])->toContain('siswa');

    $this->assertDatabaseHas('ai_data_chat_logs', ['school_id' => $school->id, 'metric_key' => 'students_by_gender']);
});
