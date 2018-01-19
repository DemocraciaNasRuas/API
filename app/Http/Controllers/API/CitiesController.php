<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\CityPost;
use App\Http\Requests\CityPut;
use App\Models\City;
use App\Http\Resources\City as CityResource;

class CitiesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return CityResource::collection(City::paginate(request()->per_page?: 10));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CityPost $request
     * @return CityResource
     */
    public function store(CityPost $request)
    {
        try{
            return CityResource::make(City::create($request->all()));
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
     * @param  \App\Models\City  $city
     * @return CityResource
     */
    public function show(City $city)
    {
        return CityResource::make($city);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param CityPut $request
     * @param  \App\Models\City $city
     * @return CityResource
     */
    public function update(CityPut $request, City $city)
    {
        try{
            $city->update($request->all());
            return CityResource::make($city);
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
     * @param  \App\Models\City  $city
     * @return \Illuminate\Http\Response
     */
    public function destroy(City $city)
    {
        try {
            $city->delete();

            return response()->json([
                'error' => false,
                'message' => 'Cidade deletada com sucesso.'
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'error' => true,
                'message' => $exception->getMessage()
            ]);
        }
    }
}
