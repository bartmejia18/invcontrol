<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Product;
use App\Models\PurchaseDetails;
use App\Models\Purchases;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchasesController extends Controller
{

    private $statusCode = 200;
    private $result = false;
    private $message = '';
    private $records = [];

    public function index()
    {
        try {
            $purchases = Purchases::all();
            if ($purchases) {
                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registro consultados exitosamente";
                $this->records      = $purchases;
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
            $newPurchase = Purchases::create([
                'supplier_id' => $request->input('supplierId'),
                'date' => $request->input('date'),
                'total' => $request->input('total'),
                'status' => 0
            ]);


            if (!$newPurchase) {
                throw new \Exception("Ocurrió un problema guardar el registro. Por favor inténtelo nuevamente");
            } else {

                $detail = json_decode($request->input('details'), true);
                foreach ($detail as $item) {

                    $newBatch = Batch::create([
                        'product_id' => $item['productId'],
                        'manufacturing_date' => $item['manufacturingDate'],
                        'expiration_date' => $item['expirationDate'],
                        'stock' => $item['stock'],
                        'cost' => $item['cost'],
                        'subtotal' => $item['subtotal']
                    ]);

                    if ($newBatch) {
                        PurchaseDetails::create([
                            'purchase_id' => $newPurchase->id,
                            'batch_id' => $newBatch->id
                        ]);

                        DB::commit();
                        $this->statusCode   =   201;
                        $this->result       =   true;
                        $this->message      =   "Se ha guardado correctamente el registro";
                        $this->records      =   $newPurchase;
                    } else {
                        throw new \Exception("Ocurrió un problema guardar el registro. Por favor inténtelo nuevamente");
                    }
                }
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
        return response()->json(Purchases::find($id), $this->statusCode);
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
            $record = Purchases::find($id);
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
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $purchase = Purchases::find($id);

            if ($purchase) {
                $purchase->status = 1;

                $details = PurchaseDetails::where('purchase_id', $purchase->id)->get();

                $details->map(function($item, $key) {
                    Batch::find($item->batch_id)->delete();
                });
                
                if ($purchase->save()) {
                    DB::commit();
                    $this->statusCode   =   201;
                    $this->result       =   true;
                    $this->message      =   "Se ha eliminado el registro correctamente";
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

    public function getPurchases(Request $request)
    {
        $purchases = new Purchases();
        switch ($request->input('type')) {
            case 1:
                $purchases = Purchases::with('supplier:id,name')->where('date', $request->input('startDate'))->get();
                $purchases->map(function ($purchase, $key) {
                    $purchase->details = $this->getDetailsPurchases($purchase->id);
                });
                break;
            case 2:
                $purchases = Purchases::with('supplier:id,name')->whereBetween('date', [$request->input('startDate'), $request->input('endDate')])->get();
                $purchases->map(function ($purchase, $key) {
                    $purchase->details = $this->getDetailsPurchases($purchase->id);
                });
                break;
            case 3:
                $timestamp = strtotime($request->input('startDate'));
                $month = date('m', $timestamp);
                $year = date('Y', $timestamp);
                $purchases = Purchases::with('supplier:id,name')->whereYear('date', $year)->whereMonth('date', $month)->get();
                $purchases->map(function ($purchase, $key) {
                    $purchase->details = $this->getDetailsPurchases($purchase->id);
                });
                break;
            case 4:
                $purchases = Purchases::with('supplier:id,name')->whereBetween('date', [$request->input('startDate'), $request->input('endDate')])->get();
                $purchases->map(function ($purchase, $key) {
                    $purchase->details = $this->getDetailsPurchases($purchase);
                });
                break;
        }

        if (count($purchases) == 0) {
            $this->statusCode   = 200;
            $this->result       = false;
            $this->message      = "No sé encontraron registros";
        } else {
            $this->statusCode   = 200;
            $this->result       = true;
            $this->records      = $purchases;
        }

        return response()->json([
            'result'    => $this->result,
            'message'   => $this->message,
            'records'   => $this->records,
        ], $this->statusCode);
    }

    public function getDetailsPurchases($purchaseId)
    {
        $purchaseDetail = "";
        $purchaseDetail = PurchaseDetails::where('purchase_id', $purchaseId)->get();
        $purchaseDetail->map(function ($detail, $key) {
            $batch = Batch::find($detail->id);
            $batch->product = Product::with(
                'brand:id,name',
                'presentation:id,presentation',
                'unitMeasurement:id,unit_measurement'
            )->where('id', $batch->product_id)->first();
            $detail->batch = $batch;
        });

        return $purchaseDetail;
    }
}
