<?php

/**
 * MICS source: app Http Controllers ReadinessController. See docs/file-reference.md for its full responsibility.
 */

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class ReadinessController extends Controller
{
    public function __invoke(): JsonResponse
    {
        try {
            DB::select('select 1');

            return response()->json(['status' => 'ready']);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json(['status' => 'unavailable'], 503);
        }
    }
}
