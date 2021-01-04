<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Response;
use Symfony\Component\HttpFoundation\Response as Statuses;

class EventController extends Controller
{
    public function index(Request $request) : JsonResponse
    {
        $query = Event::query();

        collect($request->only('title'))->each(function ($key, $value) use ($query) {
            $query->where($key, $value);
        });

        $events = $query->get();

        return Response::json(compact('events'));
    }

    public function store(Request $request) : JsonResponse
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string|max:255',
        ]);

        $event = Event::create([
            'title' => $request->get('title'),
            'description' => $request->get('description'),
            'user_id' => $request->user()->id,
        ])->load(['user:id,name,email']);

        return Response::json(compact('event'), Statuses::HTTP_CREATED);
    }

    public function show(int $id) : JsonResponse
    {
        $event = Event::with(['comments', 'comments.user:id,name,email'])->find($id);

        return Response::json(compact('event'));
    }

    public function update(Request $request, int $id) : JsonResponse
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string|max:255',
        ]);

        $event = Event::find($id)->update($request->all());

        return Response::json(compact('event'));
    }

    public function destroy(Request $request, int $id) : JsonResponse
    {
        $event = Event::find($id);

        if ($event->user_id !== $request->user()->id) {
            return Response::json([], Statuses::HTTP_FORBIDDEN);
        }

        try {
            $event->comments()->delete();
            $event->delete();

            return Response::json([]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return Response::json([], Statuses::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
