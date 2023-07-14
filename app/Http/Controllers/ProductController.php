<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Brand;
use App\Models\Presentation;
use App\Models\Product;
use App\Models\UnitMeasurement;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
                )->where('status', true)->get();

            if ($products) {
                
                $products->map(function($product, $key) {
                    $product->stock = Batch::select(
                        'id', 
                        'stock',
                        'manufacturing_date',
                        'expiration_date')
                        ->where('product_id', $product->id)
                        ->where('stock','>',0)
                        ->get();
                        
                    $product->totalStock = $product->stock->sum('stock');
                });

                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registro consultados exitosamente";
                $this->records      = $products;
            } else {
                throw new \Exception("No se encontraron registros");
            }
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

            $pathImage = "";

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $destination = 'images/products/';
                $fileName = time().'.'.$file->extension();
                $file->move($destination, $fileName);
                $pathImage = $destination . $fileName;
            }
            
            DB::beginTransaction();
            $newProduct = Product::create([
                'name' => $request->input('name'),
                'brand_id' => $request->input('brandId'),
                'price' => $request->input('price'),
                'cost' => $request->input('cost'),
                'presentation_id' => $request->input('presentationId'),
                'unit_measurement_id' => $request->input('unitMeasurementId'),
                'image' => $pathImage,
                'status' => 0
            ]);

            if (!$newProduct) {
                throw new \Exception("Ocurrió un problema guardar el registro. Por favor inténtelo nuevamente");
            } else {
                
                $product = Product::with(
                    'brand:id,name',
                    'presentation:id,presentation',
                    'unitMeasurement:id,unit_measurement'
                    )
                    ->where('id', $newProduct->id)
                    ->first();

                $product->totalStock = 0;

                DB::commit();
                $this->statusCode   =   201;
                $this->result       =   true;
                $this->message      =   "Se ha guardado correctamente el registro";
                $this->records      =   $product;
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

            $pathImage = "";

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $destination = 'images/products/';
                $fileName = time().'.'.$file->extension();
                $file->move($destination, $fileName);
                $pathImage = $destination . $fileName;
            }

            DB::beginTransaction();
            $record = Product::find($id);
            $record->name = $request->input('name', $record->name);
            $record->brand_id = $request->input('brandId', $record->brand_id);
            $record->presentation_id = $request->input('presentationId', $record->presentation_id);
            $record->unit_measurement_id = $request->input('unitMeasurementId', $record->unit_measurement_id);
            $record->price = $request->input('price', $record->price);
            $record->cost = $request->input('cost', $record->cost);
            $record->status = $request->input('status', $record->status);

            if ($pathImage != "" ) {
                $record->image = $pathImage;
            }

            if ($record->save()) {

                $product = Product::with(
                    'brand:id,name',
                    'presentation:id,presentation',
                    'unitMeasurement:id,unit_measurement'
                    )
                    ->where('id', $record->id)
                    ->first();

                DB::commit();
                $this->statusCode   =   201;
                $this->result       =   true;
                $this->message      =   "Se ha editado correctamente el registro";
                $this->records      =   $product;
            } else {
                throw new \Exception("Ocurrió un problema al editar el registro");
            }
        } catch (Exception $e) {
            DB::rollBack();
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
            $product = Product::find($id);

            if ($product) {
                $product->status = 1;
                
                if ($product->save()) {
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

    public function search(Request $request) {
        try {

            $search = $request->input('search');
            $products = Product::with(
                'brand:id,name',
                'presentation:id,presentation',
                'unitMeasurement:id,unit_measurement'
                )->where('status', 1)->where('name', 'LIKE', "%{$search}%")->get();

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
