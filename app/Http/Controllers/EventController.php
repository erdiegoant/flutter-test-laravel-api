<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Response;

class EventController extends Controller
{
    public function index(Request $request) : JsonResponse
    {
        $events = Event::filter($request->only(['title', 'description']))->get();

        return Response::json(compact('events'));
    }

    public function store(Request $request) : JsonResponse
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
        ]);

        $event = Event::create([
            'title' => $request->get('title'),
            'description' => $request->get('description'),
            'user_id' => $request->user()->id,
        ])->load(['user']);

        return Response::json(compact('event'), 201);
    }

    public function show(int $id) : JsonResponse
    {
        $event = Event::with(['comments'])->find($id);

        return Response::json(compact('event'));
    }

    public function update(Request $request, int $id) : JsonResponse
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
        ]);

        $event = Event::find($id)->update($request->all());

        return Response::json(compact('event'));
    }

    public function destroy(Request $request, int $id) : JsonResponse
    {
        $event = Event::find($id);

        if ($event->user_id !== $request->user()->id) {
            return Response::json([], 403);
        }

        try {
            $event->comments()->delete();
            $event->delete();

            return Response::json([]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return Response::json([], 500);
        }
    }
}
