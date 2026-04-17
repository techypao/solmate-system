<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Services\TestimonyImageService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreTestimonyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === User::ROLE_CUSTOMER;
    }

    public function rules(): array
    {
        return [
            'service_request_id' => ['nullable', 'integer', 'exists:service_requests,id'],
            'inspection_request_id' => ['nullable', 'integer', 'exists:inspection_requests,id'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'images' => ['sometimes', 'array', 'max:'.TestimonyImageService::MAX_IMAGES],
            'images.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->filled('service_request_id') && ! $this->filled('inspection_request_id')) {
                $validator->errors()->add(
                    'service_request_id',
                    'Either service_request_id or inspection_request_id is required.'
                );
            }
        });
    }
}
