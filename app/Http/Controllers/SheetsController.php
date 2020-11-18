<?php

namespace App\Http\Controllers;

use App\Sheets;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SheetsController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $response = Sheets::resourcePlanner();
            if (is_string($response)) {
                $values = [
                    'data' => $response,
                    'statusCode' => 418,
                    'success' => false
                ];
            } else {
                $values = [
                    'data' => $response,
                    'statusCode' => 200,
                    'success' => true
                ];
            }
        } catch (\Exception $e) {
            $values = [
                'data' => $e->getMessage(),
                'statusCode' => $e->getCode(),
                'success' => false
            ];
        }
        return response()->json($values);
    }
}
