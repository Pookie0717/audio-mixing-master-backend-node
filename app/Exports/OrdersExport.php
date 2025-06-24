<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrdersExport implements FromCollection, WithHeadings, WithMapping
{
    protected $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    public function collection()
    {
        return $this->orders;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Transaction ID',
            'Username',
            'Email',
            'amount',
            'Service Names',
            'Order Type',
            'Payment Method',
            'Created At'
        ];
    }
    public function map($order): array
    {
        // Map the data in the order you want it to appear
        return [
            $order->id,
            $order->transaction_id,
            $order->user->first_name . ' ' . $order->user->last_name, // Access the user relationship
            $order->user->email,
            $order->amount,
            $order->orderItems->pluck('name')->implode(', '),
            $order->order_type,
            $order->payment_method,
            $order->created_at->format('d/m/Y'),
        ];
    }
}
