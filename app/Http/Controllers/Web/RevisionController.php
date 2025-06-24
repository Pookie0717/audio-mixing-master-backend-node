<?php

namespace App\Http\Controllers\Web;

use Exception;
use App\Models\Revision;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\order_item;
use App\Models\User;
use App\Models\RevisionData;
use Illuminate\Support\Facades\Mail;
use App\Mail\Admin\AdminRevisionMail;
use App\Mail\User\RevisionRequestMail;
use App\Mail\User\RevisionSuccessMail;
use App\Mail\Admin\EngineerRevisionMail;

class RevisionController extends Controller
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
    
    public function store(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
    
            $validator = Validator::make(
                $request->all(),
                [
                    "order_id" => "required|numeric",
                    "service_id" => "required|numeric",
                    "message" => "required",
                ]
            );
            
            if ($validator->fails()) {
                throw new Exception($validator->errors()->first(), 400);
            }
            
            $order_item = order_item::where('service_id',$request->service_id)->where('order_id',$request->order_id)->get()->first();
            // return response()->json($order_item);
            
            $data = Revision::where('service_id', $request->service_id)
                ->where('order_id', $request->order_id);
               
            if($order_item->max_revision > 0){
                if($request->transaction_id != null || $request->transaction_id != ''){
                    $data = $data->where('transaction_id', $request->transaction_id)->first();
                    $data->message = $request->message;
                    $data->admin_is_read = 0;
                    $data->save();
                }
                else{
                    $data = new Revision();
                    $data->order_id = $request->order_id;
                    $data->service_id = $request->service_id;
                    $data->user_id = $this->user->id;
                    $data->message = $request->message;
                    $data->admin_is_read = 0;
                    $data->save();
                }
                $order_item->max_revision = $order_item->max_revision - 1;
                $order_item->save();
                DB::commit();
                 
                $user =  User::find($this->user->id);

                // sending mail to user
                Mail::to($user->email)->send(new RevisionSuccessMail([
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'order_id' => $request->order_id,                    
                    'service_id' => $request->service_id,  
                    'url'=> $this->userurl.'/order/'.$request->order_id,
                    'message' => 'Your Revision Request has been sent Successfully. Our Engineers are working on it:',
                ]));
                // sending mail to admin
                $admin =  User::where('role','admin')->first();
                Mail::to($admin->email)->send(new RevisionSuccessMail([
                    'name' => $admin->first_name . ' ' . $admin->last_name,
                    'order_id' => $request->order_id,                    
                    'service_id' => $request->service_id,
                    'url'=> $this->adminurl.'/order-detail/'.$request->order_id,
                    'message' => 'New Revision Request has been Arrived Successfully. All the Engineer has been notified:',
                ]));
                // sending mail to all engineers 
                $engineers = User::where('role', 'engineer')->get();
                foreach ($engineers as $engineer) {
                    Mail::to($engineer->email)->send(new RevisionSuccessMail([
                        'name' => $engineer->first_name . ' ' . $engineer->last_name,
                        'order_id' => $request->order_id,
                        'service_id' => $request->service_id,
                        'url'=> $this->adminurl.'/order-detail/'.$request->order_id,
                        'message' => 'New Revision Request has been Arrived Successfully. You can check by clicking the link below:',
                    ]));
                }
            }else{
                throw new Exception('max revision reached', 404);
            }
            
            
            $order = Order::find($request->order_id);
            $order->Order_status = 4;
            $order->save();
            $all_revision = Revision::where('order_id',$request->order_id)->get();
            
            return response()->json(['message' => "success",'Order_status' => 4,"max_count"=>$order_item->max_revision, "revision"=>$all_revision], 200);
        } catch (Exception $e) {
            // Ensure the status code is an integer and defaults to 500 if invalid
            $statusCode = $e->getCode() && is_numeric($e->getCode()) ? (int)$e->getCode() : 500;
            return response()->json(['error' => $e->getMessage()], $statusCode);
        }
    }
    
    public function upload(Request $request, String $id)
    {
        
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    "files" => "required|array|min:1",
                ],
                [
                    'files.required' =>'Files is required.',
                ]
            );
            
            if ($validator->fails()) {
                throw new Exception($validator->errors()->first(), 400);
            }
            $data = Revision::find($id);
            if (empty($data)) {
                throw new Exception('No data found', 404);  // Correct HTTP status code for not found
            }
            

            if ($request->hasFile('files')) {
                $filePaths = [];
            
                // Get existing files if they exist
                if (!empty($data->files)) {
                    $existingFiles = json_decode($data->files, true);
                    $filePaths = is_array($existingFiles) ? $existingFiles : [];
                }
            
                // Add new files
                foreach ($request->file('files') as $file) {
                    $file_name = 'revision_file_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    if (!$file->move(public_path('order-revision-files'), $file_name)) {
                        throw new Exception('File could not be saved.', 500);
                    }
            
                    $filePaths[] = 'order-revision-files/' . $file_name;
                }
                    
                $data->files=json_encode($filePaths);
            }
            $order = Order::find($data->order_id);
            $order->Order_status = 2;
            $order->save();
            $data->user_is_read = 0;
            $data->save();
            $user =  User::find($data->user_id);
            // sending mail to user
            Mail::to($user->email)->send(new RevisionSuccessMail([
                'name' => $user->first_name . ' ' . $user->last_name,
                'order_id' => $data->order_id,                    
                'service_id' => $data->service_id,  
                'url'=> $this->userurl.'/order/'.$data->order_id,
                'message' => 'Your Revision Fie has been Delivered Successfully:',
            ]));
            // sending mail to admin
            $admin =  User::where('role','admin')->first();
            Mail::to($admin->email)->send(new RevisionSuccessMail([
                'name' => $admin->first_name . ' ' . $admin->last_name,
                'order_id' => $data->order_id,                    
                'service_id' => $data->service_id,
                'url'=> $this->adminurl.'/order-detail/'.$data->order_id,
                'message' => 'New Revision file has been Arrived Successfully:',
            ]));
            // sending mail to all engineers 
            // $engineers = User::where('role', 'engineer')->get();
            // foreach ($engineers as $engineer) {
            //     Mail::to($engineer->email)->send(new RevisionSuccessMail([
            //         'name' => $engineer->first_name . ' ' . $engineer->last_name,
            //         'order_id' => $request->order_id,
            //         'service_id' => $request->service_id,
            //         'url'=> $this->adminurl.'/order-detail/'.$request->order_id,
            //         'message' => 'New Revision Request has been Arrived Successfully. You can check by clicking the link below:',
            //     ]));
            // }
                
            $all_revision = Revision::where('order_id',$data->order_id)->get();
            return response()->json(["revision"=>$all_revision, "order_status"=>2], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
//     public function upload(Request $request, String $id)
//     {
//     // Debugging request data
//     dd($request->all());
    
//     try {
//         // Validate request
//         $validator = Validator::make(
//             $request->all(),
//             [
//                 "files" => "required|array|min:1",
//             ],
//             [
//                 'files.required' => 'Files are required.',
//             ]
//         );
        
//         // If validation fails
//         if ($validator->fails()) {
//             throw new Exception($validator->errors()->first(), 400);
//         }
        
//         // Find the revision by ID
//         $data = Revision::find($id);
        
//         if (empty($data)) {
//             throw new Exception('No data found', 404);  // Correct HTTP status code for not found
//         }
        
//         // Handle file uploads
//         if ($request->hasFile('files')) {
//             $filePaths = [];
            
//             // Get existing files if they exist
//             if (!empty($data->files)) {
//                 $existingFiles = json_decode($data->files, true);
//                 $filePaths = is_array($existingFiles) ? $existingFiles : [];
//             }
            
//             // Add new files
//             foreach ($request->file('files') as $file) {
//                 $file_name = 'revision_file_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                
//                 if (!$file->move(public_path('order-revision-files'), $file_name)) {
//                     throw new Exception('File could not be saved.', 500);
//                 }
                
//                 $filePaths[] = 'order-revision-files/' . $file_name;
//             }
            
//             // Save file paths
//             $data->files = json_encode($filePaths);
//         }
        
//         // Update order status
//         $order = Order::find($data->order_id);
//         $order->Order_status = 2;
//         $order->save();
        
//         // Mark revision as unread
//         $data->user_is_read = 0;
//         $data->save();
        
//         // Send notification email to the user
//         $user = User::find($data->user_id);
//         Mail::to($user->email)->send(new RevisionSuccessMail([
//             'name' => $user->first_name . ' ' . $user->last_name,
//             'order_id' => $data->order_id,
//             'service_id' => $data->service_id,
//             'url' => $this->userurl . '/order/' . $data->order_id,
//             'message' => 'Your revision file has been delivered successfully.',
//         ]));
        
//         // Send notification email to the admin
//         $admin = User::where('role', 'admin')->first();
//         Mail::to($admin->email)->send(new RevisionSuccessMail([
//             'name' => $admin->first_name . ' ' . $admin->last_name,
//             'order_id' => $data->order_id,
//             'service_id' => $data->service_id,
//             'url' => $this->adminurl . '/order-detail/' . $data->order_id,
//             'message' => 'A new revision file has arrived successfully.',
//         ]));
        
//         // Get all revisions for this order
//         $all_revision = Revision::where('order_id', $data->order_id)->get();
        
//         // Return response
//         return response()->json([
//             "revision" => $all_revision,
//             "order_status" => 2
//         ], 200);
        
//     } catch (Exception $e) {
//         return response()->json(['error' => $e->getMessage()], 400);
//     }
// }

    
    public function getData(): JsonResponse
    {
        $data = RevisionData::get()->first();
        return response()->json($data,200);
    }
    
    public function flagAdmin(Request $request, String $id): JsonResponse
    {
        try {
            // Validate the incoming request
            $validator = Validator::make(
                $request->all(),
                [
                    "admin_is_read" => "required|boolean",
                    "order_item_id" =>"required|numeric"
                ],
                [
                    'admin_is_read.required' => 'Admin Is Read status is required.',
                    'admin_is_read.boolean' => 'Admin Is Read status must be true or false.',
                    
                    'order_item_id.required' => 'order item id is required.'
                ]
            );
    
            // Check if validation fails
            if ($validator->fails()) {
                throw new Exception($validator->errors()->first(), 400);
            }
    
            // Retrieve all revisions with the given order_id
            $order_item = order_item::where('order_id',$id)->where('id',$request->order_item_id)->first();
            $data = Revision::where('order_id',$id)->where('service_id',$order_item->service_id)->where('admin_is_read',0)->first();
    
            // Check if any revisions were found
            if (empty($data)) {
                throw new Exception('No data found', 404);
            }
    
            $data->admin_is_read = $request->admin_is_read;
            $data->save();
            
            $response = Revision::where('order_id',$id)->get();
            return response()->json($response, 200);
        } catch (Exception $e) {
            $statusCode = $e->getCode() === 404 ? 404 : 400;
            return response()->json(['error' => $e->getMessage()], $statusCode);
        }
    }

    
    public function flagUser(Request $request, String $id): JsonResponse
    {
        try{
            $validator = Validator::make(
                $request->all(),
                [
                "user_is_read" => "required|boolean",
                "order_item_id" => "required|numeric",
                "type" => "required|in:order,revision"
                ],
                [
                    'user_is_read.required' => 'User Is Read status is required.',
                    'user_is_read.boolean' => 'User Is Read status must be true or false.',
                    
                    'order_item_id.required' => 'Order Item ID is required.',
                    "order_item_id.numeric" => "Type must be numeric",
                    
                    'type.required' => 'Type is required.',
                    'type.in' => 'Type must be either order or revision.'
                ]
            );
            if ($validator->fails()) {
                throw new Exception($validator->errors()->first(), 400);
            }
            $data = null;
            $response = null;
            if ($request->type == 'order') {
                $data = order_item::where('order_id',$id)->where('id',$request->order_item_id)->first();
            } else if ($request->type == 'revision') {
                $order_item = order_item::where('order_id',$id)->where('id',$request->order_item_id)->first();
                $data = Revision::where('order_id',$id)->where('service_id',$order_item->service_id)->where('user_is_read',0)->first();
                
            }
    
            if (empty($data)) {
                throw new Exception('No data found', 404);
            }
    
            $data->user_is_read = $request->user_is_read;
            $data->save();
            if ($request->type == 'order') {
                $response = order_item::where('order_id',$id)->get();
            } else if ($request->type == 'revision') {
                $response = Revision::where('order_id',$id)->get();
            }
            return response()->json($response, 200);
        }catch(Exception $e){
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

}
