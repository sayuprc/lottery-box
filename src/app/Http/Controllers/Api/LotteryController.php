<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lottery\Application\UseCase\Lottery\LotteryInput;
use Lottery\Application\UseCase\Lottery\LotteryInputPort;

class LotteryController extends Controller
{
    public function handle(Request $request, LotteryInputPort $handler): JsonResponse
    {
        $request->validate([
            'boxName' => [
                'required',
                'string',
            ],
        ]);

        $result = $handler->handle(new LotteryInput($request->string('boxName')->toString()));

        if ($result->isErr()) {
            return response()->json(['error' => $result->unwrapErr()], options:JSON_UNESCAPED_UNICODE);
        }

        return response()->json(['winning' => $result->unwrap()->itemName->value], options: JSON_UNESCAPED_UNICODE);
    }
}
