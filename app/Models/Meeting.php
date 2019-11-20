<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Meeting
 *
 * @author David Fairbanks <david@makerdave.com>
 * @package App\Models
 * @version 1.0
 *
 * @property int $user_id
 * @property string $name
 * @property string $description
 * @property string $location
 * @property Carbon $start_time
 * @property Carbon $end_time
 */
class Meeting extends Model
{
    const USER_ATTENDING = 'yes';
    const USER_NOT_ATTENDING = 'no';
    const USER_MAYBE_ATTENDING = 'maybe';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'name', 'description', 'location', 'start_time', 'end_time'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time'   => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendees()
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['attending'])
            ->withTimestamps();
    }
}
