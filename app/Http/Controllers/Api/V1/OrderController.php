<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function getOrders(Request $request)
    {
        $query = Order::with(['orderDetails', 'buyer', 'center', 'customer'])
            ->select([
                'orders.id',
                'orders.order_number',
                'orders.order_amount',
                'orders.order_status',
                'orders.order_date',
                'orders.updated_at',
                'orders.delivery_date',
                'orders.buyer_id',
                'orders.center_id',
                'orders.customer_id',
                'orders.payment_status',
                'orders.payment_method',
                'orders.transaction_ref',
            ]);

        // Filter by date range
        if ($request->has('period_type') && $request->has('start_date') && $request->has('end_date')) {
            $periodType = $request->period_type;
            $startDate = $request->start_date;
            $endDate = $request->end_date . ' 23:59:59';

            $query->whereBetween($periodType, [$startDate, $endDate]);
        }

        // Filter by search field and query
        if ($request->has('search_field') && $request->has('search_query')) {
            $searchField = $request->search_field;
            $searchQuery = $request->search_query;

            if ($searchField === 'buyer') {
                $query->where(function($q) use ($searchQuery) {
                    $q->whereHas('buyer', function ($q) use ($searchQuery) {
                        $q->where('name', 'like', "%{$searchQuery}%");
                    })
                    ->orWhereHas('customer', function ($q) use ($searchQuery) {
                        $q->where('name', 'like', "%{$searchQuery}%");
                    });
                });
            } elseif ($searchField === 'product_name') {
                $query->whereHas('orderDetails', function ($q) use ($searchQuery) {
                    $q->where('product_name', 'like', "%{$searchQuery}%");
                });
            }
        }

        // Filter by order status
        if ($request->has('order_status')) {
            $query->where('order_status', $request->order_status);
        }

        // Get orders with pagination
        $orders = $query->get()->map(function ($order) {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'product_name' => $order->orderDetails->first()?->product_name ?? '',
                'amount' => $order->orderDetails->first()?->price ?? 0,
                'pv' => $order->orderDetails->first()?->pv ?? 0,
                'quantity' => $order->orderDetails->first()?->quantity ?? 0,
                'order_amount' => $order->order_amount,
                'buyer' => $order->buyer?->name ?? $order->customer?->name ?? '',
                'center' => $order->center?->name ?? '',
                'order_status' => $order->order_status,
                'order_date' => $order->order_date ?? $order->created_at,
                'updated_at' => $order->updated_at,
                'delivery_date' => $order->delivery_date,
                'payment_status' => $order->payment_status,
                'payment_method' => $order->payment_method,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $orders
        ]);
    }

    public function deleteOrder($id)
    {
        try {
            DB::beginTransaction();

            $order = Order::findOrFail($id);
            
            // Instead of deleting, update the order status to 'withdraw_order'
            $order->update([
                'order_status' => 'withdraw_order'
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Order withdrawn successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to withdraw order: ' . $e->getMessage()
            ], 500);
        }
    }
} 