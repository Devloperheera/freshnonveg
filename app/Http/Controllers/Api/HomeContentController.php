<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\HomeContentApi;
use Illuminate\Http\JsonResponse;

class HomeContentController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => HomeContentApi::homePayload(),
        ]);
    }
}
