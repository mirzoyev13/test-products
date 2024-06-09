<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use function abort;
use function dd;
use function now;
use function response;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::query()
            ->with('items.product')
            ->when($request->has('status'), function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->paginate($request->get('per_page', 15));

        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $validatedData = [
            'customer' => 'someCustomer',
            'warehouse_id' => 1,
            'items' => [
                'item' => [
                    'product_id' => 1,
                    'count' => 2
                ]
            ]
        ];

//        $validatedData = $request->validate([
//            'customer' => 'required|string|max:255',
//            'warehouse_id' => 'required|exists:warehouses,id',
//            'items' => 'required|array',
//            'items.*.product_id' => 'required|exists:products,id',
//            'items.*.count' => 'required|integer|min:1',
//        ]);

        DB::transaction(function () use ($validatedData) {
            $order = Order::create([
                'customer' => $validatedData['customer'],
                'warehouse_id' => $validatedData['warehouse_id'],
                'status' => 'active',
            ]);

            foreach ($validatedData['items'] as $item) {
                $orderItem = new OrderItem([
                    'product_id' => $item['product_id'],
                    'count' => $item['count'],
                ]);
                $order->items()->save($orderItem);

                $stock = Stock::where('product_id', $item['product_id'])
                    ->where('warehouse_id', $validatedData['warehouse_id'])
                    ->first();

                if ($stock->stock < $item['count']) {
                    abort(400, 'Not enough stock');
                }


                $stock->stock = (($stock->stock) - $item['count']);

            }
        });

        return response()->json(['message' => 'Ордер был успешно создан']);
    }

    public function update(Request $request, Order $order)
    {
        $validatedData = $request->validate([
            'customer' => 'required|string|max:255',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.count' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($validatedData, $order) {
            foreach ($order->items as $item) {
                $stock = Stock::where('product_id', $item->product_id)
                    ->where('warehouse_id', $order->warehouse_id)
                    ->first();

                $stock->increment('stock', $item->count);
            }

            $order->update([
                'customer' => $validatedData['customer'],
            ]);

            $order->items()->delete();

            foreach ($validatedData['items'] as $item) {
                $orderItem = new OrderItem([
                    'product_id' => $item['product_id'],
                    'count' => $item['count'],
                ]);
                $order->items()->save($orderItem);

                $stock = Stock::where('product_id', $item['product_id'])
                    ->where('warehouse_id', $order->warehouse_id)
                    ->first();

                if ($stock->stock < $item['count']) {
                    abort(400, 'Not enough stock');
                }

                $stock->stock = (($stock->stock) - $item['count']);

            }
        });

        return response()->json(['message' => 'Ордер был успешно обновлен']);
    }

    public function complete(Order $order)
    {
        if ($order->status !== 'active') {
            abort(400, 'Ордер не является активным');
        }

        $order->update([
            'status' => 'completed',
            'completed_at' => now() ?? Carbon::now(),
        ]);

        return response()->json(['message' => 'Ордер успешно завершен']);
    }

    public function cancel(Order $order)
    {
        if ($order->status === 'canceled') {
            abort(400, 'Ордер уже был отменен');
        }

        $order->update([
            'status' => 'canceled',
        ]);

        foreach ($order->items as $item) {
            $stock = Stock::where('product_id', $item->product_id)
                ->where('warehouse_id', $order->warehouse_id)
                ->first();

            $stock->stock = (($stock->stock) + $item->count);
        }

        return response()->json(['message' => 'Ордер был успешно отменен']);
    }

    public function resume(Order $order)
    {
        if ($order->status !== 'canceled') {
            abort(400, 'Ордер не был отменен чтобы его восстановить');
        }

        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $stock = Stock::where('product_id', $item->product_id)
                    ->where('warehouse_id', $order->warehouse_id)
                    ->first();

                if ($stock->stock < $item->count) {
                    abort(400, 'Недостаточно средств чтобы восстановить ордер');
                }

                $stock->stock = (($stock->stock) - $item->count);
            }

            $order->update([
                'status' => 'active',
            ]);
        });

        return response()->json(['message' => 'Ордер был успешно восстановлен']);
    }

    public function getMovements(Request $request)
    {
        $query = Order::with('items.product', 'warehouse')
            ->when($request->has('product_id'), function ($query) use ($request) {
                return $query->whereHas('items', function ($query) use ($request) {
                    $query->where('product_id', $request->product_id);
                });
            })
            ->when($request->has('warehouse_id'), function ($query) use ($request) {
                return $query->where('warehouse_id', $request->warehouse_id);
            })
            ->when($request->has('start_date') && $request->has('end_date'), function ($query) use ($request) {
                return $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
            })
            ->paginate($request->get('per_page', 15));

        return response()->json($query);
    }

}
