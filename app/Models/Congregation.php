<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Congregation extends Model
{
    protected $fillable = ['name', 'city', 'attendant_roles'];

    protected $casts = ['attendant_roles' => 'array'];

    /** Role key => label map for this congregation, falling back to the defaults. */
    public function attendantRoles(): array
    {
        return $this->attendant_roles ?: Schedule::ROLES;
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function attendants(): HasMany
    {
        return $this->hasMany(Attendant::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class);
    }
}
