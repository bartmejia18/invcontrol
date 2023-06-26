<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use Exception;
use Illuminate\Http\Request;

class BatchController extends Controller
{

    private $statusCode = 200;
    private $result = false;
    private $message = '';
    private $records = [];

    public function index()
    {
        try {
            $batch = Batch::all();
            if ($batch) {
                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registro consultados exitosamente";
                $this->records      = $batch;
            } else
                throw new \Exception("No se encontraron registros");
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
            $newBatch = Batch::create([
                'product_id' => $request->input('productId'),
                'manufacturing_date' => $request->input('manufacturingDate'),
                'expiration_date' => $request->input('expirationDate'),
                'stock' => $request->input('stock'),
                'price' => $request->input('price'),
                'cost' => $request->input('cost')
            ]);

            if (!$newBatch) {
                throw new \Exception("Ocurrió un problema guardar el registro. Por favor inténtelo nuevamente");
            } else {
                $this->statusCode   =   201;
                $this->result       =   true;
                $this->message      =   "Se ha guardado correctamente el registro";
                $this->records      =   $newBatch;
            }
        } catch (\Exception $e) {
            $this->statusCode   =   204;
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
        return response()->json(Batch::find($id), $this->statusCode);
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
            $record = Batch::find($id);
            $record->product_id = $request->input('productId', $record->product_id);
            $record->manufacturing_date = $request->input('manufacturingDate', $record->manufacturing_date);
            $record->expiration_date = $request->input('expirationDate', $record->expiration_date);
            $record->stock = $request->input('stock', $record->stock);
            $record->price = $request->input('price', $record->price);
            $record->cost = $request->input('cost', $record->cost);

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
        return Batch::find($id)->delete();
    }
}
