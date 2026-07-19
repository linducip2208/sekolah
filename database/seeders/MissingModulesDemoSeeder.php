<?php

namespace Database\Seeders;

use App\Models\Academic\Assignment;
use App\Models\Academic\AssignmentSubmission;
use App\Models\Academic\ClassRoom;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Lesson;
use App\Models\Academic\Student;
use App\Models\Academic\Subject;
use App\Models\Career\CareerAssessment;
use App\Models\Curriculum\CurriculumCompetency;
use App\Models\Curriculum\CurriculumFramework;
use App\Models\DailyReport\DailyReport;
use App\Models\Extracurricular\Extracurricular;
use App\Models\QuestionBank\QuestionBankCategory;
use App\Models\QuestionBank\QuestionBankItem;
use App\Models\School;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the 6 modules that are missing from the sidebar:
 *   07. Online Classroom    (assignments + submissions)
 *   36. Question Bank       (categories + items)
 *   40. Curriculum Mapping  (frameworks + competencies)
 *   43. Daily Report
 *   44. Extracurricular     (+ memberships + attendances)
 *   39. Career Guidance     (career_assessments)
 *
 *   php artisan db:seed --class=MissingModulesDemoSeeder
 */
class MissingModulesDemoSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::where('subdomain', 'sman1demo')->first();
        if (!$school) {
            $this->command->warn('Demo school sman1demo not found — run DemoSchoolSeeder first.');
            return;
        }

        $admin = User::where('school_id', $school->id)
            ->whereHas('roles', fn ($q) => $q->where('name', 'admin'))
            ->first();

        $subjects   = $this->seedSubjects($school);
        $framework  = $this->seedCurriculum($school, $subjects);
        $categories = $this->seedQuestionBank($school, $subjects, $admin, $framework);
        $lessons    = $this->seedLessons($school, $subjects);
        $this->seedAssignments($school, $lessons);
        $this->seedExtracurriculars($school, $admin);
        $this->seedDailyReports($school);
        $this->seedCareerAssessments($school);

        $this->command->info('=== MissingModulesDemoSeeder complete ===');
    }

    private function seedSubjects(School $school): array
    {
        $medium = DB::table('mediums')->where('school_id', $school->id)->first();
        if (!$medium) return [];

        $defs = [
            ['Matematika', 'MTK', 'theory', 4],
            ['Bahasa Indonesia', 'BIN', 'theory', 4],
            ['Bahasa Inggris', 'BING', 'theory', 4],
            ['Fisika', 'FIS', 'theory', 3],
            ['Kimia', 'KIM', 'theory', 3],
            ['Biologi', 'BIO', 'theory', 3],
            ['Sejarah', 'SEJ', 'theory', 2],
            ['Pendidikan Agama', 'PAI', 'theory', 3],
            ['PPKn', 'PKN', 'theory', 2],
            ['Penjasorkes', 'PJK', 'practical', 2],
        ];

        $ids = [];
        foreach ($defs as [$name, $code, $type, $hours]) {
            $s = Subject::firstOrCreate(
                ['school_id' => $school->id, 'code' => $code],
                ['medium_id' => $medium->id, 'name' => $name, 'type' => $type, 'credit_hours' => $hours, 'is_active' => true],
            );
            $ids[$code] = $s->id;
        }
        $this->command->info('  → seeded ' . count($ids) . ' subjects');
        return $ids;
    }

    private function seedCurriculum(School $school, array $subjects): ?CurriculumFramework
    {
        if (empty($subjects)) return null;

        $framework = CurriculumFramework::firstOrCreate(
            ['school_id' => $school->id, 'name' => 'Kurikulum Merdeka'],
            ['type' => 'national', 'config' => ['version' => '2024', 'phase' => 'F'], 'is_active' => true],
        );

        $classRooms = ClassRoom::where('school_id', $school->id)->pluck('id')->all();
        if (empty($classRooms)) return $framework;

        $cpData = [
            'MTK'  => ['Bilangan & Aljabar', 'Geometri & Pengukuran', 'Analisis Data & Peluang', 'Kalkulus'],
            'BIN'  => ['Menyimak', 'Berbicara', 'Membaca', 'Menulis'],
            'BING' => ['Listening', 'Speaking', 'Reading', 'Writing'],
            'FIS'  => ['Kinematika', 'Dinamika', 'Termodinamika', 'Listrik & Magnet'],
            'BIO'  => ['Keanekaragaman Hayati', 'Genetika', 'Ekologi', 'Bioteknologi'],
        ];

        $count = 0;
        foreach ($cpData as $code => $cps) {
            if (!isset($subjects[$code])) continue;
            foreach ($cps as $i => $cp) {
                CurriculumCompetency::firstOrCreate(
                    [
                        'school_id'                => $school->id,
                        'curriculum_framework_id'  => $framework->id,
                        'subject_id'               => $subjects[$code],
                        'class_room_id'            => $classRooms[0],
                        'code'                     => "CP-{$code}-" . ($i + 1),
                    ],
                    [
                        'description' => "Peserta didik mampu menguasai konsep {$cp} dengan pendekatan kontekstual.",
                        'level_type'  => 'cp',
                        'indicators'  => ["Memahami konsep dasar {$cp}", "Menerapkan {$cp} dalam pemecahan masalah", "Mengevaluasi solusi terkait {$cp}"],
                    ],
                );
                $count++;
            }
        }
        $this->command->info("  → seeded curriculum: 1 framework + {$count} competencies");
        return $framework;
    }

    private function seedQuestionBank(School $school, array $subjects, ?User $admin, ?CurriculumFramework $framework): array
    {
        if (empty($subjects) || !$admin) return [];

        $catNames = ['Pilihan Ganda Dasar', 'Pilihan Ganda HOTS', 'Essay', 'True/False'];
        $categoryIds = [];

        foreach ($subjects as $code => $sid) {
            foreach ($catNames as $cn) {
                $cat = QuestionBankCategory::firstOrCreate(
                    ['school_id' => $school->id, 'subject_id' => $sid, 'name' => $cn],
                );
                $categoryIds[] = $cat->id;
            }
        }

        $sampleQuestions = [
            'MTK'  => ['Hasil dari 12 × 8 adalah ...', 'Jika 2x + 5 = 17, maka nilai x adalah ...', 'Luas lingkaran dengan jari-jari 7 cm adalah ...'],
            'BIN'  => ['Apa makna kata "introspeksi"?', 'Tentukan ide pokok paragraf berikut ...', 'Buatlah kalimat efektif dari kalimat berikut ...'],
            'BING' => ['Choose the correct form: "She ___ to school every day."', 'What is the past tense of "go"?', 'Translate: "Saya sedang belajar."'],
            'FIS'  => ['Sebuah benda jatuh bebas dari ketinggian 80 m. Berapa waktu tempuhnya? (g=10 m/s²)', 'Rumus energi kinetik adalah ...', 'Hukum Newton II menyatakan bahwa ...'],
            'BIO'  => ['Proses fotosintesis menghasilkan ...', 'Organel sel yang berperan dalam respirasi seluler adalah ...', 'Apa fungsi DNA?'],
        ];

        $count = 0;
        foreach ($subjects as $code => $sid) {
            $questions = $sampleQuestions[$code] ?? ["Soal contoh untuk {$code}"];
            $subjectCats = QuestionBankCategory::where('subject_id', $sid)->pluck('id')->all();

            foreach ($questions as $i => $q) {
                $type = ['mcq','mcq','essay','true_false'][$i % 4];
                $diff = ['easy','medium','hard'][$i % 3];
                $bloom = ['C1','C2','C3','C4','C5','C6'][$i % 6];

                $opts = null;
                $key = ['A'];
                if ($type === 'mcq') {
                    $opts = ['A. 96', 'B. 100', 'C. 88', 'D. 72'];
                    $key  = ['A'];
                } elseif ($type === 'true_false') {
                    $opts = ['True', 'False'];
                    $key  = ['True'];
                } elseif ($type === 'essay') {
                    $opts = null;
                    $key  = ['Jawaban sesuai dengan konsep yang diajarkan.'];
                }

                QuestionBankItem::create([
                    'school_id'                 => $school->id,
                    'subject_id'                => $sid,
                    'question_bank_category_id' => $subjectCats[$i % count($subjectCats)] ?? null,
                    'author_id'                 => $admin->id,
                    'question_html'             => "<p>{$q}</p>",
                    'type'                      => $type,
                    'options'                   => $opts,
                    'answer_key'                => $key,
                    'explanation_html'          => "<p>Pembahasan akan ditambahkan oleh guru.</p>",
                    'difficulty'                => $diff,
                    'cognitive_level'           => $bloom,
                    'tags'                      => [$code, $diff, $bloom],
                    'is_published'              => true,
                ]);
                $count++;
            }
        }
        $this->command->info("  → seeded question bank: " . count($categoryIds) . " categories, {$count} items");
        return $categoryIds;
    }

    private function seedLessons(School $school, array $subjects): array
    {
        if (empty($subjects)) return [];

        $sections = ClassSection::where('school_id', $school->id)->limit(5)->get();
        if ($sections->isEmpty()) return [];

        $teachers = User::where('school_id', $school->id)
            ->whereHas('roles', fn ($q) => $q->where('name', 'teacher'))
            ->limit(5)->pluck('id')->all();

        if (empty($teachers)) {
            $admin = User::where('school_id', $school->id)
                ->whereHas('roles', fn ($q) => $q->where('name', 'admin'))->first();
            $teachers = $admin ? [$admin->id] : [];
        }
        if (empty($teachers)) return [];

        $ids = [];
        foreach ($sections as $section) {
            foreach ($subjects as $code => $sid) {
                $lesson = Lesson::create([
                    'school_id'        => $school->id,
                    'class_section_id' => $section->id,
                    'subject_id'       => $sid,
                    'teacher_id'       => $teachers[array_rand($teachers)],
                    'title'            => "Pelajaran {$code} — Bab Pengantar",
                    'description'      => "Materi pengantar mata pelajaran {$code} untuk semester ini.",
                ]);
                $ids[] = $lesson->id;
            }
        }
        $this->command->info('  → seeded ' . count($ids) . ' lessons');
        return $ids;
    }

    private function seedAssignments(School $school, array $lessons): void
    {
        if (empty($lessons)) return;

        $students = Student::where('school_id', $school->id)->limit(50)->pluck('id')->all();
        if (empty($students)) return;

        $count = 0;
        $subCount = 0;
        foreach (array_slice($lessons, 0, 20) as $lessonId) {
            $assignment = Assignment::create([
                'school_id'    => $school->id,
                'lesson_id'    => $lessonId,
                'title'        => 'Tugas ' . fake()->numberBetween(1, 5) . ': ' . fake()->sentence(3),
                'instructions' => "Kerjakan soal-soal berikut dengan rapi.\n\n1. Baca petunjuk dengan teliti.\n2. Tulis jawaban di lembar terpisah.\n3. Kumpulkan sebelum batas waktu.",
                'due_date'     => now()->addDays(rand(3, 14)),
                'total_marks'  => 100,
            ]);
            $count++;

            $sampleStudents = array_slice($students, 0, rand(15, 30));
            foreach ($sampleStudents as $studentId) {
                AssignmentSubmission::create([
                    'assignment_id' => $assignment->id,
                    'student_id'    => $studentId,
                    'answer'        => 'Jawaban siswa: ' . fake()->paragraph(2),
                    'file'          => null,
                    'is_late'       => rand(0, 9) < 2,
                    'marks'         => rand(60, 95),
                    'feedback'      => rand(0, 2) === 0 ? 'Bagus, pertahankan.' : null,
                ]);
                $subCount++;
            }
        }
        $this->command->info("  → seeded {$count} assignments + {$subCount} submissions");
    }

    private function seedExtracurriculars(School $school, ?User $admin): void
    {
        $items = [
            ['Pramuka',       '⛺', 'Senin 15:00–17:00', 200, 0],
            ['Paskibra',      '🚩', 'Selasa 15:00–17:00', 50, 0],
            ['English Club',  '🇬🇧', 'Rabu 15:00–16:30',  40, 50000],
            ['Robotik',       '🤖', 'Kamis 15:00–17:00', 25, 150000],
            ['Basket',        '🏀', 'Jumat 15:30–17:30', 30, 25000],
            ['Futsal',        '⚽', 'Sabtu 08:00–10:00', 30, 25000],
            ['Tari Tradisional','💃','Senin 16:00–17:30',25, 75000],
            ['Musik',         '🎸', 'Rabu 16:00–18:00',  20, 100000],
            ['Karya Ilmiah Remaja','🔬','Kamis 14:00–16:00',30, 0],
            ['Tahfidz',       '🕌', 'Setiap hari 16:00–17:00', 60, 0],
        ];

        $extras = [];
        foreach ($items as [$name, $icon, $schedule, $cap, $fee]) {
            $e = Extracurricular::firstOrCreate(
                ['school_id' => $school->id, 'name' => $name],
                [
                    'icon'          => $icon,
                    'description'   => "Ekstrakurikuler {$name} terbuka untuk semua siswa.",
                    'coach_id'      => $admin?->id,
                    'schedule'      => ['days' => [explode(' ', $schedule)[0]], 'time' => substr($schedule, strpos($schedule, ' ') + 1)],
                    'capacity'      => $cap,
                    'fee_per_month' => $fee,
                    'is_active'     => true,
                ],
            );
            $extras[] = $e->id;
        }

        $students = Student::where('school_id', $school->id)->limit(200)->pluck('id')->all();
        if (empty($students)) {
            $this->command->info('  → seeded ' . count($extras) . ' extracurriculars (no students for memberships)');
            return;
        }

        $memberCount = 0;
        $attCount = 0;
        foreach ($extras as $eId) {
            $picks = array_slice($students, 0, rand(15, 40));
            foreach ($picks as $sId) {
                $exists = DB::table('student_extracurriculars')
                    ->where('extracurricular_id', $eId)->where('student_id', $sId)->exists();
                if ($exists) continue;

                DB::table('student_extracurriculars')->insert([
                    'school_id'          => $school->id,
                    'extracurricular_id' => $eId,
                    'student_id'         => $sId,
                    'joined_at'          => now()->subMonths(rand(1, 6)),
                    'level'              => ['beginner','intermediate','advanced'][rand(0,2)],
                    'is_active'          => true,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
                $memberCount++;
            }

            $sessionDays = [now()->subDays(7), now()->subDays(14), now()->subDays(21)];
            foreach ($sessionDays as $day) {
                foreach (array_slice($picks, 0, 10) as $sId) {
                    DB::table('extracurricular_attendances')->insert([
                        'school_id'          => $school->id,
                        'extracurricular_id' => $eId,
                        'student_id'         => $sId,
                        'session_date'       => $day->toDateString(),
                        'status'             => ['present','present','present','late','absent'][rand(0,4)],
                        'marked_by'          => $admin?->id ?? 1,
                        'created_at'         => now(),
                        'updated_at'         => now(),
                    ]);
                    $attCount++;
                }
            }
        }
        $this->command->info('  → seeded ' . count($extras) . " extracurriculars, {$memberCount} memberships, {$attCount} attendances");
    }

    private function seedDailyReports(School $school): void
    {
        $students = Student::where('school_id', $school->id)->limit(100)->pluck('id')->all();
        if (empty($students)) return;

        $count = 0;
        $today = Carbon::today();
        foreach ($students as $studentId) {
            for ($d = 0; $d < 3; $d++) {
                $date = $today->copy()->subDays($d);
                DailyReport::firstOrCreate(
                    [
                        'school_id'   => $school->id,
                        'student_id'  => $studentId,
                        'report_date' => $date,
                    ],
                    [
                        'attendance'      => ['status' => 'present', 'time_in' => '06:55'],
                        'subjects_today'  => [
                            ['name' => 'Matematika', 'topic' => 'Persamaan Kuadrat'],
                            ['name' => 'Bahasa Indonesia', 'topic' => 'Teks Argumentasi'],
                        ],
                        'homework_due'    => [['subject' => 'Matematika', 'due' => $date->copy()->addDays(2)->toDateString()]],
                        'canteen_summary' => ['spent' => rand(10000, 25000), 'items' => rand(1, 3)],
                        'clinic_visit'    => null,
                        'discipline_events' => [],
                        'wellness_checkin'  => ['mood' => ['happy','neutral','tired'][rand(0,2)]],
                        'teacher_notes'     => ['note' => rand(0,1) ? 'Aktif di kelas hari ini.' : 'Perlu lebih konsentrasi.'],
                        'sent_at'           => $date->copy()->setTime(16, 30),
                    ],
                );
                $count++;
            }
        }
        $this->command->info("  → seeded {$count} daily reports");
    }

    private function seedCareerAssessments(School $school): void
    {
        $students = Student::where('school_id', $school->id)->limit(50)->pluck('id')->all();
        if (empty($students)) return;

        $tests = [
            'riasec' => ['Realistic','Investigative','Artistic','Social','Enterprising','Conventional'],
            'mbti'   => ['INTJ','INTP','ENTJ','ENTP','INFJ','INFP','ISTJ','ISFJ'],
            'minat'  => ['Sains','Sosial','Bahasa','Seni','Teknik','Bisnis'],
        ];

        $count = 0;
        foreach ($students as $studentId) {
            foreach (array_keys($tests) as $type) {
                if (rand(0, 1) === 0) continue;
                CareerAssessment::create([
                    'school_id'  => $school->id,
                    'student_id' => $studentId,
                    'test_type'  => $type,
                    'responses'  => array_map(fn () => rand(1, 5), range(1, 20)),
                    'result'     => [
                        'top_category' => $tests[$type][array_rand($tests[$type])],
                        'score'        => rand(70, 95),
                        'recommendations' => ['Lanjut ke ' . $tests[$type][array_rand($tests[$type])] . ' di perguruan tinggi'],
                    ],
                    'taken_at'   => now()->subDays(rand(1, 60)),
                ]);
                $count++;
            }
        }
        $this->command->info("  → seeded {$count} career assessments");
    }
}
