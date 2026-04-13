<?php

namespace App\Exports;

use App\Services\ItemService;
use App\Http\Requests\PaginateRequest;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ItemsReportExport implements FromCollection, WithHeadings
{
    public ItemService $itemService;
    public PaginateRequest $request;

    public function __construct(ItemService $itemService, $request)
    {
        $this->itemService = $itemService;
        $this->request     = $request;
    }

    public function collection(): \Illuminate\Support\Collection
    {
        $itemsReportArray  = [];
        $itemsReportsArray = $this->itemService->list($this->request);

        $totalOrders = 0;
        $totalQuantity = 0;

        foreach ($itemsReportsArray as $item) {
            $totalOrders   += $item->orders_count;
            $totalQuantity += $item->orders_sum_quantity ?? 0;

            $itemsReportArray[] = [
                $item->name,
                optional($item->category)->name,
                trans('itemType.' . $item->item_type),
                $item->orders_count,         // Number of orders
                $item->orders_sum_quantity ?? 0, // Total quantity ordered
            ];
        }

        // Add total row
        $itemsReportArray[] = [
            trans('all.label.total'),
            '',
            '',
            $totalOrders,
            $totalQuantity,
        ];

        return collect($itemsReportArray);
    }

    public function headings(): array
    {
        return [
            trans('all.label.name'),
            trans('all.label.item_category_id'),
            trans('all.label.item_type'),
            trans('all.label.quantity_orders'), // Count of orders
            trans('all.label.total_quantity'), // Sum of item quantities
        ];
    }
}
