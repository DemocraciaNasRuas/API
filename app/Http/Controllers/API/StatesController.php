<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\EventPut;
use App\Http\Requests\StatePost;
use App\Models\State;
use Illuminate\Http\Request;
use App\Http\Resources\State as StateResource;

class StatesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return StateResource::collection(State::paginate(request()->per_page?: 10));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StatePost $request
     * @return StateResource
     */
    public function store(StatePost $request)
    {
        try{
            return StateResource::make(State::create($request->all()));
        }catch (\Exception $exception){
            return response()->json([
                'error' => true,
                'message' => $exception->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\State  $state
     * @return StateResource
     */
    public function show(State $state)
    {
        return StateResource::make($state);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param EventPut $request
     * @param  \App\Models\State $state
     * @return StateResource
     */
    public function update(EventPut $request, State $state)
    {
        try{
            $state->update($request->all());
            return StateResource::make($state);
        }catch (\Exception $exception){
            return response()->json([
                'error' => true,
                'message' => $exception->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\State  $state
     * @return \Illuminate\Http\Response
     */
    public function destroy(State $state)
    {
        try {
            $state->delete();

            return response()->json([
                'error' => false,
                'message' => 'Estado deletado com sucesso.'
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'error' => true,
                'message' => $exception->getMessage()
            ]);
        }
    }
}
