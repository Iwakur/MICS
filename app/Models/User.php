<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Authenticated application user.
 *
 * In MICS HUB this model represents login accounts and access control. It does not
 * represent every future business identity directly. For example, students are
 * business records, not authenticated users.
 */
#[Fillable(['staff_id', 'username', 'email', 'password', 'role', 'is_active', 'last_login_at', 'locale'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Cast database values into richer PHP types.
     *
     * The role string becomes the UserRole enum so route and view checks can be
     * explicit and readable.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Mirror important database defaults at the model level.
     *
     * Keeping defaults here helps factories, forms, and mass assignment behave
     * the same way as the users table.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'role' => UserRole::Teacher->value,
        'is_active' => true,
        'locale' => 'en',
    ];

    /**
     * Small helper for authorization checks in routes, middleware, and views.
     */
    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    /**
     * Small helper for teacher-only UI and future teacher-scoped data rules.
     */
    public function isTeacher(): bool
    {
        return $this->role === UserRole::Teacher;
    }

    public function staffMember(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
}
