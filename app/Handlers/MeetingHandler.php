<?php
/**
 * (file name)
 *
 * @package App\Handlers
 * @copyright (c) 2019, Fairbanks Publishing
 * @license Proprietary
 */

namespace App\Handlers;

use App\Models\Meeting;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;

/**
 * Class MeetingHandler
 *
 * @author David Fairbanks <david@makerdave.com>
 * @package App\Handlers
 * @version 1.0
 */
class MeetingHandler extends Handler
{
    public static function validatorForCreate(array $params=[])
    {
        return Validator::make(
            $params,
            [
                'name'        => 'required|string|max:64',
                'description' => 'string',
                'location'    => 'string|max:128',
                'start_time'  => 'date',
                'end_time'    => 'date|nullable'
            ]
        );
    }

    public static function validatorForUpdate(array $params=[])
    {
        return Validator::make(
            $params,
            [
                'name'        => 'string|min:5|max:64',
                'description' => 'string',
                'location'    => 'string|max:128',
                'start_time'  => 'date',
                'end_time'    => 'date|nullable'
            ]
        );
    }

    /**
     * @param User $user
     * @param array $params
     *
     * @return Meeting|null
     */
    public static function create(User $user, array $params=[])
    {
        /* @var Meeting $meeting */
        $meeting = $user->meetings()->create($params);

        if($meeting instanceof Meeting == false) {
            self::$messages[] = 'Error creating new meeting';
            return null;
        }

        return $meeting;
    }

    /**
     * @param array $params
     *
     * @return Collection of Meeting
     */
    public static function search(array $params=[])
    {
        $builder = Meeting::query();

        if(!empty($params)) {
            foreach($params as $field => $param) {
                if(is_array($param)) {
                    $builder->whereIn($field, $param);
                } else {
                    $builder->where($field, $param);
                }
            }
        }

        return $builder->get();
    }

    /**
     * @param Meeting $meeting
     * @param array $data
     *
     * @return boolean
     */
    public static function update(Meeting &$meeting, array $data=[])
    {
        if(empty($data)) {
            self::$messages[] = 'Nothing to update';
            return false;
        }

        $modified = false;
        foreach($data as $field => $value) {
            if(!in_array($field, $meeting->getFillable()))
                continue;

            if($meeting->{$field} != $value) {
                $modified = true;
                $meeting->{$field} = $value;
            }
        }

        if($modified == false) {
            self::$messages[] = 'Nothing to update';
            return false;
        }

        return $meeting->save();
    }

    /**
     * @param Meeting $meeting
     *
     * @return boolean
     */
    public static function delete(Meeting $meeting)
    {
        try {
            $r = $meeting->delete();
        } catch(\Exception $e) {
            // @todo Log error
            self::$messages[] = 'Error deleting meeting';
            return false;
        }

        return ($r === true);
    }
}
