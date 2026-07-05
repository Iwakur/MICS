<?php

namespace App\Models;

use Database\Factories\StaffRoleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'note'])]
class StaffRole extends Model
{
    /** @use HasFactory<StaffRoleFactory> */
    use HasFactory;

    public function staffMembers(): HasMany
    {
        return $this->hasMany(Staff::class);
    }
}
