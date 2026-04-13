<?php
namespace App\Http\Resources;
use App\Libraries\AppLibrary;
use Illuminate\Http\Resources\Json\JsonResource;
class ItemReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            "id"               => $this->id,
            "name"             => $this->name,
            "slug"             => $this->slug,
            "item_category_id" => $this->item_category_id,
            "tax_id"           => $this->tax_id,
            "flat_price"       => AppLibrary::flatAmountFormat($this->price),
            "convert_price"    => AppLibrary::convertAmountFormat($this->price),
            "currency_price"   => AppLibrary::currencyAmountFormat($this->price),
            "price"            => $this->price,
            "item_type"        => $this->item_type,
            "is_featured"      => $this->is_featured,
            "status"           => $this->status,
            "description"      => $this->description ?? '',
            "caution"          => $this->caution ?? '',
            "order"            => $this->orders_count,  // Total number of orders for this item
            "total_quantity"   => $this->orders_sum_quantity ?? 0, // Use the sum from the filtered query
            "thumb"            => $this->thumb,
            "cover"            => $this->cover,
            "preview"          => $this->preview,
            "category_name"    => optional($this->category)->name,
            "category"         => new ItemCategoryResource($this->category)
        ];
    }
}