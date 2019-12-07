<?php
/**
 * (file name)
 *
 * @package App\Handlers
 * @copyright (c) 2019, Fairbanks Publishing
 * @license Proprietary
 */

namespace App\Handlers;

use App\Models\Attend;
use App\Models\Meeting;
use App\Models\User;

/**
 * Class AttendHandler
 *
 * @author David Fairbanks <david@makerdave.com>
 * @package App\Handlers
 * @version 1.0
 */
class AttendHandler extends Handler
{
    /**
     * @param User $user
     * @param Meeting $meeting
     *
     * @return Attend
     */
    public static function create(User $user, Meeting $meeting)
    {
        $attend = new Attend(['attending' => Attend::USER_ATTENDING]);
        $attend->meeting()->associate($meeting);
        $attend->user()->associate($user);
        $attend->save();

        return $attend;
    }

    /**
     * @param User $user
     * @param Meeting $meeting
     *
     * @return Attend|null
     */
    public static function get(User $user, Meeting $meeting)
    {
        return $user->attends()->where(['meeting_id' => $meeting->id])->first();
    }

    /**
     * @param Meeting $meeting
     * @param array $params
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function search(Meeting $meeting, array $params=[])
    {
        $builder = $meeting->attends();

        if(isset($params['attending'])) {
            if(is_string($params['attending'])) {
                $builder->where(['attending' => $params['attending']]);
            } elseif(is_array($params['attending'])) {
                $builder->whereIn('attending', $params['attending']);
            }
        } else {
            foreach($params as $field => $param) {
                if(strpos($field, 'user.') === 0) {
                    $builder->whereHas('user', function($query) use ($field, $param) {
                        $query->where(substr($field, 5), $param);
                    });
                }
            }
        }

        return $builder->get();
    }

    public static function update(Attend $attend, array $data=[])
    {
        if(empty($data) || !isset($data['attending'])) {
            self::$messages[] = 'Nothing to update';
            return false;
        }

        if($data['attending'] == $attend->attending) {
            return true;
        }

        // model will throw an exception if the submitted data is not allowed value
        try {
            $attend->attending = $data['attending'];
        } catch(\Exception $e) {
            self::$messages[] = $e->getMessage();
            return false;
        }

        return $attend->save();
    }
}
