<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCongregation;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use BelongsToCongregation;

    protected $fillable = ['congregation_id', 'name', 'gender'];

    const GENDERS = [
        'brother' => 'Hermano',
        'sister'  => 'Hermana',
    ];
}
