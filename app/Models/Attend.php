<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Support\Str;

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
 * @method static Builder search(array $params = [])
 */
class Attend extends Model
{
    const USER_ATTENDING = 'yes';
    const USER_NOT_ATTENDING = 'no';
    const USER_MAYBE_ATTENDING = 'maybe';

    const USER_RELATION_DOT_NOTATION = 'user.';

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

    public function scopeSearch(Builder $builder, array $params = [])
    {
        return $builder->when(
            isset($params['attending']), function (Builder $builder) use ($params) {
            $builder->whereIn('attending', collect($params['attending']));
        }, function (Builder $builder) use ($params) {
            collect($params)
                ->filter(fn($value, $key) => Str::startsWith($key, self::USER_RELATION_DOT_NOTATION))
                ->each(
                    function ($param, $field) use ($builder) {
                        $builder->whereHas(
                            'user',
                            function ($query) use ($field, $param) {
                                $relationshipAttribute = Str::after($field, self::USER_RELATION_DOT_NOTATION);
                                $query->where($relationshipAttribute, $param);
                            }
                        );
                    }
                );
        }
        );
    }
}
