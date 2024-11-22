<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    public function sendSuccess($data, $message, $status = 200)
    {
        return response()->json([
            'message'   => $message,
            'data'      => $data,
        ], $status);
    }

    public function sendError($data, $message, $status = 200)
    {
        return response()->json([
            'message'   => $message,
            'data'      => [],
        ], $status);
    }
}
