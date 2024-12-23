<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Administrator extends Model
{
    protected $table = 'administrator'; 

    protected $fillable = ['user_id']; 

    public $timestamps = false; 
    // Modelo Administrator.php
public function group()
{
    return $this->belongsTo(Group::class);
}

public function user()
{
    return $this->belongsTo(User::class);
}

}
