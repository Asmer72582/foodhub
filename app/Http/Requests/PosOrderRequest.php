<?php

namespace App\Http\Requests;

use App\Enums\Activity;
use App\Enums\OrderType;
use App\Enums\PosPaymentMethod;
use App\Rules\ValidJsonOrder;
use Illuminate\Validation\Rule;
use Smartisan\Settings\Facades\Settings;
use Illuminate\Foundation\Http\FormRequest;

class PosOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'customer_id'     => ['required', 'numeric'],
            'branch_id'       => ['required', 'numeric'],
            'subtotal'        => ['required', 'numeric'],
            'discount'        => ['nullable', 'numeric'],
            'delivery_charge' => request('order_method') === 3 ? [ // 3 is DELIVERY
                'required',
                'numeric'
            ] : ['nullable'],
            'total'            => ['required', 'numeric'],
            'order_type'       => ['required', 'numeric'],
            'order_method'     => ['required', 'numeric', 'in:1,2,3'], // 1=DINE_IN, 2=TAKEAWAY, 3=DELIVERY
            'is_advance_order' => ['required', 'numeric'],
            'address_id'       => ['nullable', 'numeric'],
            'delivery_time'       => ['nullable', 'string'],
            // 'delivery_time'    => request('order_method') === 3 ? [
            //     'required',
            //     'string'
            // ] : ['nullable'],
            'coupon_id'        => ['nullable', 'numeric'],
            'source'           => ['required', 'numeric'],
            'items'            => ['required', 'json', new ValidJsonOrder],
            'pos_payment_method' => ['required', 'numeric', 'in:1,2,3,4,5,6'],
            'pos_payment_note'  => ['nullable', 'string'],
            'pos_split_cash'   => ['nullable', 'numeric'],
            'pos_split_online' => ['nullable', 'numeric'],
            'tip_amount'       => ['nullable', 'numeric'],
            'tip_employee_id'  => ['nullable', 'numeric'],
            'status'           => ['required', 'numeric']
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (request('order_method') == 3 && Settings::group('order_setup')->get("order_setup_delivery") == Activity::DISABLE) {
                $validator->errors()->add('order_method', 'Delivery orders are disabled at the moment. Please try another order method or contact management.');
            } else if (request('order_method') == 2 && Settings::group('order_setup')->get("order_setup_takeaway") == Activity::DISABLE) {
                $validator->errors()->add('order_method', 'Takeaway orders are disabled at the moment. Please try another order method or contact management.');
            } else if (blank(request('order_method'))) {
                $validator->errors()->add('order_method', 'Please select an order method.');
            }
        });
    }

    public function messages(){
        return [
            'pos_payment_note.required' => 'Payment note field is required '
        ];
    }
}