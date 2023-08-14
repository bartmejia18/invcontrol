<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetails;
use App\Models\UnitMeasurement;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller {

    private $statusCode = 200;
    private $result = false;
    private $message = '';
    private $records = [];

    public function index() {
        try {
            $sales = Sale::all();
            if ($sales) {
                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registro consultados exitosamente";
                $this->records      = $sales;
            } else
                throw new \Exception("No se encontraron registros");
        } catch (\Exception $e) {
            $this->statusCode = 200;
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
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $newSale = Sale::create([
                'customer' => $request->input('customer'),
                'date' => $request->input('date'),
                'total' => $request->input('total'),
                'status' => 0

            ]);

            if (!$newSale) {
                throw new \Exception("Ocurrió un problema guardar el registro. Por favor inténtelo nuevamente");
            } else {

                $details = json_decode($request->input('details'),true);
                foreach ($details as $detail) {

                    $unitMeasurement = UnitMeasurement::find($detail['unitMeasurementId']);

                    $batchs = Batch::where('product_id', $detail['productId'])
                                    ->where('stock','>',0)->get();

                    $totalQuantitySold = $detail['quantity'] * $unitMeasurement->value;

                    if ($batchs->sum('stock') >= $totalQuantitySold) {

                        $tempStock = $totalQuantitySold;
        
                        foreach ($batchs as $batch) {
                            if ($tempStock != 0) {
                                if ($batch->stock >= $tempStock) {
                                    $batch->stock = $batch->stock - $tempStock;
                                    $tempStock = 0;
                                    $batch->save();
                                } else {
                                    $tempStock = $tempStock - $batch->stock;
                                    $batch->stock = 0;
                                    $batch->save();
                                }
                            }
                        }

                        SaleDetails::create([
                            "sale_id" => $newSale->id,
                            "product_id" => $detail['productId'],
                            "unit_measurement_id" => $detail['unitMeasurementId'],
                            "price" => $detail['price'],
                            "quantity" => $detail['quantity'],
                            "subtotal" => $detail['subtotal']    
                        ]);
                    } else {
                        throw new \Exception("La cantidad de productos ingresados es mayor al stock");
                    }
                }
                DB::commit();
                $this->statusCode   =   201;
                $this->result       =   true;
                $this->message      =   "Se ha guardado correctamente el registro";
                $this->records      =   $newSale;
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->statusCode   =   200;
            $this->result       =   false;
            $this->message      =   env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al guardar el registro. Por favor inténtelo nuevamente";
        } finally {
            $response = [
                'result'    => $this->result,
                'message'   => $this->message,
                'records'   => $this->records,
            ];
            return response()->json($response, $this->statusCode);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $sale = Sale::find($id);
            if ($sale) {
                
                $sale->details = $this->getDetailsSales($sale->id);

                $this->statusCode   =   201;
                $this->result       =   true;
                $this->message      =   "Registro consultado exitosamente";
                $this->records      =   $sale;
            } else {
                throw new \Exception("No se encontraron registros");
            }
        } catch (Exception $e) {
            $this->statusCode   = 200;
            $this->result       = false;
            $this->message      = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al consultar los datos";
        } finally {
            $response = [
                'result'    => $this->result,
                'message'   => $this->message,
                'records'   => $this->records,
            ];
            return response()->json($response, $this->statusCode);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        try {
            $record = Sale::find($id);
            $record->date = $request->input('date', $record->date);
            $record->total = $request->input('total', $record->total);

            if ($record->save()) {

                $this->statusCode   =   201;
                $this->result       =   true;
                $this->message      =   "Se ha editado correctamente el registro";
                $this->records      =   $record;
            } else {
                throw new \Exception("Ocurrió un problema al editar el registro");
            }
        } catch (Exception $e) {
            $this->statusCode   = 200;
            $this->result       = false;
            $this->message      = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al editar el registro";
        } finally {
            $response = [
                'result'    => $this->result,
                'message'   => $this->message,
                'records'   => $this->records,
            ];
            return response()->json($response, $this->statusCode);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $sale = Sale::find($id);

            if ($sale) {
                $sale->status = 1;
                $sale->user_cancel_id = $request->input('userCancelId');
                $sale->date_cancel = Carbon::now()->toDateString();

                $detail = SaleDetails::where('sale_id', $sale->id)->get();

                $detail->map(function($item, $key) {
                    $batchs = Batch::where('product_id', $item->product_id)->get();
                    $batchsEmpty = $batchs->filter(function($item) {return $item->stock == 0;});
                    $batchsNoEmpty = $batchs->filter(function($item) {return $item->stock != 0;});
                    if ($batchsEmpty->count() > 0) {
                        $lastBatch = $batchsEmpty->last();
                        $lastBatch->stock = $lastBatch->stock + $item->quantity;
                        $lastBatch->save();
                    } else if ($batchsNoEmpty->count() > 0){
                        $firstBatch = $batchsNoEmpty->first();
                        $firstBatch->stock = $firstBatch->stock + $item->quantity;
                        $firstBatch->save();
                    }

                });
                
                if ($sale->save()) {
                    DB::commit();

                    $salesDelete = Sale::find($sale->id);
                    $salesDelete->details = $this->getDetailsSales($salesDelete->id);
                    
                    $this->statusCode   =   201;
                    $this->result       =   true;
                    $this->message      =   "Se ha eliminado el registro correctamente";
                    $this->records      =   $salesDelete;
                }
            } else {
                throw new \Exception("No se encontró el registro");
            }
        } catch (Exception $e) {
            DB::rollBack();
            $this->statusCode   = 200;
            $this->result       = false;
            $this->message      = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al eliminar el registro";
        } finally {
            $response = [
                'result'    => $this->result,
                'message'   => $this->message,
                'records'   => $this->records,
            ];
            return response()->json($response, $this->statusCode);
        }
    }

    public function getSales(Request $request) {

        $sales = new Sale();

        switch ($request->input('type')) {
            case 1:
                $sales = Sale::where('date', $request->input('startDate'))->get();
                $sales->map(function($sale, $key) { 
                    $sale->details = $this->getDetailsSales($sale->id);
                });
                break;
            case 2:
                $sales = Sale::whereBetween('date', [$request->input('startDate'), $request->input('endDate')])->get();
                $sales->map(function($sale, $key) { 
                    $sale->details = $this->getDetailsSales($sale->id);
                });
                break;
            case 3:
                $timestamp = strtotime($request->input('startDate'));
                $month = date('m', $timestamp);
                $year = date('Y', $timestamp);
                $sales = Sale::whereYear('date', $year)->whereMonth('date', $month)->get();
                $sales->map(function($sale, $key) { 
                    $sale->details = $this->getDetailsSales($sale->id);
                });
                break;
            case 4:
                $sales = Sale::whereBetween('date', [$request->input('startDate'), $request->input('endDate')])->get();
                $sales->map(function($sale, $key) { 
                    $sale->details = $this->getDetailsSales($sale->id);
                });
                break;
        }

        if (count($sales) == 0 ) {
            $this->statusCode   = 200;
            $this->result       = false;
            $this->message      = "No sé encontraron registros";
        } else {
            $this->statusCode   = 200;
            $this->result       = true;
            $this->records      = $sales;
        }

        return response()->json([
            'result'    => $this->result,
            'message'   => $this->message,
            'records'   => $this->records,
        ], $this->statusCode);
    }

    public function getDetailsSales($saleId) {
        $saleDetail = "";
        $saleDetail = SaleDetails::where('sale_id', $saleId)->get();
        $saleDetail->map(function($detail, $key) {
            $detail->product = Product::with(
                'brand:id,name',
                'presentation:id,presentation'
                )->where('id',$detail->product_id)->first();
            $detail->unit_measurement = UnitMeasurement::find($detail->unit_measurement_id);
        });

        return $saleDetail;
    }
}
