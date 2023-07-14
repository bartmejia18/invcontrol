<?php

namespace App\Http\Controllers;

use App\Models\Presentation;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PresentationController extends Controller
{

    private $statusCode = 200;
    private $result = false;
    private $message = '';
    private $records = [];

    public function index()
    {
        try {
            $presentation = Presentation::where('status',1)->get();
            if ($presentation) {
                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registro consultados exitosamente";
                $this->records      = $presentation;
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
            $newPresentation = Presentation::create([
                'presentation' => $request->input('presentation'),
                'status' => true
            ]);

            if (!$newPresentation) {
                throw new \Exception("Ocurrió un problema guardar la presentación. Por favor inténtelo nuevamente");
            } else {
                $this->statusCode   =   201;
                $this->result       =   true;
                $this->message      =   "Se ha registrado correctamente la presentación";
                $this->records      =   $newPresentation;
            }
        } catch (\Exception $e) {
            $this->statusCode   =   200;
            $this->result       =   false;
            $this->message      =   env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al guardar la presentación. Por favor inténtelo nuevamente";
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
        return response()->json(Presentation::find($id), $this->statusCode);
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
            $record = Presentation::find($id);
            $record->presentation = $request->input('presentation', $record->presentation);
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
        try {
            DB::beginTransaction();
            $presentation = Presentation::find($id);

            if ($presentation) {
                $presentation->status = false;
                
                if ($presentation->save()) {
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
}
