<?php

namespace App\Console\Commands;

use App\Models\Academic\Exam;
use App\Services\Academic\ItemAnalysisService;
use Illuminate\Console\Command;

class AnalyzeExamItems extends Command
{
    protected $signature = 'exam:item-analysis {exam : ID ujian} {--all : Analisis semua ujian yang sudah dikumpulkan}';

    protected $description = 'Hitung analisis butir soal (difficulty, discrimination, distractor) untuk sebuah ujian';

    public function handle(ItemAnalysisService $service): int
    {
        if ($this->option('all')) {
            $exams = Exam::withCount('results')
                ->having('results_count', '>', 0)
                ->get();

            foreach ($exams as $exam) {
                $data = $service->analyze($exam);
                $this->line(sprintf(
                    '[%d] %s — %d siswa, %d soal dianalisis',
                    $exam->id, $exam->title, $data['total_students'], $data['summary']['hard'] + $data['summary']['medium'] + $data['summary']['easy']
                ));
            }

            $this->info('Selesai: ' . $exams->count() . ' ujian dianalisis.');
            return self::SUCCESS;
        }

        $exam = Exam::findOrFail($this->argument('exam'));
        $data = $service->analyze($exam);

        $this->info(sprintf('Ujian "%s": %d siswa, %d soal.', $exam->title, $data['total_students'], $data['total_questions']));
        $this->table(
            ['No', 'Soal', 'Tingkat', 'Sulit/Sedang/Mudah', 'Daya Beda', 'Interpretasi'],
            $data['questions']->map(function ($q, $i) {
                return [
                    $i + 1,
                    \Illuminate\Support\Str::limit(strip_tags($q['question']), 40),
                    $q['difficulty'] ?? '—',
                    $q['difficulty_label'],
                    $q['discrimination'] ?? '—',
                    $q['discrimination_label'],
                ];
            })->all()
        );

        return self::SUCCESS;
    }
}
