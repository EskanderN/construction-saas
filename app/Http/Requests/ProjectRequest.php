<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'participants' => 'nullable|array',
            'participants.*.user_id' => 'required|exists:users,id',
            'participants.*.role' => 'required|string',
            'files.*' => 'nullable|file|max:10240',
        ];
    }
}