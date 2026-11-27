<?php

namespace App\Http\Requests;

use App\Enums\EnquiryType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactMessageRequest extends FormRequest
{
    /** Budget ranges are stored as keys. The website chooses the labels. */
    public const BUDGET_RANGES = ['not_sure', 'small', 'medium', 'large'];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['email' => strtolower(trim((string) $this->input('email')))]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'organisation' => ['nullable', 'string', 'max:160'],
            'enquiry_type' => ['required', Rule::enum(EnquiryType::class)],
            'subject' => ['required', 'string', 'min:3', 'max:190', 'regex:/^[^\r\n]+$/'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'budget_range' => ['nullable', Rule::in(self::BUDGET_RANGES)],
            'privacy_accepted' => ['accepted'],
            // Honeypot: real visitors never see or fill this field.
            'website' => ['nullable', 'string', 'max:255'],
            'cf-turnstile-response' => ['nullable', 'string', 'max:2048'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'privacy_accepted.accepted' => 'Please confirm that you have read the privacy notice.',
            'subject.regex' => 'The subject must be a single line.',
        ];
    }
}
