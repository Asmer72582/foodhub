<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->id,
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:500',
            'country_code' => 'nullable|string|max:10',
            'status' => 'required|integer',
            'branch_id' => 'required|integer|exists:branches,id',
            'role_id' => 'required|integer|exists:roles,id',
        ];
    }
}
