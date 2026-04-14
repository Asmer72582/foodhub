<?php

namespace App\Http\Resources;


use App\Libraries\AppLibrary;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request): array
    {
        if (!$this->resource) {
            return [];
        }

        return [
            'id'                             => $this->id,
            'order_serial_no'                => $this->order_serial_no,
            'user_id'                        => $this->user_id,
            'branch_id'                      => $this->branch_id,
            'branch_name'                    => optional($this->branch)->name,
            'order_items'                    => optional($this->orderItems)->count(),
            "total_currency_price"           => AppLibrary::currencyAmountFormat($this->total),
            "total_tax_currency_price"       => AppLibrary::currencyAmountFormat($this->total_tax),
            "total_amount_price"             => AppLibrary::flatAmountFormat($this->total),
            "discount_currency_price"        => AppLibrary::currencyAmountFormat($this->discount),
            "delivery_charge_currency_price" => AppLibrary::currencyAmountFormat($this->delivery_charge),
            'payment_method'                 => $this->payment_method,
            'payment_status'                 => $this->payment_status,
            'pos_payment_method'             => $this->pos_payment_method,
            'pos_split_cash'                 => $this->pos_split_cash > 0 ? AppLibrary::currencyAmountFormat($this->pos_split_cash) : "",
            'pos_split_online'               => $this->pos_split_online > 0 ? AppLibrary::currencyAmountFormat($this->pos_split_online) : "",
            'preparation_time'               => $this->preparation_time,
            'order_type'                     => $this->order_type,
            'order_method'                   => $this->order_method,
            'order_datetime'                 => AppLibrary::datetime($this->order_datetime),
            'status'                         => $this->status,
            'is_advance_order'               => $this->is_advance_order,
            'status_name'                    => trans('orderStatus.' . $this->status),
            'tip_amount'                     => $this->tip_amount,
            'tip_currency_amount'            => AppLibrary::currencyAmountFormat($this->tip_amount),
            'tip_employee_id'                => $this->tip_employee_id,
            'tip_employee_name'              => optional($this->tipEmployee)->name,
            'customer'                       => $this->user ? new OrderUserResource($this->user->load('roles', 'media')) : null,
            'transaction'                    => $this->transaction ? new TransactionResource($this->transaction->load('order')) : null,
        ];
    }
}
