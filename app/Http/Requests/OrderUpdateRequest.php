<?php 

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // public function rules(): array
    // {
    //     return [
    //         'status'             => 'required|integer',
    //         'user_id'            => 'required|exists:users,id',
    //         'items'              => 'required|array',
    //         'items.*.id'         => 'required|exists:order_items,id',
    //         'items.*.quantity'   => 'required|integer|min:1',
    //         'total_amount_price' => 'required|numeric|min:0'
    //     ];
    // }

    // In your OrderUpdateRequest file
    public function rules(): array
    {
        return [
            'customer_id' => 'required|exists:users,id',
            'branch_id' => 'required|exists:branches,id',
            'total_amount_price' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'items' => ['required', 'json'],
            'pos_payment_method' => 'required|numeric',
            'pos_payment_note' => 'nullable|string',
            'pos_split_cash' => 'nullable|numeric',
            'pos_split_online' => 'nullable|numeric',
            'token' => 'nullable|string',
            'tip_amount' => 'nullable|numeric|min:0',
            'tip_employee_id' => 'nullable|exists:users,id'
        ];
    }
}