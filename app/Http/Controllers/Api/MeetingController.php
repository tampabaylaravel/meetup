<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Meeting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMeetingRequest;
use App\Http\Requests\UpdateMeetingRequest;

class MeetingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $meetings = Meeting::search($request->input())->get();

        return response()->json(
            [
                'success' => true,
                'meetings' => $meetings,
                'rowCount' => $meetings->count(),
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreMeetingRequest $request
     *
     * @return JsonResponse
     */
    public function store(StoreMeetingRequest $request)
    {
        $meeting = $request->user()->meetings()->create($request->validated());

        return response()->json(
            [
                'success' => ($meeting !== null),
                'message' => 'Successfully created the meeting',
                'meeting' => $meeting,
            ],
            201
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  Meeting $meeting
     *
     * @return JsonResponse
     */
    public function show(Meeting $meeting)
    {
        return response()->json(
            [
                'success' => ($meeting !== null),
                'meeting' => $meeting,
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateMeetingRequest  $request
     * @param  Meeting $meeting
     *
     * @return JsonResponse
     */
    public function update(UpdateMeetingRequest $request, Meeting $meeting)
    {
        $meeting->update($request->validated());

        return response()->json(
            [
                'success' => true,
                'message' => 'Successfully updated meeting',
            ]
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param UpdateMeetingRequest $request
     * @param Meeting $meeting
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(UpdateMeetingRequest $request, Meeting $meeting)
    {
        $meeting->delete();

        return response()->json(
            [
                'success' => true,
                'message' => 'Successfully deleted meeting',
            ]
        );
    }
}
