<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UserUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return backpack_auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $id = $this->get('id') ?? request()->route('id');

        return [
            'email' => 'nullable|email:rfc,dns|unique:'.config('permission.table_names.users', 'users').',email,'.$id,
            'name' => 'required',
            'password' => ['nullable', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()],
        ];
    }
}
