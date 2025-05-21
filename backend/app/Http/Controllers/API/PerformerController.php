<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class PerformerController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'message' => 'Добро пожаловать, исполнитель!',
        ]);
    }
}
