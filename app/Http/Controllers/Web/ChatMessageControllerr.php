<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\chat;
use App\Models\message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatMessageControllerr extends Controller
{

    /**
     * Display a listing chatList.
     *
     * @return \Illuminate\Http\Response
     */
    public function chatList(Request $request)
    {
        $userAuth = $request->header('Authorization');
        // Explode the string by the pipe character
        $parts = explode('|', $userAuth);
    
        // Get the part after the pipe
        $afterPipe = isset($parts[1]) ? $parts[1] : null;
        $hashedToken = hash('sha256', $afterPipe);
        $user = DB::table('personal_access_tokens')->where('token', $hashedToken)->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not authenticated'], 401);
        }
    
        $userExisted = $user->tokenable_id;
        $perPage = $request->query('per_page', 10); // Default to 10 items per page, can be customized via query parameter
    
        try {
            $chatQuery = chat::query()
                ->join('users', 'chats.senderId', '=', 'users.id')
                ->join('users as ru', 'chats.receiverId', '=', 'ru.id')
                ->select(
                    'chats.id',
                    'chats.senderId',
                    'users.first_name as senderFirstName',
                    'users.last_name as senderLastName',
                    'users.email as senderEmail',
                    'ru.id as receiverId',
                    'ru.first_name as receiverFirstName',
                    'ru.last_name as receiverLastName',
                    'ru.email as receiverEmail'
                )
                ->where(function ($query) use ($userExisted) {
                    $query->where('senderId', '=', $userExisted)
                        ->orWhere('receiverId', '=', $userExisted);
                });
    
            // Additional filters based on 'chat_room'
            if ($request->input('chat_room') === 'all') {
                // No additional user-specific filter when 'all' is selected
            } else {
                if ($request->has('smart_searching')) {
                    $searching = $request->smart_searching;
                    $chatQuery->whereDoesntHave('messages')
                        ->where(function ($query) use ($searching) {
                            $query->where('senderId', 'LIKE', '%' . $searching . '%')
                                ->orWhereHas('sender', function ($subQuery) use ($searching) {
                                    $subQuery->where('first_name', 'LIKE', '%' . $searching . '%')
                                        ->orWhere('last_name', 'LIKE', '%' . $searching . '%')
                                        ->orWhere('email', 'LIKE', '%' . $searching . '%');
                                })
                                ->orWhereHas('receiver', function ($subQuery) use ($searching) {
                                    $subQuery->where('first_name', 'LIKE', '%' . $searching . '%')
                                        ->orWhere('last_name', 'LIKE', '%' . $searching . '%')
                                        ->orWhere('email', 'LIKE', '%' . $searching . '%');
                                });
                        });
                } else {
                    $chatQuery->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                            ->from('messages')
                            ->whereRaw('messages.chatId = chats.id');
                    });
                }
            }
    
            // Applying pagination to the chat query
            $chatListDetails = $chatQuery->orderBy('chats.id', 'asc')->paginate($perPage);
            // Filter the results based on whether $userExisted matches with senderId or receiverId
            $filteredChatList = [];
            foreach ($chatListDetails as $chat) {
                if ($userExisted != $chat->senderId) {
                    $filteredChatList[] = [
                        'id' => $chat->id,
                        'senderId' => $chat->senderId,
                        'senderFirstName' => $chat->senderFirstName,
                        'senderLastName' => $chat->sendertLatsName,
                        'senderEmail' => $chat->sendertEmail,
                    ];
                } elseif ($userExisted != $chat->receiverId) {
                    $filteredChatList[] = [
                        'id' => $chat->id,
                        'receiverId' => $chat->receiverId,
                        'receiverFirstName' => $chat->receiverFirstName,
                        'receiverLastName' => $chat->receiverLastName,
                        'receiverEmail' => $chat->receiverEmail,
                    ];
                }
            }

            return response()->json(['success' => true, 'chat_list' => $filteredChatList,  'pagination' => [
                'current_page' => $chatListDetails->currentPage(),
                'last_page' => $chatListDetails->lastPage(),
                'per_page' => $chatListDetails->perPage(),
                'total' => $chatListDetails->total()
            ]], 200);
        } catch (\Throwable $exception) {
            return response()->json(['success' => false, 'data' => [], 'message' => $exception->getMessage()], 500);
        }
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function messageSent(Request $request)
    {
        DB::beginTransaction();
        $request->validate([

            "chatId"        => "required",
            "message"       => "required",
            "messageType"   => "required",
            'image_url'     => 'nullable|url',
            'image_file' => 'nullable|file|mimetypes:image/jpeg,image/png,image/gif,audio/mpeg,audio/mp3,audio/wav,video/mp4,video/quicktime,video/x-msvideo',


        ]);

        try {

            $chatData = [

                "senderId"       => $request->senderId,
                "chatId"         => $request->chatId,
                "message"        => $request->message,
                "messageType"    => $request->messageType,
            ];

            $userAuth = $request->header('Authorization');
            // Explode the string by the pipe character
            $parts = explode('|', $userAuth);

            // Get the part after the pipe
            $afterPipe = isset($parts[1]) ? $parts[1] : null;
            $hashedToken = hash('sha256', $afterPipe);
            $user = DB::table('personal_access_tokens')->where('token', $hashedToken)->first();

            if ($user) {
                $chatData['senderId'] = $user->tokenable_id;
            }

            $image_path = null;
            if ($request->has('image_file')) {
                $image = $request->file('image_file');
                $image_name = 'Upload-Message-File' . time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('Upload-Message-File'), $image_name);
                $image_path = 'Upload-Message-File/' . $image_name;
            } elseif ($request->has('image_url')) {
                $image_path = $request->image_url;
            }
            $chatData['image'] = $image_path;
            $chatData['is_read'] = 0;
            $messageDetails = message::create($chatData);

            DB::commit();
            return response()->json(['success' => true, 'message' => "message sent successfully"], 200);
        } catch (\Throwable $exception) {
            return response()->json(['success' => false, 'data' => [], 'message' => $exception->getMessage()], 500);
        }
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function massageFetch(Request $request)
    {
        try {
            // Check if 'chatId' exists in the request
            if (!$request->has('chatId')) {
                return response()->json(['success' => false, 'message' => 'chatId is required'], 400);
            }

            $chatId = $request->chatId;
            $messageIds = $request->input('All', null); // This can be a comma-separated list of IDs
            $isRead = $request->input('is_read', null); // optional parameter

            // Update is_read if messageIds and is_read are provided
            if ($messageIds && $isRead !== null) {
                $messageIdArray = explode(',', $messageIds); // Convert string to array
                // Update all messages that match the ID array and chatId to the new is_read status
                message::whereIn('id', $messageIdArray)
                    ->where('chatId', $chatId)
                    ->update(['is_read' => $isRead]);
            }

            // Fetch messages for the chat after update
            $messageDetails = message::with('sender')
                ->where('chatId', $chatId)
                ->get();

            // Count unread messages in the chat
            $unreadCount = message::where('chatId', $chatId)->where('is_read', 0)->count();

            return response()->json([
                'success' => true,
                'fetch_message' => $messageDetails,
                'unread_messages' => $unreadCount
            ], 200);
        } catch (\Throwable $exception) {
            // Return an error if something goes wrong
            return response()->json(['success' => false, 'message' => $exception->getMessage()], 500);
        }
    }
}
