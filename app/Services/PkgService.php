<?php

namespace App\Services;

class PkgService
{
    public function getRecommendation(float $finalScore): string
    {
        return match (true) {
            $finalScore >= 91 => 'sangat_baik',
            $finalScore >= 76 => 'baik',
            $finalScore >= 61 => 'cukup',
            default           => 'kurang',
        };
    }

    public function getRecommendationLabel(string $recommendation): string
    {
        return match ($recommendation) {
            'sangat_baik' => 'Sangat Baik',
            'baik'        => 'Baik',
            'cukup'       => 'Cukup',
            'kurang'      => 'Kurang',
            default       => '-',
        };
    }

    public function getRecommendationColor(string $recommendation): string
    {
        return match ($recommendation) {
            'sangat_baik' => '#16A34A',
            'baik'        => '#2563EB',
            'cukup'       => '#EAB308',
            'kurang'      => '#DC2626',
            default       => '#64748B',
        };
    }

    public function calculateWeightedScore(array $scores, object $competencies): float
    {
        $totalScore = 0;
        $totalWeight = 0;

        foreach ($competencies as $comp) {
            if (isset($scores[$comp->id])) {
                $totalScore += (float) $scores[$comp->id] * $comp->weight;
                $totalWeight += $comp->weight;
            }
        }

        return $totalWeight > 0 ? round($totalScore / $totalWeight, 2) : 0;
    }
}
