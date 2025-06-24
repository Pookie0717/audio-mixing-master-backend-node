<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Gift;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Http\JsonResponse;
use App\Models\GiftOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MyGiftController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $perPage = $request->query('per_page', 10);

            $data = GiftOrder::with('gift')
                ->where('user_id', $user->id)
                ->paginate($perPage);

            if (empty($data)) return response()->json(['error' => 'No data found.'], 404);
            return response()->json($data, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function verify(String $code): JsonResponse
    {
        try {
            $data = GiftOrder::select(
                'gift_orders.id',
                'gift_orders.balance',
            )
                ->where('promo_code', $code)
                ->first();

            if (empty($data)) return response()->json(['error' => 'No data found.'], 404);

            return response()->json($data, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
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
                    'gift_id' => 'required|exists:gifts,id',
                    'purchase_price' => 'required|numeric',
                    'payment_method' => 'required|max:255',
                    'transaction_id' => 'required|max:255',
                    'transaction_card_name' => 'required|max:255',
                    'transaction_card_number' => 'required|max:255',
                    'transaction_cvc' => 'required|max:255',
                    'transaction_expiry_year' => 'required|date_format:Y',
                    'transaction_expiry_month' => 'required|date_format:m',
                ],
                [
                    'gift_id.exists' => 'Invalid Gift.',

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

            $data = new GiftOrder;
            $data->user_id = $this->user->id;
            $data->gift_id = $request->gift_id;
            $data->user_name = $request->user_name;
            $data->user_email = $request->user_email;
            $data->user_phone = $request->user_phone;
            $data->balance = $request->purchase_price;
            $data->promo_code = $this->generateUniquePromoCode();
            $data->purchase_price = $request->purchase_price;
            $data->payment_method = $request->payment_method;
            $data->transaction_id = $request->transaction_id;
            $data->transaction_card_name = $request->transaction_card_name;
            $data->transaction_card_number = $request->transaction_card_number;
            $data->transaction_cvc = $request->transaction_cvc;
            $data->transaction_expiry_year = $request->transaction_expiry_year;
            $data->transaction_expiry_month = $request->transaction_expiry_month;
            $data->save();

            DB::commit();
            return response()->json($data, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate unique promo code
     */
    public function generateUniquePromoCode(): string
    {
        $prefix = 'AMM';
        $uniqueCode = false;

        do {
            $randomString = strtoupper(Str::random(7));
            $promoCode = $prefix . $randomString;
            $existingPromoCode = GiftOrder::where('promo_code', $promoCode)->first();
            if (!$existingPromoCode) $uniqueCode = true;
        } while (!$uniqueCode);

        return $promoCode;
    }
}
