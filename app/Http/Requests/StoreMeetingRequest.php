<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMeetingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // @todo When user roles are implemented, only allow organizers to create meetings
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'        => 'required|string|min:5|max:64',
            'description' => 'string',
            'location'    => 'string|max:128',
            'start_time'  => 'date|after:yesterday',
            'end_time'    => 'date|after:start_time'
        ];
    }
}
