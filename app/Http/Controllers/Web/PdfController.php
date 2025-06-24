<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PdfController extends Controller
{
    public function generatePDFd(Request $request) {
        // return response()->json($request);
        $validator = Validator::make(
        $request->all(),
            [
                'date_range' => 'required|array|size:2',
                'date_range.*' => 'required|date_format:d/m/Y'
            ]
        );
        if ($validator->fails()) throw new Exception($validator->errors()->first(), 400);
        $date_range = $request->date_range;

        if (count($date_range) != 2) {
            return response()->json(['error' => 'Invalid date range provided.'], 400);
        }

        
        $start_date = Carbon::createFromFormat('d/m/Y', $date_range[0])->startOfDay();
        $end_date = Carbon::createFromFormat('d/m/Y', $date_range[1])->endOfDay();
        // $orders = Order::whereBetween('created_at', [$start_date, $end_date])->get();

        $orders = Order::join('users', 'orders.user_id', '=', 'users.id')
                   ->whereBetween('orders.created_at', [$start_date, $end_date])
                   ->get(['orders.*', 'users.first_name', 'users.last_name', 'users.email']);
        
        if ($orders->isEmpty()) {
            return response()->json(['error' => 'No orders found in the provided date range.'], 404);
        }

        // return response()->json($orders);
        $pdf = PDF::loadView('pdf', ['orders' => $orders]);
        // return $pdf->download($filename, [], true);
        // $filename = 'order-details-' . $start_date->format('d-m-Y') . '-to-' . $end_date->format('d-m-Y') . '.pdf';
        // $path = 'pdfs/pdf_' . time() . '_' . $filename;
    
        
        // $pdf->save(public_path($path));
    
        // return response()->json(['link' => asset($path)]);  
        $filename = 'order-details-' . $start_date->format('d-m-Y') . '-to-' . $end_date->format('d-m-Y') . '.pdf';
    
        // Directly return the PDF as a response
        return $pdf->download($filename);
    }
    
    public function generatePDF(Request $request) {
    // $validator = Validator::make(
    //     $request->all(),
    //     [
    //         'date_range' => 'required|array|size:2',
    //         'date_range.*' => 'required|date_format:d/m/Y'
    //     ]
    // );

    // if ($validator->fails()) {
    //     throw new Exception($validator->errors()->first(), 400);
    // }

    //$date_range = $request->date_range;

    $start_date = Carbon::createFromFormat('d/m/Y', $request->start_date)->startOfDay();
    $end_date = Carbon::createFromFormat('d/m/Y', $request->end_date)->endOfDay();

    $orders = Order::join('users', 'orders.user_id', '=', 'users.id')
        ->whereBetween('orders.created_at', [$start_date, $end_date])
        ->get(['orders.*', 'users.first_name', 'users.last_name', 'users.email']);
    
    if ($orders->isEmpty()) {
        return response()->json(['error' => 'No orders found in the provided date range.'], 404);
    }

    $pdf = PDF::loadView('pdf', ['orders' => $orders]);
    $filename = 'order-details-' . $start_date->format('d-m-Y') . '-to-' . $end_date->format('d-m-Y') . '.pdf';

    // Directly return the PDF as a response
    return $pdf->download($filename);
}

}

