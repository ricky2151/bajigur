<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Http\Requests\StoreProduct;
use App\Http\Requests\UpdateProduct;

class ProductController extends Controller
{
    private $product;
    public function __construct(Product $product)
    {
        $this->product = $product;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = $this->product->orderBy('id', 'desc')->get();

        $data = $data->map(function ($data) { 
            $data = Arr::add($data, 'category_name', $data['category']['name']);
            $countTransaction = 0;
            $data->detail_transactions->map(function ($detail) use(&$countTransaction) {
                $countTransaction += $detail->qty;
            });
            $data = Arr::add($data, 'count_transaction', $countTransaction);
            return Arr::except($data, ['category', 'detail_transactions']);
        });
        
        return response()->json(['error' => false, 'data'=>$data]);
    }

    

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProduct $request)
    {
        $data = $request->validated();
        $this->product->create($data);
        return response()->json([
            "error" => false,
            "message" => "product successfully created !"
        ]);
    }

    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateProduct $request, Product $product)
    {
        $data = $request->validated();
        
        $product->update($data);
        
        return response()->json([
            "error" => false,
            "message" => "product successfully updated !"
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            "error" => false,
            "message" => "product successfully deleted !"
        ]);
    }
}
