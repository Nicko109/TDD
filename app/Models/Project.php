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
//    protected $dates = ['created_at_time', 'contracted_at', 'deadline'];

    public function getFormattedCreatedAtTimeAttribute($value)
    {
        return Carbon::parse($this->created_at_time)->format('d.m.y');
    }

    public function getFormattedContractedAtAttribute($value)
    {
        return Carbon::parse($this->contracted_at)->format('d.m.y');
    }

    public function getFormattedDeadlineAttribute($value)
    {
        return Carbon::parse($this->deadline)->format('d.m.y');
    }


    public function type()
    {
        return $this->belongsTo(Type::class, 'type_id', 'id');
    }
}
