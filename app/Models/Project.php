<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $guarded = false;
    protected $with = ['type'];
    protected $casts = ['created_at_time' => 'date', 'contracted_at' => 'date'];

    public function getFormattedDeadlineAttribute($value)
    {
        return Carbon::parse($this->deadline)->format('d.m.y');
    }


    public function type()
    {
        return $this->belongsTo(Type::class, 'type_id', 'id');
    }
}
