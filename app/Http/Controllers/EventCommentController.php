<?php

namespace App\Http\Controllers;

use App\Models\EventComment;
use App\Rules\CommentThrottling;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EventCommentController extends Controller
{
    public function store(Request $request) : JsonResponse {
        $user = $request->user();

        $request->validate([
            'comment' => ['required', 'string', new CommentThrottling($user)],
            'event_id' => 'required|integer|exists:events,id',
        ]);

        $comment = EventComment::create([
            ...$request->all(),
            'user_id' => $user->id,
        ]);

        return response()->json(compact('comment'), Response::HTTP_CREATED);
    }

    public function destroy(Request $request, int $id) : JsonResponse {
        $comment = EventComment::with('event')->find($id);

        if (
            $request->user()->id !== $comment->user_id
            && $request->user()->id !== $comment->event->user_id
        ) {
            return response()->json([], Response::HTTP_FORBIDDEN);
        }

        try {
            $comment->delete();

            return response()->json([]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json([], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
