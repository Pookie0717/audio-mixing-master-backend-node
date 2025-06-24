<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Faq;
use Exception;
use Illuminate\Http\JsonResponse;

class FaqController extends Controller
{
    public function FaqList(): JsonResponse
    {
        try {
            $data = Faq::get();

            if ($data->isEmpty()) throw new Exception('No data found', 200);
            return response()->json($data, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}
