<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reaction extends Model
{

    protected $table = 'reaction';
    public $timestamps = false;

    use HasFactory;

    protected $fillable = [
        'user_id',
        'target_id',
        'target_type',
        'reaction_type',
        'reaction_date'
    ];

    protected $casts = [
        'reaction_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function target()
    {
        return $this->morphTo();
    }

    public function getMorphClass()
    {
        return strtolower(class_basename($this)); 
    }
}