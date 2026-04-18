<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class JobStatusController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate(['cache_key' => ['required', 'string']]);

        $result = Cache::get($request->cache_key);

        if (! $result) {
            return response()->json(['status' => 'processing', 'data' => null]);
        }

        return response()->json(['status' => 'done', 'data' => $result]);
    }
}
