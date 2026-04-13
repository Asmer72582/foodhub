<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\SimpleItemResource;
use App\Http\Resources\TopCustomerResource;
use Exception;
use App\Libraries\AppLibrary;
use App\Services\ItemService;
use App\Services\DashboardService;
use App\Http\Resources\ItemResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Http\Resources\OrderSummaryResource;
use App\Http\Resources\SalesSummaryResource;
use App\Http\Resources\CustomerStatesResource;
use App\Http\Resources\OrderStatisticsResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class DashboardController extends AdminController
{
    private DashboardService $dashboardService;
    private ItemService $itemService;

    public function __construct(DashboardService $dashboardService, ItemService $itemService)
    {
        parent::__construct();
        $this->dashboardService = $dashboardService;
        $this->itemService      = $itemService;
        $this->middleware(['permission:dashboard'])->only(
            'orderStatistics',
            'orderSummary',
            'featuredItems',
            'mostPopularItems',
            'topCustomers',
            'totalSales',
            'salesSummary',
            'customerStates',
            'totalOrders',
            'totalCustomers',
            'totalMenuItems'
        );
    }

    public function totalSales(): \Illuminate\Http\Response | array | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return ['data' => ['total_sales' => AppLibrary::currencyAmountFormat($this->dashboardService->totalSales())]];
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function totalTips(): \Illuminate\Http\Response | array | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return ['data' => ['total_tips' => AppLibrary::currencyAmountFormat($this->dashboardService->totalTips())]];
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function totalOrders(): \Illuminate\Http\Response | array | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return ['data' => ['total_orders' => $this->dashboardService->totalOrders()]];
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function totalCustomers(): \Illuminate\Http\Response | array | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return ['data' => ['total_customers' => $this->dashboardService->totalCustomers()]];
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function totalMenuItems(): \Illuminate\Http\Response | array | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return ['data' => ['total_menu_items' => $this->dashboardService->totalMenuItems()]];
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function orderStatistics(
        Request $request
    ): \Illuminate\Http\Response | OrderStatisticsResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new OrderStatisticsResource($this->dashboardService->orderStatistics($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function salesSummary(
        Request $request
    ): \Illuminate\Http\Response | SalesSummaryResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new SalesSummaryResource($this->dashboardService->salesSummary($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function orderSummary(
        Request $request
    ): \Illuminate\Http\Response | OrderSummaryResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new OrderSummaryResource($this->dashboardService->orderSummary($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function customerStates(
        Request $request
    ): \Illuminate\Http\Response | CustomerStatesResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new CustomerStatesResource($this->dashboardService->customerStates($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function topCustomers(): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return  TopCustomerResource::collection($this->dashboardService->topCustomers());
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function employeeOrders(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            // We can reuse TopCustomerResource or UserResource, or simply return JSON response.
            $employees = $this->dashboardService->employeeOrders($request);
            return response()->json(['data' => $employees]);
        } catch (Exception $exception) {
            return response()->json(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function featuredItems(): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return SimpleItemResource::collection($this->itemService->featuredItems());
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function mostPopularItems(): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return SimpleItemResource::collection($this->itemService->mostPopularItems());
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function paymentBreakdown(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'date_range' => 'required|string|in:today,this_week,this_month,this_year,custom',
                'start_date' => 'required_if:date_range,custom|date',
                'end_date' => 'required_if:date_range,custom|date|after_or_equal:start_date'
            ]);

            $data = $this->dashboardService->paymentBreakdown($request);
            
            if (!is_array($data)) {
                throw new Exception('Invalid data format received from service');
            }

            return response()->json([
                'status' => true,
                'message' => 'Payment breakdown retrieved successfully',
                'data' => [
                    'total_sales' => AppLibrary::currencyAmountFormat($data['total_sales'] ?? 0),
                    'total_tips' => AppLibrary::currencyAmountFormat($data['total_tips'] ?? 0),
                    'online_sales' => AppLibrary::currencyAmountFormat($data['online_sales'] ?? 0),
                    'cash_sales' => AppLibrary::currencyAmountFormat($data['cash_sales'] ?? 0),
                    'dates' => $data['dates'] ?? [],
                    'online_amounts' => array_map(function($amount) {
                        return AppLibrary::flatAmountFormat($amount);
                    }, $data['online_amounts'] ?? []),
                    'cash_amounts' => array_map(function($amount) {
                        return AppLibrary::flatAmountFormat($amount);
                    }, $data['cash_amounts'] ?? []),
                    'tip_amounts' => array_map(function($amount) {
                        return AppLibrary::flatAmountFormat($amount);
                    }, $data['tip_amounts'] ?? [])
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            return response()->json([
                'status' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }

    public function orderBreakdown(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'date_range' => 'required|string|in:today,this_week,this_month,this_year,custom',
                'start_date' => 'required_if:date_range,custom|date',
                'end_date' => 'required_if:date_range,custom|date|after_or_equal:start_date'
            ]);

            $data = $this->dashboardService->orderBreakdown($request);
            
            if (!is_array($data)) {
                throw new Exception('Invalid data format received from service');
            }

            return response()->json([
                'status' => true,
                'message' => 'Order breakdown retrieved successfully',
                'data' => [
                    'total_orders' => $data['total_orders'] ?? 0,
                    'dine_in_orders' => $data['dine_in_orders'] ?? 0,
                    'delivery_orders' => $data['delivery_orders'] ?? 0,
                    'take_away_orders' => $data['take_away_orders'] ?? 0,
                    'dates' => $data['dates'] ?? [],
                    'dine_in_counts' => $data['dine_in_counts'] ?? [],
                    'delivery_counts' => $data['delivery_counts'] ?? [],
                    'take_away_counts' => $data['take_away_counts'] ?? []
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            return response()->json([
                'status' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }

    public function paymentMethodBreakdown(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'date_range' => 'required|string|in:today,this_week,this_month,this_year,custom',
                'start_date' => 'required_if:date_range,custom|date',
                'end_date' => 'required_if:date_range,custom|date|after_or_equal:start_date'
            ]);

            $data = $this->dashboardService->paymentMethodBreakdown($request->date_range, $request->start_date, $request->end_date);
            
            return response()->json([
                'status' => true,
                'message' => 'Payment method breakdown retrieved successfully',
                'data' => [
                    'dine_in' => AppLibrary::flatAmountFormat($data['dine_in'] ?? 0),
                    'takeaway' => AppLibrary::flatAmountFormat($data['takeaway'] ?? 0),
                    'delivery' => AppLibrary::flatAmountFormat($data['delivery'] ?? 0)
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $exception) {
            Log::error('Error in paymentMethodBreakdown: ' . $exception->getMessage());
            return response()->json([
                'status' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }
}