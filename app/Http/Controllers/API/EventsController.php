<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\EventPost;
use App\Http\Requests\EventPut;
use App\Models\Event;
use App\Http\Resources\Event as EventResource;

class EventsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return EventResource::collection(Event::paginate(request()->per_page?: 10));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param EventPost $request
     * @return EventResource
     */
    public function store(EventPost $request)
    {
        try{
            return EventResource::make(Event::create($request->all()));
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
     * @param  \App\Models\Event  $event
     * @return EventResource
     */
    public function show(Event $event)
    {
        return EventResource::make($event);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param EventPut $request
     * @param  \App\Models\Event $event
     * @return EventResource
     */
    public function update(EventPut $request, Event $event)
    {
        try{
            $city->update($request->all());
            return EventResource::make($event);
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
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function destroy(Event $event)
    {
        try {
            $event->delete();

            return response()->json([
                'error' => false,
                'message' => 'Evento deletado com sucesso.'
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'error' => true,
                'message' => $exception->getMessage()
            ]);
        }
    }
}
