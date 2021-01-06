<?php

namespace App\Http\Controllers;

use App\Models\EventComment;
use App\Rules\CommentThrottling;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Response;
use Symfony\Component\HttpFoundation\Response as Statuses;

class EventCommentController extends Controller
{
    public function store(Request $request) : JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'comment' => ['required', 'string', new CommentThrottling($user)],
            'event_id' => 'required|integer|exists:events,id',
        ]);

        $comment = EventComment::create([
            'comment' => $request->get('comment'),
            'event_id' => $request->get('event_id'),
            'user_id' => $user->id,
        ]);
        $comment->load(['user:id,name,email']);

        return response()->json(compact('comment'), Statuses::HTTP_CREATED);
    }

    public function destroy(Request $request, int $id) : JsonResponse
    {
        $comment = EventComment::with('event')->find($id);

        if (
            $request->user()->id !== $comment->user_id
            && $request->user()->id !== $comment->event->user_id
        ) {
            return Response::json([], Statuses::HTTP_FORBIDDEN);
        }

        try {
            $comment->delete();

            return Response::json([]);
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return Response::json([], Statuses::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
