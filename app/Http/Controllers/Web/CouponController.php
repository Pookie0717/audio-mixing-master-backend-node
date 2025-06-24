<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Http\JsonResponse;
use App\Models\Coupon;
use App\Models\Service;
use App\Models\UserWallet;
use App\Models\UserCoupon;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


class CouponController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function verify(Request $request, $code)
    {
        try {
            
            
            
            if (strpos($code, 'gift-') !== false) {
                // If the code contains 'gift-', perform gift card specific logic
            
                // Find the gift card by the code
                $giftCard = UserWallet::where('promocode', $code)->first();
                if (empty($giftCard)) {
                    throw new Exception('Invalid Gift Card Code', 200);
                }
            
                return response()->json([
                    'coupon' => $giftCard,
                    'type' => 'gift'
                ], 200);
            }

            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            // Find the coupon by code
            $data = Coupon::where('code', $code)->first();
            if (empty($data)) {throw new Exception('Invalid Coupon', 200);}

            // Check if the coupon is active
            if (!$data->is_active) {throw new Exception('Invalid Coupon', 200);}

            // Check if the coupon has reached its maximum uses
            if ($data->max_uses && $data->uses >= $data->max_uses) {throw new Exception('Invalid Coupon', 200);}

            // Check if the coupon has expired
            $currentDate = Carbon::now()->format('Y-m-d');
            //if ($data->end_date < $currentDate) {throw new Exception('Coupon has been expired', 200);}

            // Check if the user has already used the coupon
            $verify_user = UserCoupon::where('user_id', $this->user->id)->where('coupon_code', $code)->first();
            if (!empty($verify_user)) {throw new Exception('Can not reuse coupon', 200);}
            
            // product check
    
            // Check for intersection
            $matchingProductIds = [];
            
            if ($data->coupon_type != 0) {
                // Get the coupon's product IDs and decode them
                $dataProductIdsString = $data->product_ids;
                $dataProductIds = json_decode($dataProductIdsString, true);
    
                // If needed, fetch additional product IDs from the Service model
                
                foreach ($dataProductIds as $parentId) {
                    $serviceProducts = Service::where('parent_id', $parentId)->pluck('id')->toArray();

                    // Merge fetched service product IDs with the coupon's product IDs
                    $dataProductIds = array_merge($dataProductIds, $serviceProducts);
                }
                
    
                // Check for matching product IDs in the request
                foreach ($request->product_ids as $item) {
                    if (in_array($item, $dataProductIds)) {
                        $matchingProductIds[] = $item;
                    }
                }
    
                if (empty($matchingProductIds)) {
                    throw new Exception('No matching product IDs found', 200);
                }
            }
            return response()->json([
                'coupon' => $data,
                'matched_product_ids' => $matchingProductIds,
                'type' => 'coupon'
            ], 200);
        } catch (Exception $e) {
            return response()->json(['error'=> $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}
