<?php

namespace App\Http\Requests;

use App\Models\Testimony;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AdminUpdateTestimonyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === User::ROLE_ADMIN;
    }

    public function rules(): array
    {
        return [
            'service_request_id' => ['nullable', 'integer', 'exists:service_requests,id'],
            'inspection_request_id' => ['nullable', 'integer', 'exists:inspection_requests,id'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'status' => ['nullable', 'in:pending,approved,rejected'],
            'admin_note' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $testimony = Testimony::query()->find($this->route('id'));
            $serviceRequestId = $this->input('service_request_id', $testimony?->service_request_id);
            $inspectionRequestId = $this->input('inspection_request_id', $testimony?->inspection_request_id);

            if (! $serviceRequestId && ! $inspectionRequestId) {
                $validator->errors()->add(
                    'service_request_id',
                    'Either service_request_id or inspection_request_id is required.'
                );
            }
        });
    }
}
