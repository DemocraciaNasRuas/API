<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddressPost;
use App\Http\Requests\AddressPut;
use App\Models\Address;
use App\Http\Resources\Address as AddressResource;

class AddressesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return AddressResource::collection(Address::paginate(request()->per_page?: 10));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param AddressPost $request
     * @return AddressResource
     */
    public function store(AddressPost $request)
    {
        try{
            return AddressResource::make(Address::create($request->all()));
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
     * @param  \App\Models\Address  $address
     * @return AddressResource
     */
    public function show(Address $address)
    {
        return AddressResource::make($address);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param AddressPut $request
     * @param  \App\Models\Address $address
     * @return AddressResource
     */
    public function update(AddressPut $request, Address $address)
    {
        try{
            $address->update($request->all());
            return AddressResource::make($address);
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
     * @param  \App\Models\Address  $address
     * @return \Illuminate\Http\Response
     */
    public function destroy(Address $address)
    {
        try {
            $address->delete();

            return response()->json([
                'error' => false,
                'message' => 'Endereço deletado com sucesso.'
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'error' => true,
                'message' => $exception->getMessage()
            ]);
        }
    }
}
