<?php

use App\Models\School;
use App\Models\User;
use App\Services\AI\OcrService;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    $this->service = app(OcrService::class);
    $this->school = School::factory()->create();
    $this->user = User::factory()->create(['school_id' => $this->school->id]);
});

it('stores the uploaded document and fails gracefully without an AI provider', function () {
    $file = UploadedFile::fake()->image('scan.jpg', 100, 100);

    $result = $this->service->process($this->school->id, $this->user->id, $file);

    expect($result->status)->toBe('failed');
    expect($result->filename)->toBe('scan.jpg');
    expect($result->file_path)->not->toBeNull();
    expect($result->error)->not->toBeNull();
});
