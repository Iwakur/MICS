<?php

namespace App\Models;

use Database\Factories\StaffRoleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'can_teach', 'is_active', 'note'])]
class StaffRole extends Model
{
    /** @use HasFactory<StaffRoleFactory> */
    use HasFactory;

    protected $attributes = ['can_teach' => false, 'is_active' => true];

    protected function casts(): array
    {
        return ['can_teach' => 'boolean', 'is_active' => 'boolean'];
    }

    public function staffMembers(): HasMany
    {
        return $this->hasMany(Staff::class);
    }
}
