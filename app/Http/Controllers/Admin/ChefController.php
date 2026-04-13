<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\User;
use App\Models\Order;
use App\Enums\Role;
use App\Enums\OrderStatus;
use App\Services\EmployeeService;
use App\Services\OrderService;
use App\Http\Resources\EmployeeResource;
use App\Http\Resources\OrderResource;
use App\Http\Requests\EmployeeRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\ChangeImageRequest;
use App\Http\Requests\UserChangePasswordRequest;
use App\Http\Requests\OrderStatusRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class ChefController extends AdminController
{
    private EmployeeService $employeeService;
    private OrderService $orderService;

    public function __construct(EmployeeService $employeeService, OrderService $orderService)
    {
        parent::__construct();
        $this->employeeService = $employeeService;
        $this->orderService    = $orderService;
        $this->middleware(['permission:chefs'])->only('index', 'export', 'changePassword', 'changeImage', 'orders');
        $this->middleware(['permission:chefs_create'])->only('store');
        $this->middleware(['permission:chefs_edit'])->only('update');
        $this->middleware(['permission:chefs_delete'])->only('destroy');
        $this->middleware(['permission:chefs_show'])->only('show');
        $this->middleware(['permission:chef-kanban'])->only('kanban', 'changeOrderStatus');
    }

    public function index(PaginateRequest $request): \Illuminate\Http\Response|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return EmployeeResource::collection($this->chefList($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function store(EmployeeRequest $request): \Illuminate\Http\Response|EmployeeResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            // Force role to CHEF
            $request->merge(['role_id' => Role::CHEF]);
            return new EmployeeResource($this->employeeService->store($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(EmployeeRequest $request, User $chef): \Illuminate\Http\Response|EmployeeResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $request->merge(['role_id' => Role::CHEF]);
            return new EmployeeResource($this->employeeService->update($request, $chef));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(User $chef): \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $this->employeeService->destroy($chef);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(User $chef): \Illuminate\Http\Response|EmployeeResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new EmployeeResource($chef);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function changePassword(UserChangePasswordRequest $request, User $chef): \Illuminate\Http\Response|EmployeeResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new EmployeeResource($this->employeeService->changePassword($request, $chef));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function changeImage(ChangeImageRequest $request, User $chef): \Illuminate\Http\Response|EmployeeResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new EmployeeResource($this->employeeService->changeImage($request, $chef));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Kanban: returns all orders grouped by status for the chef board.
     */
    public function kanban(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $orders = Order::with('user', 'orderItems.orderItem', 'branch', 'transaction')
                ->whereDate('order_datetime', Carbon::today())
                ->whereNotIn('status', [OrderStatus::CANCELED, OrderStatus::REJECTED])
                ->orderBy('order_datetime', 'desc')
                ->get();

            $grouped = [
                'pending'    => $orders->whereIn('status', [OrderStatus::PENDING])->values(),
                'processing' => $orders->whereIn('status', [OrderStatus::ACCEPT, OrderStatus::PROCESSING])->values(),
                'completed'  => $orders->whereIn('status', [OrderStatus::OUT_FOR_DELIVERY, OrderStatus::DELIVERED])->values(),
            ];

            return response()->json(['data' => $grouped]);
        } catch (Exception $exception) {
            return response()->json(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Change the status of a single order.
     */
    public function changeOrderStatus(Request $request, Order $order): \Illuminate\Http\JsonResponse
    {
        try {
            $request->validate(['status' => 'required|integer']);
            $order->status = $request->status;
            $order->save();
            return response()->json(['data' => $order]);
        } catch (Exception $exception) {
            return response()->json(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    private function chefList(PaginateRequest $request)
    {
        $requests    = $request->all();
        $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
        $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
        $orderColumn = $request->get('order_column') ?? 'id';
        $orderType   = $request->get('order_type') ?? 'desc';

        return User::with('media', 'addresses', 'roles')
            ->whereHas('roles', function ($q) {
                $q->where('id', Role::CHEF);
            })
            ->where(function ($query) use ($requests) {
                foreach (['name', 'email', 'phone', 'status'] as $key) {
                    if (!empty($requests[$key])) {
                        $query->where($key, 'like', '%' . $requests[$key] . '%');
                    }
                }
            })
            ->orderBy($orderColumn, $orderType)
            ->$method($methodValue);
    }
}
