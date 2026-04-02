<?php

namespace App\Http\Requests;

use App\Enums\ElementType;
use App\Models\Element;
use App\Models\Group;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreElementRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Group $group */
        $group = $this->route('group');

        return $this->user()?->can('create', [Element::class, $group]) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $raw = $this->input('config');
        if ($raw === null || $raw === '') {
            $this->merge(['config' => []]);

            return;
        }
        if (is_array($raw)) {
            return;
        }
        if (! is_string($raw)) {
            $this->merge(['config' => []]);

            return;
        }
        $trim = trim($raw);
        if ($trim === '') {
            $this->merge(['config' => []]);

            return;
        }
        $decoded = json_decode($trim, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return;
        }
        $this->merge(['config' => $decoded]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::enum(ElementType::class)],
            'name' => ['required', 'string', 'max:255'],
            'config' => ['present', 'array'],
            'consumer_can_read_via_api' => ['sometimes', 'boolean'],
        ];
    }
}
