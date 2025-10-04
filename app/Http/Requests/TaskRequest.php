<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Please enter a title for the task.',
            'title.max' => 'Title is too long (max 255 chars).',
            'description.max' => 'Description is too long (max 2000 chars).',
        ];
    }
}
