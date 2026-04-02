<?php

namespace App\Http\Requests;

use App\Enums\ElementType;
use App\Models\Element;
use App\Models\Group;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateElementRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Element $element */
        $element = $this->route('element');

        return $this->user()?->can('update', $element) ?? false;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('config')) {
            return;
        }
        $raw = $this->input('config');
        if ($raw === null || $raw === '') {
            $this->merge(['config' => []]);

            return;
        }
        if (is_array($raw)) {
            return;
        }
        if (! is_string($raw)) {
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
            'name' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', 'string', Rule::enum(ElementType::class)],
            'config' => ['sometimes', 'array'],
            'consumer_can_read_via_api' => ['sometimes', 'boolean'],
        ];
    }

    public function canSetConsumerFlag(): bool
    {
        /** @var Group $group */
        $group = $this->route('group');
        /** @var Element $element */
        $element = $this->route('element');

        return $this->user()?->can('setConsumerApiFlag', [$element, $group]) ?? false;
    }
}
