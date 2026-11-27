<?php

namespace App\Support;

use App\Models\Portfolio;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class StudentDocumentCache
{
    private const TTL_MINUTES = 5;

    public static function studentDirectory(): Collection
    {
        return Cache::remember('students:directory', now()->addMinutes(self::TTL_MINUTES), function () {
            return User::where('role', 'student')
                ->with(['groups', 'reviewers'])
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();
        });
    }

    public static function portfolioForStudent(int $studentId): ?Portfolio
    {
        $cached = Cache::remember("students:{$studentId}:portfolio", now()->addMinutes(self::TTL_MINUTES), function () use ($studentId) {
            return ['portfolio' => Portfolio::with('evidence')->where('user_id', $studentId)->first()];
        });

        return $cached['portfolio'];
    }

    public static function forgetStudentDirectory(): void
    {
        Cache::forget('students:directory');
    }

    public static function forgetStudent(int $studentId): void
    {
        self::forgetStudentDirectory();
        self::forgetPortfolio($studentId);
    }

    public static function forgetPortfolio(int $studentId): void
    {
        Cache::forget("students:{$studentId}:portfolio");
    }
}
