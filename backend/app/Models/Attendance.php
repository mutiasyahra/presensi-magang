<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_date',
        'clock_in',
        'clock_in_photo',
        'clock_in_lat',
        'clock_in_long',
        'rencana_kegiatan',
        'clock_out',
        'clock_out_photo',
        'clock_out_lat',
        'clock_out_long',
        'progress_kegiatan',
        'evidence',
        'status',
        'clock_in_location',
        'clock_out_location',
        'clock_in_status',
        'clock_out_status',
        'is_auto',
        'is_verified'
    ];

    protected $casts = [
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function intern()
    {
        return $this->belongsTo(Intern::class);
    }    
}
