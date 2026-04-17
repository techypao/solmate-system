<?php

namespace App\Http\Requests;

use App\Models\Testimony;
use App\Models\User;
use App\Services\TestimonyImageService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateTestimonyRequest extends FormRequest
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
            'remove_image_ids' => ['sometimes', 'array'],
            'remove_image_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('testimony_images', 'id')->where(fn ($query) => $query->where('testimony_id', $this->route('id'))),
            ],
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

            $existingImageCount = $testimony?->images()->count() ?? 0;
            $removeImageCount = count($this->input('remove_image_ids', []));
            $newImageCount = count($this->file('images', []));

            if (($existingImageCount - $removeImageCount + $newImageCount) > TestimonyImageService::MAX_IMAGES) {
                $validator->errors()->add(
                    'images',
                    'A testimony may only have up to '.TestimonyImageService::MAX_IMAGES.' images.'
                );
            }
        });
    }
}
