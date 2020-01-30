<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\UpdateReservationRequest;
use App\Models\Reservation;
use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    /**
     * Get all meeting attendees
     *
     * @param Request $request
     *  Search parameters can include:
     *      attending state (yes|no|maybe)
     *      user attributes through dot notation i.e. user.name
     * @param Meeting $meeting
     *
     * @return JsonResponse
     */
    public function index(Request $request, Meeting $meeting)
    {
        $attendees = $meeting->reservations()->search($request->input())->get();

        return response()->json(
            [
                'success'  => true,
                'attendees' => $attendees,
                'rowCount' => $attendees->count()
            ]
        );
    }

    /**
     * User states they are attending a meeting
     *
     * Gets user from request so only the logged in user can say they are attending
     *
     * @param Request $request
     * @param Meeting $meeting
     *
     * @return JsonResponse
     */
    public function store(Request $request, Meeting $meeting)
    {
        $attend = new Reservation(['attending' => Reservation::USER_ATTENDING]);
        $attend->meeting()->associate($meeting);
        $attend->user()->associate($request->user());
        $attend->save();

        return response()->json(
            [
                'success' => true,
                'message' => 'Successfully set user as attending the meeting'
            ]
        );
    }

    /**
     * Get the a single user's attend record for a single meeting
     *
     * @param Meeting $meeting
     * @param User $user
     *
     * @return JsonResponse
     */
    public function show(Meeting $meeting, User $user)
    {
        $attend = $user->reservations()->where(['meeting_id' => $meeting->getKey()])->first();

        return response()->json(
            [
                'success' => ($attend !== null),
                'attendee' => $attend
            ]
        );
    }

    /**
     * User changes their attending status for a meeting
     *
     * Gets user from request so only the logged in user can say they are attending
     *
     * @param UpdateReservationRequest $request
     * @param Meeting $meeting
     *
     * @return JsonResponse
     */
    public function update(UpdateReservationRequest $request, Meeting $meeting)
    {
        /* @var Reservation $attend */
        $attend = $request->user()->reservations()->where(['meeting_id' => $meeting->getKey()])->first();

        if($attend === null) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Specified user is not attending the meeting'
                ],
                404
            );
        }

        $attend->attending = $request->input('attending');

        if($attend->save() == false) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Error setting attending status'
                ]
            );
        }

        return response()->json(
            [
                'success' => true,
                'message' => 'Successfully updated attending status'
            ]
        );
    }

    /**
     * User changes their attending status to NO
     *
     * Gets user from request so only the logged in user can say they are attending
     *
     * @param Request $request
     * @param Meeting $meeting
     *
     * @return JsonResponse
     */
    public function destroy(Request $request, Meeting $meeting)
    {
        /* @var Reservation $attend */
        $attend = $request->user()->reservations()->where(['meeting_id' => $meeting->getKey()])->first();

        if($attend === null) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Specified user is not attending the meeting'
                ],
                404
            );
        }

        $attend->attending = Reservation::USER_NOT_ATTENDING;

        if($attend->save() == false) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Error setting attending status'
                ]
            );
        }

        return response()->json(
            [
                'success' => true,
                'message' => 'Successfully updated attending status'
            ]
        );
    }
}
