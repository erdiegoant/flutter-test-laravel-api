<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EventController extends Controller
{
    public function index(Request $request) : JsonResponse {
        $events = Event::whereUserId($request->user()->id)->get();

        return response()->json(compact('events'));
    }

    public function store(Request $request) : JsonResponse {
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
        ]);

        $event = Event::create($request->all());

        return response()->json(compact('event'), Response::HTTP_CREATED);
    }

    public function show(int $id) : JsonResponse {
        $event = Event::find($id);

        return response()->json(compact('event'));
    }

    public function update(Request $request, int $id) : JsonResponse {
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string|max:255',
        ]);

        $event = Event::find($id)->update($request->all());

        return response()->json(compact('event'));
    }

    public function destroy(Request $request, int $id) : JsonResponse {
        $event = Event::find($id);

        if ($event->user_id !== $request->user()->id) {
            return response()->json([], Response::HTTP_FORBIDDEN);
        }

        try {
            $event->delete();
            return response()->json([]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
