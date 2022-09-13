<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetImageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'image' => 'required|image|mimes:jpg,png,jpeg,|max:2048|',
        ];
    }

    public function messages()
    {
        return [
            'image.required' => 'image is required!',
            'image.mimes' => 'please upload only jpg,png,jpeg!',
            'image.max' => 'image max size must by 2mb!',

        ];
    }
}
