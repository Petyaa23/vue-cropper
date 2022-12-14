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
            'image' => 'required|image|mimes:jpg,png,jpeg|max:3000',
        ];
    }

    public function messages()
    {
        return [
            'image.required' => 'Image is required!',
            'image.mimes' => 'Please upload only jpg,png,jpeg!',
            'image.max' => 'Image max size must by 3 MB!',
        ];
    }
}
