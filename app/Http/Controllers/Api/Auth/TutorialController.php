<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TutorialController extends Controller
{
    /** GET /me/tutorial — returns the list of seen route keys for the current user. */
    public function index(Request $request): JsonResponse
    {
        $seen = $request->user()->tutorial_seen ?? [];

        return response()->json(['data' => $seen]);
    }

    /** POST /me/tutorial — mark a route as seen. Body: { route: string } */
    public function markSeen(Request $request): JsonResponse
    {
        $request->validate(['route' => 'required|string|max:200']);

        $user = $request->user();
        $seen = $user->tutorial_seen ?? [];

        $route = $request->input('route');
        if (! in_array($route, $seen, true)) {
            $seen[] = $route;
            $user->update(['tutorial_seen' => $seen]);
        }

        return response()->json(['data' => $seen]);
    }

    /** DELETE /me/tutorial — reset all tutorial progress for the current user. */
    public function reset(Request $request): JsonResponse
    {
        $request->user()->update(['tutorial_seen' => []]);

        return response()->json(['data' => []]);
    }
}
