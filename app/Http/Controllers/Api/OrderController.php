<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Validation\Validator as ValidatorContracts;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $orders = Order::query()
            ->with('products')
            ->where('user_id', request()->user()->id)
            ->orderBy('id', 'DESC')
            ->paginate(24);

        if (!$orders)
        {
            return response()->json([
                'status' => 'fails',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => 'ok',
            'data' => $orders
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = $this->validator($request->all());

        if ($validator->fails())
        {
            return response()->json([
                'status' => 'fails',
                'errors' => $validator->getMessageBag(),
            ], 400);
        }

        DB::beginTransaction();

        try {
            $order = Order::query()->create([
                'order_code' => Order::generateOrderCode(),
                'user_id' => $request->user()->id,
                'address' => $request->address,
                'shipping_date' => $request->shipping_date,
            ]);

            foreach ($request->products as $index => $productData) {
                $product = Product::query()->whereKey($productData['id'])->first();

                OrderProduct::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $productData['id'],
                    'quantity' => $productData['quantity'],
                    'price' => $product->price,
                ]);
            }
        }
        catch (\Exception $e)
        {
            DB::rollback();

            return response()->json([
                'status' => 'fails',
                'data' => ['message' =>  $e->getMessage()]
            ], 500);
        }

        DB::commit();

        return response()->json([
            'status' => 'ok', // 'fails'
            'data' => $order
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $order = Order::query()
            ->with('products')
            ->where('id', $id)
            ->where('user_id', request()->user()->id)
            ->first();

        if (!$order)
        {
            return response()->json([
                'status' => 'fails',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => 'ok',
            'data' => $order,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = $this->validator($request->all());

        if ($validator->fails())
        {
            return response()->json([
                'status' => 'fails',
                'errors' => $validator->getMessageBag(),
            ], 400);
        }

        $order = Order::query()
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->whereNull('shipping_date')
            ->first();

        if (!$order)
        {
            return response()->json([
                'status' => 'fails',
                'data' => null,
            ], 404);
        }

        DB::beginTransaction();

        try {
            $order->update([
                'address' => $request->address,
                'shipping_date' => $request->shipping_date,
            ]);

            OrderProduct::query()->where('order_id', $order->id)->delete();

            foreach ($request->products as $index => $productData) {
                $product = Product::query()->whereKey($productData['id'])->first();

                OrderProduct::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $productData['id'],
                    'quantity' => $productData['quantity'],
                    'price' => $product->price,
                ]);
            }
        }
        catch (\Exception $e)
        {
            DB::rollback();

            return response()->json([
                'status' => 'fails',
                'data' => ['message' =>  $e->getMessage()]
            ], 500);
        }

        DB::commit();

        return response()->json([
            'status' => 'ok', // 'fails'
            'data' => $order
        ]);
    }

    /**
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    private function validator(array $data): ValidatorContracts
    {
        return Validator::make($data, [
            'address' => ['required', 'string', 'max:255'],
            'shipping_date' => ['nullable', 'date'],
            'products' => ['required', 'array'],
            'products.*.id' => ['required', 'numeric', 'exists:products,id'],
            'products.*.quantity' => ['required', 'numeric'],
        ]);
    }
}
