<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Administrator extends Model
{
    protected $table = 'administrator'; 

    protected $fillable = ['user_id']; 

    public $timestamps = false; 
}
