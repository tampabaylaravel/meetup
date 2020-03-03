<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Meeting;
use Illuminate\Auth\Access\HandlesAuthorization;

class MeetingPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any meetings.
     *
     * @param User $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
    }

    /**
     * Determine whether the user can view the meeting.
     *
     * @param User $user
     * @param Meeting $meeting
     * @return mixed
     */
    public function view(User $user, Meeting $meeting)
    {
        return true;
    }

    /**
     * Determine whether the user can create meetings.
     *
     * @param User $user
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the meeting.
     *
     * @param User $user
     * @param Meeting $meeting
     * @return mixed
     */
    public function update(User $user, Meeting $meeting)
    {
        return $user->is($meeting->user);
    }

    /**
     * Determine whether the user can delete the meeting.
     *
     * @param User $user
     * @param Meeting $meeting
     * @return mixed
     */
    public function delete(User $user, Meeting $meeting)
    {
        return $user->is($meeting->user);
    }

    /**
     * Determine whether the user can restore the meeting.
     *
     * @param User $user
     * @param Meeting $meeting
     * @return mixed
     */
    public function restore(User $user, Meeting $meeting)
    {
        return $user->is($meeting->user);
    }

    /**
     * Determine whether the user can permanently delete the meeting.
     *
     * @param User $user
     * @param Meeting $meeting
     * @return mixed
     */
    public function forceDelete(User $user, Meeting $meeting)
    {
        return $user->is($meeting->user);
    }
}
