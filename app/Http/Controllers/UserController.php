<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    private $statusCode = 200;
    private $result = false;
    private $message = '';
    private $records = [];

    public function index()
    {
        try {
            $users = User::with('rol')->get();
            if ($users) {
                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registro consultados exitosamente";
                $this->records      = $users;
            } else {
                throw new \Exception("No se encontraron registros");
            }
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
    {}

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return response()->json(User::with('rol:id,rol')->find($id), $this->statusCode);
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
            DB::beginTransaction();
            $user = User::find($id);

            if ($user) {
                $user->name = $request->input('name', $user->name);
                $user->user = strtolower($request->input('user', $user->user));
                $user->rol_id = $request->input('rolId', $user->rol_id);
                $user->status = $request->input('status', $user->status);

                if ($request->input("password") != "" ) {
                    $user->password = Hash::make($request->input('password'));
                }

                if ($user->save()) {
                    DB::commit();
                    $this->statusCode   = 200;
                    $this->result       = true;
                    $this->message      = "Registro editado exitosamente";
                    $this->records      = $user;
                } else {
                    throw new \Exception("Ha ocurrido un error al actualizar el registro");
                }
            } else {
                throw new \Exception("No se encontró el registro");
            }
        } catch (\Exception $e) {
            DB::rollback();
            $this->statusCode   = 200;
            $this->result       = false;
            $this->message = env('APP_DEBUG') ? $e->getMessage() : $this->message;
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
            $user = User::find($id);

            if ($user) {
                $user->status = 1;
                
                if ($user->save()) {
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
