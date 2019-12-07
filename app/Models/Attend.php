<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;

/**
 * Class Attend
 *
 * @author David Fairbanks <david@makerdave.com>
 * @package App\Models
 * @version 1.0
 *
 * @property int $user_id
 * @property int $meeting_id
 * @property string $attending
 * @property User $user Meeting attendee
 * @property Meeting $meeting
 */
class Attend extends Model
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
        'user_id', 'meeting_id', 'attending'
    ];

    public static function attendingEnumeration()
    {
        return[self::USER_ATTENDING, self::USER_NOT_ATTENDING, self::USER_MAYBE_ATTENDING];
    }

    /**
     * @param $state
     *
     * @throws \Exception
     */
    public function setAttendingAttribute($state)
    {
        if(!in_array($state, self::attendingEnumeration()))
            throw new \Exception('Invalid attending state');

        $this->attributes['attending'] = $state;
    }

    /**
     * @return Relations\BelongsTo User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return Relations\BelongsTo Meeting
     */
    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }
}
