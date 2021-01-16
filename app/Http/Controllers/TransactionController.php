<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Http\Requests\StoreTransaction;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TransactionController extends Controller
{
    private $transaction;
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = $this->transaction->orderBy('id', 'desc')->get();

        $data = $data->map(function ($data) { 
            $data = Arr::add($data, 'user_email', $data['user']['email']);
            $data = Arr::add($data, 'detail_transactions', $data['detail_transactions']);
            return Arr::except($data, ['user']);
        });
        
        return response()->json(['error' => false, 'data'=>$data]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTransaction $request)
    {
        $dataDetailTransaction = $request->validated();
        //validate product qty is enough
        for($i = 0;$i<count($dataDetailTransaction['items']);$i++) {
            $productId = $dataDetailTransaction['items'][$i]['product_id'];
            $qty = $dataDetailTransaction['items'][$i]['qty'];
            $productStock = Product::find($productId)->stock;
            if($productStock < $qty) { //qty : 4, stock : 3, so transaction cannot be continue
                return response()->json(['error' => true, 'message'=>'product with id : ' . $productId . ' just have stock ' . $productStock], 422);
            }
        }
        //make data transaction
        $dataTransaction = [
            'datetime' => Carbon::now()->format('d-M-Y H:i:s'),
            'total' => 0
        ];
        $user = Auth::user();
        $transaction = $user->transactions()->create($dataTransaction);
        $transaction->detail_transactions()->createMany($dataDetailTransaction['items']);
        $totalTransaction = 0;
        $totalPoint = 0;
        $transaction->detail_transactions->map(function ($detail) use(&$totalTransaction, &$totalPoint){
            $product = $detail->product;
            // calculate qty and update stock
            $product->stock = $product->stock - $detail->qty;
            $product->save();
            // calculate total transaction
            $totalTransaction += ($product->price * $detail->qty);
            // calculate point for user
            $totalPoint += ($product->points_earned * $detail->qty);
        });
        //update total transaction
        $transaction->total = $totalTransaction;
        $transaction->save();
        //update point for user
        $user->points = $user->points + $totalPoint;
        $user->save();

        return response()->json(['error' => false, 'message'=>"transaction successfully created !"]);

    }

    public function myHistoryTransaction() {
        $user = Auth::user();
        $data = $user->transactions->map(function ($item) {
            return Arr::add($item, 'detail_transactions', $item['detail_transactions']->map(function($detail) {
                $detail = Arr::add($detail, 'product_name', $detail['product']['name']);
                $detail = Arr::add($detail, 'product_price', $detail['product']['price']);
                return Arr::except($detail, ['product']);
            }));
        });
        
        return response()->json(['error' => false, 'data'=>$data]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function show(Transaction $transaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function edit(Transaction $transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Transaction $transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(Transaction $transaction)
    {
        //
    }
}
