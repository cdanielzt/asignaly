<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCongregation;
use Illuminate\Database\Eloquent\Model;

class Attendant extends Model
{
    use BelongsToCongregation;

    protected $fillable = ['congregation_id', 'name', 'role'];

    const ROLES = ['Elder', 'Ministerial Servant', 'Publisher'];
}
