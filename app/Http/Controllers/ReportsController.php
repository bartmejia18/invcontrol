<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Sale;
use stdClass;

class ReportsController extends Controller
{

    private $statusCode = 200;
    private $result = false;
    private $message = '';
    private $records = [];

    public function reportGeneral()
    {
       try {
            $generalData = new stdClass();

            $products = Product::with('batchs')
                                    ->where('status', 0)
                                    ->get();
            
            $productsWithStock = $products->map(function ($item, $key) {
                                    $item->totalStock = $item->batchs->sum('stock');
                                    return $item;
                                });

            $productsInStock = $productsWithStock->filter(function ($item) {
                                    return $item->totalStock > 0;
                                });
            
            $productsTotalCost = $products->map(function ($product, $key) {
                $costs = 0;
                if ($product->batchs->count() > 0) {
                    $costs = $product->batchs->map(function ($batch, $key) {
                        return $batch->stock * $batch->cost;
                    })->sum();
                }
                $product->totalCost = $costs;
                return $product;
            });

            $productUnit = $products->map(function ($product, $key) {
                $units = ProductUnit::where('product_id', $product->id)->first();
                if ($units) {
                    $totalStock = $product->batchs->sum('stock');
                    return $units->price * $totalStock;
                } else {
                    return 0;
                }
            });

            $sales = Sale::where('status', 0)->sum('total');
            
            $generalData->totalProducts = $products->count();
            $generalData->productsInStock = $productsInStock->count();
            $generalData->productsTotalCost = $productsTotalCost->sum('totalCost');
            $generalData->productsTotalPrice = $productUnit->sum();
            $generalData->totalSales = $sales;
            
            $this->statusCode   = 200;
            $this->result       = true;
            $this->message      = "Registro consultados exitosamente";
            $this->records      = $generalData;

        } catch (\Exception $e) {
            $this->statusCode = 204;
            $this->result = false;
            $this->message = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al consultar los datos";
        } finally {
            $response = [
                'result'    => $this->result,
                'message'   => $this->message,
                'records'   => $this->records,
            ];
            return response()->json($response, $this->statusCode);
        }
    }
}
