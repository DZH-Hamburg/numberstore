<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User $user */
        $user = $this->route('user');

        return $this->user()?->can('update', $user) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_platform_admin' => $this->boolean('is_platform_admin'),
            'can_create_groups' => $this->boolean('can_create_groups'),
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var User $user */
            $user = $this->route('user');
            if ($user->is_platform_admin && ! $this->boolean('is_platform_admin')) {
                $hasOtherAdmin = User::query()
                    ->where('is_platform_admin', true)
                    ->whereKeyNot($user->getKey())
                    ->exists();
                if (! $hasOtherAdmin) {
                    $validator->errors()->add(
                        'is_platform_admin',
                        __('Mindestens ein Plattform-Admin muss aktiv bleiben.')
                    );
                }
            }
        });
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var User $user */
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_platform_admin' => ['boolean'],
            'can_create_groups' => ['boolean'],
        ];
    }
}
