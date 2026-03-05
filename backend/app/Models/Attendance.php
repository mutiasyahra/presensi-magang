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
        'is_auto'
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
