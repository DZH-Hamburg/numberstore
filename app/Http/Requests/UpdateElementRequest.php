<?php

namespace App\Http\Requests;

use App\Enums\ElementType;
use App\Http\Requests\Concerns\NormalizesScreenshotElementInput;
use App\Models\Element;
use App\Models\Group;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateElementRequest extends FormRequest
{
    use NormalizesScreenshotElementInput;

    public function authorize(): bool
    {
        /** @var Element $element */
        $element = $this->route('element');

        return $this->user()?->can('update', $element) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeSecretsInput();

        if (! $this->has('config') && ! $this->has('config_json')) {
            return;
        }

        /** @var Element $element */
        $element = $this->route('element');

        $config = is_array($this->input('config'))
            ? $this->input('config')
            : ($element->config ?? []);

        if ($this->has('config_json')) {
            $jsonRaw = $this->input('config_json');
            if (is_string($jsonRaw) && trim($jsonRaw) !== '') {
                $decoded = json_decode(trim($jsonRaw), true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $config = array_replace_recursive($decoded, $config);
                }
            }
        }

        $type = $this->input('type');
        if (! is_string($type) || $type === '') {
            $type = $element->type->value;
        }
        if ($type !== ElementType::Screenshot->value) {
            foreach (['url', 'selectors', 'wait_for', 'wait_until', 'timeout_ms', 'full_page'] as $k) {
                unset($config[$k]);
            }
        }

        $this->merge(['config' => $this->trimEmptyScreenshotConfigFields($config)]);
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
            'config_json' => ['sometimes', 'nullable', 'string'],
            'config.url' => ['sometimes', 'required_if:type,'.ElementType::Screenshot->value, 'url', 'max:2048'],
            'config.selectors' => ['sometimes', 'nullable', 'array'],
            'config.selectors.username' => ['sometimes', 'nullable', 'string', 'max:255'],
            'config.selectors.password' => ['sometimes', 'nullable', 'string', 'max:255'],
            'config.selectors.totp' => ['sometimes', 'nullable', 'string', 'max:255'],
            'config.selectors.submit' => ['sometimes', 'nullable', 'string', 'max:255'],
            'config.selectors.totp_submit' => ['sometimes', 'nullable', 'string', 'max:255'],
            'config.wait_for' => ['sometimes', 'nullable', 'string', 'max:255'],
            'config.wait_until' => ['sometimes', 'nullable', 'string', Rule::in(['load', 'domcontentloaded', 'networkidle'])],
            'config.timeout_ms' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:300000'],
            'config.full_page' => ['sometimes', 'boolean'],
            'secrets' => ['sometimes', 'array'],
            'secrets.username' => ['sometimes', 'nullable', 'string', 'max:255'],
            'secrets.password' => ['sometimes', 'nullable', 'string', 'max:1024'],
            'secrets.totp_secret' => ['sometimes', 'nullable', 'string', 'min:16', 'max:128', 'regex:/^[A-Z2-7]+=*$/i'],
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
