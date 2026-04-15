<?php

namespace App\Http\Controllers;

use App\Models\PricingItem;
use App\Models\Quotation;
use App\Models\User;
use App\Services\QuotationLineItemSyncService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class QuotationLineItemController extends Controller
{
    public function __construct(
        private QuotationLineItemSyncService $quotationLineItemSyncService
    ) {
    }

    public function replace(Request $request, Quotation $quotation)
    {
        $user = $request->user();

        if (!in_array($user->role, [User::ROLE_ADMIN, User::ROLE_TECHNICIAN], true)) {
            return response()->json([
                'message' => 'Forbidden',
            ], 403);
        }

        if ($quotation->quotation_type !== 'final') {
            return response()->json([
                'message' => 'Line items can only be attached to final quotations.',
            ], 422);
        }

        $validated = $request->validate(
            $this->rules(),
            $this->messages()
        );

        $quotation = $this->quotationLineItemSyncService->replaceForQuotation(
            $quotation,
            $validated['line_items']
        );

        return response()->json([
            'message' => 'Quotation line items updated successfully.',
            'data' => $quotation,
        ]);
    }

    private function rules(): array
    {
        return [
            'line_items' => 'required|array',
            'line_items.*.pricing_item_id' => 'nullable|exists:pricing_items,id',
            'line_items.*.description' => 'required|string',
            'line_items.*.category' => ['required', 'string', Rule::in(PricingItem::CATEGORIES)],
            'line_items.*.qty' => 'required|numeric|gt:0',
            'line_items.*.unit' => 'required|string|max:50',
            'line_items.*.unit_amount' => 'required|numeric|min:0',
            'line_items.*.total_amount' => 'sometimes|numeric|min:0',
        ];
    }

    private function messages(): array
    {
        return [
            'line_items.required' => 'Line items are required.',
            'line_items.array' => 'Line items must be a valid array.',
            'line_items.*.pricing_item_id.exists' => 'Selected pricing item does not exist.',
            'line_items.*.description.required' => 'Description is required for each line item.',
            'line_items.*.description.string' => 'Description must be a valid string.',
            'line_items.*.category.required' => 'Category is required for each line item.',
            'line_items.*.category.string' => 'Category must be a valid string.',
            'line_items.*.category.in' => 'Category must be one of the supported pricing item categories.',
            'line_items.*.qty.required' => 'Quantity is required for each line item.',
            'line_items.*.qty.numeric' => 'Quantity must be a valid number.',
            'line_items.*.qty.gt' => 'Quantity must be greater than 0.',
            'line_items.*.unit.required' => 'Unit is required for each line item.',
            'line_items.*.unit.string' => 'Unit must be a valid string.',
            'line_items.*.unit.max' => 'Unit must not be greater than 50 characters.',
            'line_items.*.unit_amount.required' => 'Unit amount is required for each line item.',
            'line_items.*.unit_amount.numeric' => 'Unit amount must be a valid number.',
            'line_items.*.unit_amount.min' => 'Unit amount must be at least 0.',
            'line_items.*.total_amount.numeric' => 'Total amount must be a valid number.',
            'line_items.*.total_amount.min' => 'Total amount must be at least 0.',
        ];
    }
}
