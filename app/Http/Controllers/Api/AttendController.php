<?php

namespace App\Http\Controllers\Api;

use App\Handlers\AttendHandler;
use App\Models\Attend;
use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AttendController extends Controller
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
     * @return Response
     */
    public function index(Request $request, Meeting $meeting)
    {
        $attendees = AttendHandler::search($meeting, $request->input());

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
     * @return Response
     */
    public function store(Request $request, Meeting $meeting)
    {
        AttendHandler::create($request->user(), $meeting);

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
     * @return Response
     */
    public function show(Meeting $meeting, User $user)
    {
        $attend = AttendHandler::get($user, $meeting);

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
     * @param Request $request
     * @param Meeting $meeting
     *
     * @return Response
     */
    public function update(Request $request, Meeting $meeting)
    {
        $attend = AttendHandler::get($request->user(), $meeting);

        if($attend === null) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Specified user is not attending the meeting'
                ],
                404
            );
        }

        $r = AttendHandler::update($attend, $request->input());

        return response()->json(
            [
                'success' => ($r == true),
                'message' => AttendHandler::getMessage('Successfully updated attending status')
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
     * @return Response
     */
    public function destroy(Request $request, Meeting $meeting)
    {
        $attend = AttendHandler::get($request->user(), $meeting);

        if($attend === null) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Specified user is not attending the meeting'
                ],
                404
            );
        }

        $r = AttendHandler::update($attend, ['attending' => Attend::USER_NOT_ATTENDING]);

        return response()->json(
            [
                'success' => ($r == true),
                'message' => AttendHandler::getMessage('Successfully updated attending status')
            ]
        );
    }
}
