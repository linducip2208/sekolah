# Module 36 — Question Bank (Bank Soal)

## Depends On
Module 04, 08 (Exam Engine)

## What to Build
Bank soal reusable per mapel dengan tagging level/tipe, import dari Word/Excel, generate ujian random dari bank, IRT (Item Response Theory) basic.

## Database Schema

```php
Schema::create('question_bank_categories', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('subject_id')->constrained();
    $t->string('name');                        // "Bab 1: Bilangan Bulat"
    $t->foreignId('parent_id')->nullable()->constrained('question_bank_categories');
    $t->timestamps();
});

Schema::create('question_bank_items', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('subject_id')->constrained();
    $t->foreignId('question_bank_category_id')->nullable()->constrained();
    $t->foreignId('author_id')->constrained('users');
    $t->text('question_html');
    $t->enum('type', ['mcq','multi_select','true_false','essay','fill_blank','matching','numeric']);
    $t->json('options')->nullable();           // for mcq/multi/matching
    $t->json('answer_key');
    $t->text('explanation_html')->nullable();
    $t->enum('difficulty', ['easy','medium','hard']);
    $t->enum('cognitive_level', ['c1','c2','c3','c4','c5','c6']); // Bloom's
    $t->json('tags')->nullable();
    $t->unsignedInteger('used_count')->default(0);
    $t->decimal('avg_score_pct', 5, 2)->nullable(); // analytics
    $t->decimal('discrimination', 5, 3)->nullable();
    $t->boolean('is_published')->default(true);
    $t->timestamps(); $t->softDeletes();
    $t->index(['school_id', 'subject_id', 'difficulty']);
});
```

## Acceptance Criteria
- [ ] Import dari Word (.docx parser pakai PHPWord)
- [ ] Generate exam: random N soal dari kategori X dengan distribusi level
- [ ] IRT analytics: difficulty + discrimination per soal
- [ ] Tag-based search & filter
