<?php

namespace App\Services;


use App\Enums\Ask;
use App\Enums\Status;
use Exception;
use App\Models\Item;
use App\Models\OrderItem;
use Illuminate\Support\Str;
use App\Models\ItemVariation;
use App\Http\Requests\ItemRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\ChangeImageRequest;

class ItemService
{
    public $item;
    protected $itemFilter = [
        'name',
        'slug',
        'item_category_id',
        'price',
        'is_featured',
        'item_type',
        'tax_id',
        'status',
        'order',
        'description',
        'except'
    ];

    /**
     * Sort items by provided order of IDs
     *
     * @param array $itemIds Array of item IDs in desired order
     * @return bool
     * @throws Exception
     */
    public function sort(array $itemIds): bool
    {
        try {
            DB::beginTransaction();
            
            // Create a case statement for each item to ensure atomic update
            // This prevents race conditions that could lead to duplicate sort values
            $cases = [];
            $ids = [];
            
            foreach ($itemIds as $index => $id) {
                $cases[] = "WHEN {$id} THEN {$index}";
                $ids[] = $id;
            }
            
            if (!empty($cases) && !empty($ids)) {
                $caseString = implode(' ', $cases);
                $idsString = implode(',', $ids);
                
                // Update all items in a single query
                DB::statement("UPDATE items SET sort = CASE id {$caseString} ELSE sort END WHERE id IN ({$idsString})");
            }
            
            DB::commit();
            return true;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function list(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'sort';
            $orderType   = $request->get('order_type') ?? 'asc';

            return Item::with('media', 'category', 'tax')->where(function ($query) use ($requests) {
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->itemFilter)) {
                        if ($key == "except") {
                            $explodes = explode('|', $request);
                            if (count($explodes)) {
                                foreach ($explodes as $explode) {
                                    $query->where('id', '!=', $explode);
                                }
                            }
                        } else {
                            if ($key == "item_category_id" || $key == "status") {
                                $query->where($key, $request);
                            } else {
                                $query->where($key, 'like', '%' . $request . '%');
                            }
                        }
                    }
                }
            })->orderBy($orderColumn, $orderType)->$method(
                $methodValue
            );
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function simpleList(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'sort';
            $orderType   = $request->get('order_type') ?? 'asc';

            return Item::with('media', 'category', 'offer')->where(function ($query) use ($requests) {
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->itemFilter)) {
                        if ($key == "except") {
                            $explodes = explode('|', $request);
                            if (count($explodes)) {
                                foreach ($explodes as $explode) {
                                    $query->where('id', '!=', $explode);
                                }
                            }
                        } else {
                            if ($key == "item_category_id" || $key == "status") {
                                $query->where($key, $request);
                            } else {
                                $query->where($key, 'like', '%' . $request . '%');
                            }
                        }
                    }
                }
            })->orderBy($orderColumn, $orderType)->$method(
                $methodValue
            );
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function store(ItemRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $this->item = Item::create($request->validated() + ['slug' => Str::slug($request->name)]);
                if ($request->image) {
                    $this->item->addMedia($request->image)->toMediaCollection('item');
                }
                if ($request->variations) {
                    $this->item->variations()->createMany(json_decode($request->variations, true));
                }
            });
            return $this->item;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(ItemRequest $request, Item $item): Item
    {
        try {
            DB::transaction(function () use ($request, $item) {
                $item->update($request->validated() + ['slug' => Str::slug($request->name)]);
                if ($request->image) {
                    $item->addMedia($request->image)->toMediaCollection('item');
                }
                if ($request->variations) {
                    $variationIdsArray    = [];
                    $variationDeleteArray = [];
                    $oldVariations        = $item->variations->pluck('id')->toArray();
                    foreach (json_decode($request->variations, true) as $variation) {
                        if (isset($variation['id'])) {
                            $variationIdsArray[] = $variation['id'];
                            ItemVariation::where('id', $variation['id'])->update([
                                'name'             => $variation['name'],
                                'price' => $variation['price'],
                            ]);
                        } else {
                            $item->variations()->create($variation);
                        }
                    }

                    if ($variationIdsArray) {
                        foreach ($oldVariations as $oldVariation) {
                            if (!in_array($oldVariation, $variationIdsArray)) {
                                $variationDeleteArray[] = $oldVariation;
                            }
                        }
                    }

                    if ($variationDeleteArray) {
                        ItemVariation::whereIn('id', $variationDeleteArray)->delete();
                    }
                }
            });
            return Item::find($item->id);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy(Item $item)
    {
        try {
            DB::transaction(function () use ($item) {
                $item->variations()->delete();
                $item->extras()->delete();
                $item->addons()->delete();
                $item->delete();
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function show(Item $item): Item
    {
        try {
            return $item->load('media', 'category', 'tax', 'offer', 'addons', 'variations', 'extras');
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function changeImage(ChangeImageRequest $request, Item $item): Item
    {
        try {
            if ($request->image) {
                $item->clearMediaCollection('item');
                $item->addMedia($request->image)->toMediaCollection('item');
            }
            return $item;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function featuredItems()
    {
        try {
            return Item::with('media','category','offer')->where(['is_featured' => Ask::YES, 'status' => Status::ACTIVE])->inRandomOrder()->limit(8)->get();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function mostPopularItems()
    {
        try {
            return Item::with('media', 'category','offer')->withCount('orders')->where(['status' => Status::ACTIVE])->orderBy('orders_count', 'desc')->limit(6)->get();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    // public function itemReport(PaginateRequest $request)
    // {
    //     try {
    //         $requests    = $request->all();
    //         $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
    //         $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';

    //         return Item::with('category')
    //             ->withCount('orders') // Count total orders
    //             ->withSum('orders', 'quantity') // Sum of all ordered quantities
    //             ->where(function ($query) use ($requests) {
    //                 if (isset($requests['from_date']) && isset($requests['to_date'])) {
    //                     $first_date = date('Y-m-d', strtotime($requests['from_date']));
    //                     $last_date  = date('Y-m-d', strtotime($requests['to_date']));
    //                     $query->whereDate('created_at', '>=', $first_date)
    //                           ->whereDate('created_at', '<=', $last_date);
    //                 }

    //                 foreach ($requests as $key => $request) {
    //                     if (in_array($key, $this->itemFilter)) {
    //                         if ($key == "except") {
    //                             $explodes = explode('|', $request);
    //                             foreach ($explodes as $explode) {
    //                                 $query->where('id', '!=', $explode);
    //                             }
    //                         } else {
    //                             $query->where($key, 'like', '%' . $request . '%');
    //                         }
    //                     }
    //                 }
    //             })
    //             ->orderBy('orders_count', 'desc')
    //             ->$method($methodValue);
    //     } catch (Exception $exception) {
    //         Log::info($exception->getMessage());
    //         throw new Exception($exception->getMessage(), 422);
    //     }
    // }



//     public function itemReport(PaginateRequest $request)
// {
//     try {
//         $requests    = $request->all();
//         $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
//         $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';

//         return Item::with('category')
//             ->whereHas('orders', function ($query) use ($requests) {
//                 if (!empty($requests['from_date']) && !empty($requests['to_date'])) {
//                     $first_date = date('Y-m-d', strtotime($requests['from_date']));
//                     $last_date  = date('Y-m-d', strtotime($requests['to_date']));
//                     $query->whereBetween('created_at', [$first_date, $last_date]);
//                 }
//             })
//             ->withCount(['orders as orders_count' => function ($query) use ($requests) {
//                 if (!empty($requests['from_date']) && !empty($requests['to_date'])) {
//                     $first_date = date('Y-m-d', strtotime($requests['from_date']));
//                     $last_date  = date('Y-m-d', strtotime($requests['to_date']));
//                     $query->whereBetween('created_at', [$first_date, $last_date]);
//                 }
//             }])
//             ->withSum(['orders as orders_sum_quantity' => function ($query) use ($requests) {
//                 if (!empty($requests['from_date']) && !empty($requests['to_date'])) {
//                     $first_date = date('Y-m-d', strtotime($requests['from_date']));
//                     $last_date  = date('Y-m-d', strtotime($requests['to_date']));
//                     $query->whereBetween('created_at', [$first_date, $last_date]);
//                 }
//             }], 'quantity')
//             ->whereNull('items.deleted_at')
//             ->where(function ($query) use ($requests) {
//                 foreach ($requests as $key => $request) {
//                     if (in_array($key, $this->itemFilter)) {
//                         if ($key == "except") {
//                             $explodes = explode('|', $request);
//                             foreach ($explodes as $explode) {
//                                 $query->where('id', '!=', $explode);
//                             }
//                         } else {
//                             $query->where($key, 'like', '%' . $request . '%');
//                         }
//                     }
//                 }
//             })
//             ->orderByDesc('orders_count')
//             ->$method($methodValue);
//     } catch (Exception $exception) {
//         Log::info($exception->getMessage());
//         throw new Exception($exception->getMessage(), 422);
//     }
// }



public function itemReport(PaginateRequest $request)
{
    try {
        $requests    = $request->all();
        $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
        $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';

        return Item::with('category')
            ->withCount(['orders' => function($query) use ($requests) {
                // Apply date filtering to orders relationship
                if (isset($requests['from_date']) && isset($requests['to_date'])) {
                    $first_date = date('Y-m-d', strtotime($requests['from_date']));
                    $last_date  = date('Y-m-d', strtotime($requests['to_date']));
                    $query->whereDate('created_at', '>=', $first_date)
                          ->whereDate('created_at', '<=', $last_date);
                }
            }])
            ->withSum(['orders' => function($query) use ($requests) {
                // Apply the same date filtering to orders for the sum
                if (isset($requests['from_date']) && isset($requests['to_date'])) {
                    $first_date = date('Y-m-d', strtotime($requests['from_date']));
                    $last_date  = date('Y-m-d', strtotime($requests['to_date']));
                    $query->whereDate('created_at', '>=', $first_date)
                          ->whereDate('created_at', '<=', $last_date);
                }
            }], 'quantity')
            ->where(function ($query) use ($requests) {
                // Keeping your existing item filtering unchanged
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->itemFilter)) {
                        if ($key == "except") {
                            $explodes = explode('|', $request);
                            foreach ($explodes as $explode) {
                                $query->where('id', '!=', $explode);
                            }
                        } else {
                            $query->where($key, 'like', '%' . $request . '%');
                        }
                    }
                }
            })
            ->whereHas('orders', function($query) use ($requests) {
                // Ensure only items with orders in the date range are included
                if (isset($requests['from_date']) && isset($requests['to_date'])) {
                    $first_date = date('Y-m-d', strtotime($requests['from_date']));
                    $last_date  = date('Y-m-d', strtotime($requests['to_date']));
                    $query->whereDate('created_at', '>=', $first_date)
                          ->whereDate('created_at', '<=', $last_date);
                }
            })
            ->orderBy('orders_count', 'desc')
            ->$method($methodValue);
    } catch (Exception $exception) {
        Log::info($exception->getMessage());
        throw new Exception($exception->getMessage(), 422);
    }
}

    public function itemDetails(Item $item)
    {
        return $item->load('media', 'category', 'tax', 'offer', 'addons', 'variations', 'extras');
    }
}
