<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ContactLeadGeneration;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


class ContactLeadGenerateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 10);
            $data = ContactLeadGeneration::orderBy('id', 'desc')->paginate($perPage);

            if (empty($data)) throw new Exception('No data found', 404);
            return response()->json($data, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $data = ContactLeadGeneration::where('id', $id)->first();
            if (empty($data)) throw new Exception('No data found', 404);

            return response()->json($data, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required|max:255',
                    'email' => 'required|string|lowercase|email|max:255',
                    'subject' => 'required|max:255',
                    'message' => 'required|max:255'
                ],
                [
                    'name.required' => 'Name required.',
                    'name.max' => 'Name maximum 255 characters.',

                    'email.required' => 'Email required',
                    'email.email' => 'Invalid Email',
                    'email.max' => 'Email must be less than 255 characters.',
                    'email.lowercase' => 'Email must be lowercase.',

                    'subject.required' => 'Subject required.',
                    'subject.max' => 'Subject maximum 255 characters.',

                    'message.required' => 'Message required.',
                    'message.max' => 'Message maximum 255 characters.'
                ]
            );

            if ($validator->fails()) {
                throw new Exception($validator->errors()->first(), 400);
            }

            $data = new ContactLeadGeneration();
            $data->name         = $request->name;
            $data->email        = $request->email;
            $data->subject      = $request->subject;
            $data->message      = $request->message;
            $data->save();

            DB::commit();
            return response()->json(['message' => 'success', 'contact_leads' => $data], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $data = ContactLeadGeneration::find($id);
            if (empty($data)) throw new Exception('No data found', 404);
            $data->delete();

            DB::commit();

            return response()->json('Deleted', 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}
