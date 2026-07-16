<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EndorsementController extends Controller
{
    public function toggle(): \Illuminate\Http\JsonResponse
    {
        return response()->json(['message' => 'Endorsements not implemented'], 501);
    }
}


