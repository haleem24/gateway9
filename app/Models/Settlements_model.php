<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Settlements_model extends Model
{
    use HasFactory;
    protected $table = 'settlements';
    protected $primaryKey =  'id';
}
