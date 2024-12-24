<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JoinGroupRequest extends Model
{
    use HasFactory;

    protected $table = 'join_group_request';

    protected $fillable = [
        'group_id',
        'user_id',
        'request_status',
        'requested_at',
    ];

    public $timestamps = false;

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
