<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected static function booted(): void
    {
        static::saving(function (User $user) {
            $user->education = ($user->role ?? 'student') === 'student' ? ($user->education ?: 'Software Developer') : null;
        });

        static::creating(function (User $user) {
            $user->role ??= 'student';
            if ($user->isStudent()) {
                $parts = preg_split('/\s+/', trim((string) $user->name), 2);
                $user->first_name ??= $parts[0] ?? null;
                $user->last_name ??= $parts[1] ?? null;
                $user->preferred_name ??= $user->first_name;
            }
        });
        static::created(function (User $user) {
            if ($user->isStudent()) {
                $newGroup = Group::where('lifecycle_status', 'nieuw')->first();
                if ($newGroup) {
                    $user->groups()->syncWithoutDetaching([$newGroup->id]);
                    if ($user->group_id !== $newGroup->id) {
                        $user->forceFill(['group_id' => $newGroup->id])->saveQuietly();
                    }
                }
            }
        });
    }

    public function portfolios(): HasMany
    {
        return $this->hasMany(Portfolio::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function groups(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Group::class)->withTimestamps();
    }

    public function reviewers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(self::class, 'student_reviewer', 'student_id', 'teacher_id')->withTimestamps();
    }

    public function reviewedStudents(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(self::class, 'student_reviewer', 'teacher_id', 'student_id')->withTimestamps();
    }

    public function hasLifecycleGroup(string $status): bool
    {
        return $this->groups()->where('lifecycle_status', $status)->exists();
    }

    public function portfolioIsLocked(): bool
    {
        return $this->hasLifecycleGroup('geslaagd') || $this->hasLifecycleGroup('weg');
    }

    public function canOnlySubmitEvidence(): bool
    {
        return $this->hasLifecycleGroup('herkansen');
    }

    public function getEducationAttribute($value): ?string
    {
        if (!$this->isStudent()) return null;
        return $this->groups()->whereNull('lifecycle_status')->with('educationProgram')->get()
            ->first(fn ($group) => $group->educationProgram !== null)?->educationProgram?->name ?? $value ?? 'Software Developer';
    }

    public function displayName(): string
    {
        if (! $this->isStudent()) {
            return trim($this->name);
        }

        $first = $this->preferred_name ?: ($this->first_name ?: $this->name);
        return trim($first.' '.($this->last_name ?? ''));
    }

    public function teachingGroups(): HasMany
    {
        return $this->hasMany(Group::class, 'teacher_id');
    }

    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isTeacher(): bool { return $this->role === 'teacher'; }
    public function isStudent(): bool { return $this->role === 'student'; }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'leerweg',
        'email',
        'password',
        'role',
        'group_id',
        'first_name',
        'last_name',
        'preferred_name',
        'student_number',
        'education',
        'study_type',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'mfa_secret',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'mfa_enabled' => 'boolean',
            'is_admin' => 'boolean',
        ];
    }
}
