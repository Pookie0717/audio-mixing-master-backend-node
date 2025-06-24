<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OrdersExport;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ExcelController extends Controller
{
    public function exportOrders(Request $request) {
        // Validate the date range input
        $validator = Validator::make(
            $request->all(),
            [
                'start_date' => 'required',
                'end_date' => 'required'
            ]
        );

        // If validation fails, throw an exception
        if ($validator->fails()) {
            throw new Exception($validator->errors()->first(), 400);
        }

        // Extract and format the start and end dates
        $date_range = $request->date_range;
        $start_date = Carbon::createFromFormat('d/m/Y', $request->start_date)->startOfDay();
        $end_date = Carbon::createFromFormat('d/m/Y', $request->end_date)->endOfDay();

        // Fetch orders within the date range
        // $orders = Order::join('users', 'orders.user_id', '=', 'users.id')
        //     ->whereBetween('orders.created_at', [$start_date, $end_date])
        //     ->get(['orders.*', 'users.first_name', 'users.last_name', 'users.email']);
        
        
    $orders = Order::with(['items', 'user'])
    ->whereBetween('created_at', [$start_date, $end_date])
    ->get();

        // If no orders are found, return an error response
        if ($orders->isEmpty()) {
            return response()->json(['error' => 'No orders found in the provided date range.'], 404);
        }

        // Generate the Excel file using the OrdersExport class
        $export = new OrdersExport($orders);

        // Define the filename based on the date range
        $filename = 'orders-report-' . $start_date->format('d-m-Y') . '-to-' . $end_date->format('d-m-Y') . '.xlsx';

        // Return the Excel file as a download
        return Excel::download($export, $filename);
    }
}
