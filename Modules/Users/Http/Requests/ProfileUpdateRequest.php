<?php

namespace Modules\Users\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Users\Models\User;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'name' => 'required|string|min:3|max:80',
            'last_name' => 'nullable|min:3|max:80',
            'email' => ['required', 'string', 'lowercase', 'email', 'max:200', Rule::unique(User::class)->ignore($userId)],
            // dni / phone are nullable: collected later via the dedicated
            // profile fields, not at basic profile-update time. The
            // regex is enforced when present.
            'dni' => ['nullable', 'regex:/^\d?\d\.\d{3}\.\d{3}\-[\d|k|K]$/', Rule::unique(User::class)->ignore($userId)],
            'phone' => 'nullable|min:11|max:20',
            'password' => [],
            'avatar' => '',
            'avatarRemove' => 'boolean',
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'nombre',
            'last_name' => 'apellidos',
            'email' => 'correo electrónico',
            'dni' => 'rut',
            'phone' => 'teléfono',
            'password' => 'contraseña',
            'avatar' => 'foto',
            'avatarremove' => 'avatarremove',
        ];
    }
}
