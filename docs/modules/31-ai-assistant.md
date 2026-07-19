# Module 31 — AI Assistant (Dynamic Provider, NO Hardcode)

## Depends On
Module 02, 07 (Online Classroom)

## ⚠️ CRITICAL: Follow Global "No Hardcoded Providers" Rule

Tidak ada nama vendor (OpenAI, Claude, Gemini) di code. Sama persis dengan pattern di Module 11b — admin sekolah input API key, model name, base URL sendiri.

## What to Build
- **Study Assistant** (chatbot tanya materi)
- **Auto-Generate RPP** (lesson plan draft)
- **Auto-Grade Essay** (rubric-based)
- **Translation** (notice multi-bahasa)
- **Smart Summary** (auto-paragraf insight kepsek)
- **Question Bank Generator** (variation soal dari topik)

## Database Schema

```php
Schema::create('ai_providers', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->string('name');                           // "OpenAI Production" / "DeepSeek" / "Local Ollama"
    $t->string('slug');
    $t->string('api_format', 40);                 // openai_compatible|anthropic_format|gemini_format|image_generic
    $t->string('base_url', 500);
    $t->text('api_key_encrypted')->nullable();
    $t->json('extra_headers')->nullable();
    $t->json('extra_config')->nullable();         // {default_temperature, max_tokens, timeout}
    $t->boolean('is_active')->default(true);
    $t->unsignedSmallInteger('priority')->default(0);
    $t->timestamps(); $t->softDeletes();
    $t->unique(['school_id', 'slug']);
});

Schema::create('ai_models', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('ai_provider_id')->constrained()->cascadeOnDelete();
    $t->string('model_name');                     // user input: 'gpt-4o-mini', 'deepseek-chat', 'claude-haiku-4-5', 'llama-3.3-70b'
    $t->string('display_name');                   // user input
    $t->enum('capability', ['chat', 'completion', 'embedding', 'image_gen', 'image_analysis', 'speech_to_text', 'tts'])->default('chat');
    $t->unsignedInteger('context_window')->default(8192);
    $t->decimal('input_price_per_1k', 10, 6)->default(0);  // user input
    $t->decimal('output_price_per_1k', 10, 6)->default(0); // user input
    $t->boolean('is_active')->default(true);
    $t->timestamps();
});

Schema::create('ai_feature_assignments', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->string('feature_key', 50);                // 'study_assistant', 'lesson_plan_gen', 'essay_grader', etc.
    $t->foreignId('ai_model_id')->constrained();
    $t->json('feature_config')->nullable();       // prompts, system messages, max output, etc.
    $t->boolean('is_enabled')->default(true);
    $t->timestamps();
    $t->unique(['school_id', 'feature_key']);
});

Schema::create('ai_usage_logs', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('user_id')->constrained();
    $t->foreignId('ai_model_id')->constrained();
    $t->string('feature_key', 50);
    $t->unsignedInteger('input_tokens');
    $t->unsignedInteger('output_tokens');
    $t->decimal('estimated_cost', 10, 6);
    $t->unsignedInteger('latency_ms');
    $t->boolean('success')->default(true);
    $t->text('error')->nullable();
    $t->timestamps();
    $t->index(['school_id', 'feature_key', 'created_at']);
});
```

## Format-Based Adapter Architecture

Lokasi: `app/Services/AI/Adapters/`
- `OpenAICompatibleAdapter` — covers OpenAI, DeepSeek, Groq, Mistral, Together, Fireworks, OpenRouter, xAI, Ollama, LM Studio, vLLM, Cerebras
- `AnthropicFormatAdapter` — covers Anthropic API
- `GeminiFormatAdapter` — covers Google Gemini
- `ImageGenericAdapter` — covers DALL-E, Stable Diffusion, Flux

**JANGAN BIKIN:** OpenAIAdapter, DeepSeekAdapter, ClaudeAdapter — itu vendor-specific, melanggar global rule.

## API Endpoints

| Method | URI | Role |
|---|---|---|
| GET/POST | `/api/v1/admin/ai-providers` | admin |
| GET/POST | `/api/v1/admin/ai-models` | admin |
| GET | `/api/v1/admin/ai-providers/{id}/fetch-models` | admin | (auto-fetch from /v1/models endpoint) |
| GET/PUT | `/api/v1/admin/ai-features` | admin |
| POST | `/api/v1/ai/study-assistant` | student |
| POST | `/api/v1/ai/lesson-plan` | teacher |
| POST | `/api/v1/ai/essay-grade` | teacher |
| GET | `/api/v1/admin/ai-usage` | admin | (cost dashboard) |

## Optional Preset Templates

Lokasi `storage/app/ai-presets/*.json`:
- `openai-compatible-cloud.json` (base_url placeholder, model placeholder)
- `anthropic-format.json`
- `gemini-format.json`
- `ollama-local.json` (base_url=http://localhost:11434/v1)
- `lm-studio-local.json`

**WAJIB:** Code TIDAK PERNAH read preset files at runtime. Hanya UI autofill convenience.

## Acceptance Criteria
- [ ] Tidak ada nama vendor di class atau code
- [ ] Admin add provider via UI tanpa code change
- [ ] Cost tracking per request, dashboard per fitur
- [ ] Setiap fitur AI bisa pakai model berbeda (assignment)
- [ ] BYOK marketing message: "Pakai key OpenAI/Anthropic/Gemini Anda sendiri"
