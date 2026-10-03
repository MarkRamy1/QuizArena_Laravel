<?php

namespace App\Helpers;

use Carbon\Carbon;

class StreakHelper
{
    /**
     * Evaluates streak based on previous activity date string ("YYYY-MM-DD")
     * and current date string ("YYYY-MM-DD").
     */
    public static function evaluateStreak(?string $lastDateStr, string $currentDateStr, int $currentStreak): array
    {
        if (empty($lastDateStr)) {
            return ['streak' => 1, 'streakUpdated' => true];
        }

        if ($lastDateStr === $currentDateStr) {
            return ['streak' => max(1, $currentStreak), 'streakUpdated' => false];
        }

        $last = Carbon::parse($lastDateStr)->startOfDay();
        $curr = Carbon::parse($currentDateStr)->startOfDay();

        if ($last->copy()->addDay()->isSameDay($curr)) {
            return ['streak' => $currentStreak + 1, 'streakUpdated' => true];
        }

        // Gap > 1 day -> reset streak to 1
        return ['streak' => 1, 'streakUpdated' => true];
    }
}
