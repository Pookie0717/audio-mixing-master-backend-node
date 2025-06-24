<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\GiftOrder;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderHasService;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\User\OrderStatusMail;
use Illuminate\Support\Facades\Auth;
use App\Models\order_item;
use App\Models\OrderFile;
use App\Models\Revision;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderController extends Controller
{
    protected $user;
    protected $adminurl;
    protected $userurl;

    public function __construct()
    {
        $this->user = Auth::user();
        $this->adminurl = env('REACT_BACKEND_URL', 'https://fallback-url.com');
        $this->userurl = env('REACT_FRONTEND_URL', 'https://fallback-url.com');
    }
    
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->query('per_page', 10);
            
            // Get paginated orders for the authenticated user
            $orders = Order::where('user_id', $this->user->id)
                            ->orderBy('id', 'desc')
                            ->paginate($perPage);
        
            if ($orders->isEmpty()) {
                throw new Exception('No data found', 404);
            }
    
            // Add custom notification data to each order
            $orders->getCollection()->transform(function ($order) {
                $order->notify = $order->checkNotification(); // Use model method for notification logic
                return $order;
            });
    
            return response()->json($orders, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
     * Display a listing of the resource.
     */


    /**
     * Display the specified resource.
     */

    
    // this is new
    public function show(String $id): JsonResponse
    {
        try {
            // Retrieve the order by ID
            $order = Order::find($id);
    
            if (!$order) {
                throw new Exception('No order found', 404);
            }
    
            // Retrieve order items associated with the order
            $orderItems = order_item::where('order_id', $order->id)->get();
            
            $serviceIds = $orderItems->pluck('service_id')->unique();

            // Check if any service has category_id == 15
            $hasGiftcard = Service::whereIn('id', $serviceIds)
                                  ->where('category_id', 15)
                                  ->exists();
    
            // Set is_giftcard
            $is_giftcard = $hasGiftcard ? 1 : 0;
            
            
    
            // Check if order items are empty
            if ($orderItems->isEmpty()) {
                throw new Exception('No order items found for this order', 404);
            }
            $revision = Revision::where('order_id',$id)->get();
            // Prepare the data to be returned
            $data = [
                'order' => $order,
                'order_items' => $orderItems,
                'revision' => $revision->count() > 0 ? $revision : null,
                'is_giftcard'=>$is_giftcard
            ];
            
            
    
            return response()->json($data, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
    
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make(
                $request->all(),
                [
                    'gift_order_id' => 'nullable|exists:gift_orders,id',
                    'gift_amount' => 'nullable|numeric|required_with:gift_order_id',
                    'user_name' => 'required|max:255',
                    'user_email' => 'required|max:255',
                    'user_phone' => 'required|max:255',
                    'services_amount' => 'required|numeric',
                    'total_amount' => 'required|numeric',
                    'payment_method' => 'nullable|max:255',
                    'transaction_id' => 'nullable|max:255',
                    'transaction_card_name' => 'nullable|max:255',
                    'transaction_card_number' => 'nullable|max:255',
                    'transaction_cvc' => 'nullable|max:255',
                    'transaction_expiry_year' => 'nullable|date_format:Y',
                    'transaction_expiry_month' => 'nullable|date_format:m',
                ],
                [
                    'gift_order_id.exists' => 'Invalid Gift.',

                    'gift_amount.required_with' => 'Gift amount required.',
                    'gift_amount.numeric' => 'Gift amount numeric.',

                    'user_name.required' => 'Name required.',
                    'user_name.max' => 'Name must be less then 255 characters.',

                    'user_email.required' => 'Email required.',
                    'user_email.max' => 'Email must be less then 255 characters.',

                    'user_phone.required' => 'Phone required.',
                    'user_phone.max' => 'Phone must be less then 255 characters.',

                    'services_amount.required' => 'Services amount required.',
                    'services_amount.numeric' => 'Services amount numeric.',

                    'total_amount.required' => 'Total amount required.',
                    'total_amount.numeric' => 'Total amount numeric.',

                    'payment_method.required' => 'Payment method required.',
                    'payment_method.max' => 'Payment method must be less then 255 characters.',

                    'transaction_id.required' => 'Transaction id required.',
                    'transaction_id.max' => 'Transaction id must be less then 255 characters.',

                    'transaction_card_name.required' => 'Card name required.',
                    'transaction_card_name.max' => 'Card name must be less then 255 characters.',

                    'transaction_card_number.required' => 'Card number required.',
                    'transaction_card_number.max' => 'Card number must be less then 255 characters.',

                    'transaction_cvc.required' => 'Cvc required.',
                    'transaction_cvc.max' => 'Cvc must be less then 255 characters.',

                    'transaction_expiry_year.required' => 'Expiry year required.',
                    'transaction_expiry_year.date_format' => 'Expiry year invalid.',

                    'transaction_expiry_month.required' => 'Expiry month required.',
                    'transaction_expiry_month.date_format' => 'Expiry month invalid.',
                ]
            );

            if ($validator->fails()) throw new Exception($validator->errors()->first(), 400);

            $user = User::where('id', $this->user->id)->first();

            $data = new Order();

            $data->user_id = $this->user->id;

            if ($request->gift_order_id != null) {
                $data->gift_order_id = $request->gift_order_id;
                $data->gift_amount = $request->gift_amount;
            }

            $data->user_name = $request->user_name;
            $data->user_email = $request->user_email;
            $data->user_phone = $request->user_phone;
            $data->services_amount = $request->services_amount;
            $data->total_amount = $request->total_amount;
            $data->payment_method = $request->payment_method;
            $data->transaction_id = $request->transaction_id;
            $data->transaction_card_name = $request->transaction_card_name;
            $data->transaction_card_number = $request->transaction_card_number;
            $data->transaction_cvc = $request->transaction_cvc;
            $data->transaction_expiry_year = $request->transaction_expiry_year;
            $data->transaction_expiry_month = $request->transaction_expiry_month;
            $data->save();

            foreach ($request->services as $service) {
                $serviceModal = Service::where('id', $service['id'])->first();

                $serviceData = new OrderHasService();
                $serviceData->order_id = $data->id;
                $serviceData->service_id = $serviceModal['id'];
                $serviceData->image = $serviceModal->image;
                $serviceData->name = $serviceModal->name;
                $serviceData->service_type = $service['type'];
                $serviceData->qty = $service['qty'];
                $serviceData->price = $service['price'];
                $serviceData->total_price = $service['total_price'];
                $serviceData->save();
            }

            if ($request->gift_order_id != null) {
                $gift = GiftOrder::find($request->gift_order_id)->first();
                $gift->balance = $gift->balance - $request->gift_amount;
                $gift->save();
            }

            DB::commit();
            return response()->json($data, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function orderUpdateStatus(Request $request, String $id)
    {
        // return response()->json(['url'=> $this->userurl . 'order/' . $id]);
        
        
        try {
            $data = order::find($id);
            if (empty($data)) {
                throw new Exception('No data found', 404);  // Correct HTTP status code for not found
            }
            $user = User::find($data->user_id);
            // status update mail
            $orderStatusMessage = '';
            if($request->order_status != $data->Order_status){
                
                // Determine the order status message
                
                switch ($request->order_status) {
                        case 0:
                            $orderStatusMessage = 'Pending';
                            break;
                        case 1:
                            $orderStatusMessage = 'In Process';
                            break;
                        case 2:
                            $orderStatusMessage = 'Delivered';
                            break;
                        case 3:
                            $orderStatusMessage = 'Canceled';
                            break;
                        default:
                            $orderStatusMessage = 'Unknown';
                            break;
                    }
                $orderItems = order_item::where('order_id',$id)->get();
                Mail::to($user->email)->send(new OrderStatusMail([
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'order_id' => $id,
                    'order_status' => $orderStatusMessage,
                    'url'=> $this->userurl . '/order/' . $id,
                    'message' => 'Your project is now ' . $orderStatusMessage . '! You can view the latest changes or additions in your panel',
                ]));
                
                // send to admin
                $admin = User::where('role','admin')->first();
                Mail::to($admin->email)->send(new OrderStatusMail([
                    'name' => $admin->first_name . ' ' . $admin->last_name,
                    'order_id' => $id,
                    'order_status' => $orderStatusMessage,
                    'url'=> $this->adminurl . 'order-detail/' . $id,
                    'message' => 'Order Status has been changed of this Order ID:',
                ]));
            }
            
            $data->Order_status = $request->order_status;

            $data->save();
            return response()->json($data->Order_status, 200);
        } catch (Exception $e) {
            $status = 500; // Default to server error
            if ($e->getCode() == 23000) {
                $status = 400; // Bad request, or consider 409 Conflict if it's due to a constraint violation
            } elseif ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                $status = 404; // Not found
            } elseif (is_numeric($e->getCode()) && $e->getCode() >= 100 && $e->getCode() <= 599) {
                $status = (int)$e->getCode(); // Only use the exception's code if it's a valid HTTP status
            }

            return response()->json(['error' => $e->getMessage()], $status);
        }
    }
    
    public function orderUpdateFile(Request $request, String $id)
    {
        // return response()->json(['url'=> $this->userurl . 'order/' . $id]);

        
        try {
            $data = order::find($id);
            if (empty($data)) {
                throw new Exception('No data found', 404);  // Correct HTTP status code for not found
            }
            $user = User::find($data->user_id);
            // status update mail
            $data_item = order_item::find($request->order_item_id);
            if (empty($data_item)) {
                throw new Exception('No data item found', 404);  // Correct HTTP status code for not found
            }
            $orderStatusMessage='';
            
            if ($request->hasFile('file')) {
                $data->Order_status = 2;
                switch ($data->Order_status) {
                    case 0:
                        $orderStatusMessage = 'Pending';
                        break;
                    case 1:
                        $orderStatusMessage = 'In Process';
                        break;
                    case 2:
                        $orderStatusMessage = 'Delivered';
                        break;
                    case 3:
                        $orderStatusMessage = 'Canceled';
                        break;
                    default:
                        $orderStatusMessage = 'Unknown';
                        break;
                }
                $filePaths = [];
            
                // Get existing files if they exist
                if (!empty($data_item->deliverable_files)) {
                    $existingFiles = json_decode($data_item->deliverable_files, true);
                    $filePaths = is_array($existingFiles) ? $existingFiles : [];
                }
            
                // Add new files
                foreach ($request->file('file') as $file) {
                    $file_name = $this->user->first_name . '-' . $this->user->last_name . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    if (!$file->move(public_path('order-files'), $file_name)) {
                        throw new Exception('File could not be saved.', 500);
                    }
            
                    $filePaths[] = 'order-files/' . $file_name;
                }
            
                $data_item->deliverable_files=json_encode($filePaths);
                $data_item->user_is_read = 0;
                Mail::to($user->email)->send(new OrderStatusMail([
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'order_id' => $id,
                    'order_status' => $orderStatusMessage,
                    'total' => $data->amount,
                    'url'=> $this->userurl . '/order/' . $id,
                    'message' => 'Your project is now ' . $orderStatusMessage . '! You can view the latest changes or additions in your panel',
                ]));
                
                // send to admin
                $admin = User::where('role','admin')->first();
                Mail::to($admin->email)->send(new OrderStatusMail([
                    'name' => $admin->first_name . ' ' . $admin->last_name,
                    'order_id' => $id,
                    'order_status' => $orderStatusMessage,
                    'url'=> $this->adminurl . 'order-detail/' . $id,
                    'message' => 'Your Order has been Delivered successfully. Now you can check the details by clicking the button blow:',
                ]));
            }

            $data->save();
            $data_item->save();
            return response()->json(["Order_status"=>$data->Order_status,"order_item"=>$data_item], 200);
        } catch (Exception $e) {
            $status = 500; // Default to server error
            if ($e->getCode() == 23000) {
                $status = 400; // Bad request, or consider 409 Conflict if it's due to a constraint violation
            } elseif ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                $status = 404; // Not found
            } elseif (is_numeric($e->getCode()) && $e->getCode() >= 100 && $e->getCode() <= 599) {
                $status = (int)$e->getCode(); // Only use the exception's code if it's a valid HTTP status
            }

            return response()->json(['error' => $e->getMessage()], $status);
        }
    }
    
    // orders by user_id
    public function userOrders()
    {
    try {
        $user_id = $this->user->id;
        // Retrieve the orders by user_id
        $orders = Order::where('user_id', $user_id)->get();
        
        if ($orders->isEmpty()) {
            throw new Exception('No order found', 404);
        }
        
        // Prepare the order details with associated order items
        $orderDetails = $orders->map(function ($order) {
            $orderItems = order_item::where('order_id', $order->id)->get();
            return [
                'order' => $order,
                'order_items' => $orderItems
            ];
        });
    
        return response()->json($orderDetails, 200);
    } catch (Exception $e) {
        // Return an error response if an exception occurs
        return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
    }
}
    
    
}
