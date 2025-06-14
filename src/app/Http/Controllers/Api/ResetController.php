<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lottery\Application\UseCase\Reset\ResetInput;
use Lottery\Application\UseCase\Reset\ResetInputPort;

class ResetController extends Controller
{
    public function handle(Request $request, ResetInputPort $handler): JsonResponse
    {
        $request->validate([
            'boxName' => [
                'required',
                'string',
            ],
        ]);

        $result = $handler->handle(new ResetInput($request->string('boxName')->toString()));

        if ($result->isErr()) {
            return response()->json(['error' => $result->unwrapErr()], options: JSON_UNESCAPED_UNICODE);
        }

        return response()->json(['reset' => 'success'], options: JSON_UNESCAPED_UNICODE);
    }
}
