<?php

namespace App\Services;

use App\Models\Inventory\Asset;

class AssetDepreciationService
{
    public function calculateMonthlyDepreciation(Asset $asset): int
    {
        if (!$asset->useful_life_years || $asset->useful_life_years <= 0) {
            return 0;
        }

        $cost = $asset->purchase_price ?? 0;
        $salvage = $asset->salvage_value ?? 0;
        $depreciableBase = max(0, $cost - $salvage);
        $totalMonths = $asset->useful_life_years * 12;

        if ($totalMonths <= 0) {
            return 0;
        }

        if ($asset->depreciation_method === 'double_declining') {
            $straightRate = 1 / $totalMonths;
            $decliningRate = $straightRate * 2;
            $currentBook = $this->currentBookValue($asset);
            return max(0, (int) round($currentBook * $decliningRate));
        }

        return (int) round($depreciableBase / $totalMonths);
    }

    public function depreciationSchedule(Asset $asset): array
    {
        $schedule = [];
        $cost = $asset->purchase_price ?? 0;
        $salvage = $asset->salvage_value ?? 0;
        $life = $asset->useful_life_years ?? 0;

        if ($life <= 0) {
            return [];
        }

        $totalMonths = $life * 12;
        $bookValue = $cost;

        for ($month = 1; $month <= $totalMonths; $month++) {
            if ($asset->depreciation_method === 'double_declining') {
                $straightRate = 1 / $totalMonths;
                $dep = max(0, $bookValue * $straightRate * 2);
                if ($bookValue - $dep < $salvage) {
                    $dep = max(0, $bookValue - $salvage);
                }
            } else {
                $dep = $totalMonths > 0 ? ($cost - $salvage) / $totalMonths : 0;
                if ($bookValue - $dep < $salvage) {
                    $dep = max(0, $bookValue - $salvage);
                }
            }

            $bookValue = max($salvage, $bookValue - $dep);
            $schedule[] = [
                'month' => $month,
                'year' => ceil($month / 12),
                'depreciation' => (int) round($dep),
                'book_value' => (int) round($bookValue),
            ];

            if ($bookValue <= $salvage) {
                break;
            }
        }

        return $schedule;
    }

    public function currentBookValue(Asset $asset): int
    {
        $cost = $asset->purchase_price ?? 0;
        $purchaseDate = $asset->purchase_date;
        if (!$purchaseDate) {
            return $cost;
        }

        $monthsElapsed = max(0, (int) $purchaseDate->diffInMonths(now()));
        $totalMonths = ($asset->useful_life_years ?? 0) * 12;
        $salvage = $asset->salvage_value ?? 0;
        $bookValue = $cost;

        if ($totalMonths <= 0) {
            return $cost;
        }

        for ($i = 0; $i < min($monthsElapsed, $totalMonths); $i++) {
            if ($asset->depreciation_method === 'double_declining') {
                $straightRate = 1 / $totalMonths;
                $dep = max(0, $bookValue * $straightRate * 2);
            } else {
                $dep = ($cost - $salvage) / $totalMonths;
            }
            $bookValue = max($salvage, $bookValue - $dep);
        }

        return (int) round($bookValue);
    }

    public function calculateAllForSchool(int $schoolId): void
    {
        Asset::where('school_id', $schoolId)->chunk(100, function ($assets) {
            foreach ($assets as $asset) {
                $asset->update(['monthly_depreciation' => $this->calculateMonthlyDepreciation($asset)]);
            }
        });
    }
}
