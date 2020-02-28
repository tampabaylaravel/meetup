<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;

/**
 * Class Meeting
 *
 * @author David Fairbanks <david@makerdave.com>
 * @package App\Models
 * @version 1.0
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $description
 * @property string $location
 * @property Carbon $start_time
 * @property Carbon $end_time
 * @property User $user Meeting organizer/owner
 * @property Collection $attends Collection of meeting attendance records (links to users)
 * @method static Builder search(array $params = [])
 */
class Meeting extends Model
{
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

    /**
     * Get meeting User (owner/organizer)
     * @return Relations\BelongsTo User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get meeting Users (attendees)
     * @return Relations\HasMany
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function scopeSearch(Builder $builder, array $params = [])
    {
        collect($params)->each(function ($param, $field) use ($builder) {
            if($field == 'name' && is_string($param)) {
                $builder->where('name', 'LIKE', "%{$param}%");
            } else {
                $builder->whereIn($field, collect($param));
            }
        });
    }
}
