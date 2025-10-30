<?php

namespace App\Http\Controllers;

use App\Services\GlobalApiServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GlobalApiController extends Controller
{
    public function fetchPriceGroup(GlobalApiServices $globalApi)
    {
        $response = $globalApi->FetchPriceGroup();

        if (!$response['success']) {
            return response()->json([
                'success' => false,
                'message' => $response['message'] ?? 'Failed to fetch Price Group data.'
            ], 500);
        }

        return response()->json($response['data']);
    }

    public function accountCodeList(GlobalApiServices $globalApi)
    {
        $response = $globalApi->AccoundCodeList();

        if (!$response['success']) {
            return response()->json([
                'success' => false,
                'message' => $response['message'] ?? 'Failed to fetch Account code list'
            ], 500);
        }

        return response()->json($response['data']);
    }

    public function customerCodeList(GlobalApiServices $globalApi)
    {
        $response = $globalApi->CustomerCodeList();
        if (!$response['success']) {
            return response()->json([
                'success' => false,
                'message' => $response['message'] ?? 'Failed to fetch customer code list'
            ]);
        }
        return response()->json($response['data']);
    }
}
