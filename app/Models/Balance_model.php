<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Balance_model extends Model
{
    use HasFactory;
    protected $table = 'balance';
    protected $primaryKey = 'id';
}
