<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Brand;
use App\Models\Presentation;
use App\Models\Product;
use App\Models\UnitMeasurement;
use Exception;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    private $statusCode = 200;
    private $result = false;
    private $message = '';
    private $records = [];

    public function index()
    {
        try {
            $products = Product::with(
                'brand:id,name',
                'presentation:id,presentation',
                'unitMeasurement:id,unit_measurement'
                )->get();

            if ($products) {
                
                $products->map(function($product, $key) {
                    $product->stock = Batch::select(
                        'id', 
                        'stock',
                        'manufacturing_date',
                        'expiration_date')->where('product_id', $product->id)->get()->filter(function($item) {
                            return $item->stock > 0;
                        });
                    $product->totalStock = $product->stock->sum('stock');
                });

                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registro consultados exitosamente";
                $this->records      = $products;
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
            $newProduct = Product::create([
                'name' => $request->input('name'),
                'brand_id' => $request->input('brandId'),
                'price' => $request->input('price'),
                'cost' => $request->input('cost'),
                'presentation_id' => $request->input('presentationId'),
                'unit_measurement_id' => $request->input('unitMeasurementId'),
                'image' => $request->input('image'),
                'status' => 1
            ]);

            if (!$newProduct) {
                throw new \Exception("Ocurrió un problema guardar el registro. Por favor inténtelo nuevamente");
            } else {
                $newProduct->brand = Brand::where('id', $newProduct->brand_id)->pluck('name');
                $newProduct->presentation = Presentation::where('id', $newProduct->presentation_id)->pluck('presentation');
                $newProduct->unit_measurement = UnitMeasurement::where('id', $newProduct->unit_measurement_id)->pluck('unit_measurement');

                $this->statusCode   =   201;
                $this->result       =   true;
                $this->message      =   "Se ha guardado correctamente el registro";
                $this->records      =   $newProduct;
            }
        } catch (\Exception $e) {
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
        return response()->json(Product::get($id), $this->statusCode);
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
            $record = Product::find($id);
            $record->name = $request->input('name', $record->name);
            $record->brand_id = $request->input('brandId', $record->brand_id);
            $record->presentation_id = $request->input('presentationId', $record->presentation_id);
            $record->unit_measurement_id = $request->input('unitMeasurementId', $record->unit_measurement_id);
            $record->status = $request->input('status', $record->status);

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

        return Product::find($id)->delete();
    }

    public function search(Request $request) {
        try {

            $search = $request->input('search');
            $products = Product::with(
                'brand:id,name',
                'presentation:id,presentation',
                'unitMeasurement:id,unit_measurement'
                )->where('name', 'LIKE', "%{$search}%")->get();

            if ($products) {

                $products->map(function($product, $key) {
                    $product->stock = Batch::select(
                        'id', 
                        'stock',
                        'manufacturing_date',
                        'expiration_date')->where('product_id', $product->id)->get();
                    $product->totalStock = $product->stock->sum('stock');
                });

                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registro consultados exitosamente";
                $this->records      = $products;
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
}
