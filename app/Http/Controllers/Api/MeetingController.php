<?php

namespace App\Http\Controllers\Api;

use App\Handlers\MeetingHandler;
use App\Http\Controllers\Controller;
use App\Models\Meeting;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MeetingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $meetings = MeetingHandler::search($request->input());

        return response()->json(
            [
                'success'  => true,
                'meetings' => $meetings,
                'rowCount' => $meetings->count()
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $validator = MeetingHandler::validatorForCreate($request->input());

        if($validator->invalid()) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Invalid meeting parameters',
                    'errors'  => $validator->messages()
                ],
                422
            );
        }

        //$meeting = $request->user()->meetings()->create($request->input());
        $meeting = MeetingHandler::create($request->user(), $request->input());

        return response()->json(
            [
                'success' => ($meeting !== null),
                'message' => MeetingHandler::getMessage('Successfully created the meeting'),
                'meeting' => $meeting
            ],
            201
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  Meeting $meeting
     *
     * @return Response
     */
    public function show(Meeting $meeting)
    {
        return response()->json(
            [
                'success' => ($meeting !== null),
                'meeting' => $meeting
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  Meeting $meeting
     *
     * @return Response
     */
    public function update(Request $request, Meeting $meeting)
    {
        if($request->user()->isNot($meeting->user)) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Only the meeting organizer is allowed to edit a meeting'
                ],
                401
            );
        }

        $validator = MeetingHandler::validatorForUpdate($request->input());

        if($validator->invalid()) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Invalid meeting parameters',
                    'errors'  => $validator->messages()
                ],
                422
            );
        }

        $r = MeetingHandler::update($meeting, $request->input());

        return response()->json(
            [
                'success' => ($r == true),
                'message' => MeetingHandler::getMessage('Successfully updated meeting')
            ]
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Request  $request
     * @param  Meeting $meeting
     *
     * @return Response
     */
    public function destroy(Request $request, Meeting $meeting)
    {
        if($request->user()->isNot($meeting->user)) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Only the meeting organizer is allowed to delete a meeting'
                ],
                401
            );
        }

        $r = MeetingHandler::delete($meeting);

        return response()->json(
            [
                'success' => ($r == true),
                'message' => MeetingHandler::getMessage('Successfully deleted meeting')
            ]
        );
    }
}
