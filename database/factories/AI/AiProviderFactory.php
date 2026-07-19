<?php

namespace Database\Factories\AI;

use App\Models\AI\AiProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AiProviderFactory extends Factory
{
    protected $model = AiProvider::class;

    public function definition(): array
    {
        $name = fake()->randomElement(['OpenAI Compatible', 'Local LLM', 'Cloud Inference', 'Self-hosted']);
        return [
            'name'        => $name . ' ' . Str::random(3),
            'slug'        => Str::slug($name) . '-' . Str::lower(Str::random(4)),
            'api_format'  => fake()->randomElement(['openai_compatible', 'anthropic_format', 'gemini_format']),
            'base_url'    => 'https://api.example.com/v1',
            'is_active'   => true,
            'priority'    => 0,
        ];
    }
}
