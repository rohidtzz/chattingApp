<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use GuzzleHttp\Exception\RequestException;
use Auth;


class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request,$receiver_id,$sender_id)
    {
        $validatedData = $request->validate([
            'room' => 'nullable|string|max:100',
            'limit' => 'integer|min:1|max:100'
        ]);

        $messages = Message::inRoom($validatedData['room'] ?? null)
        ->where(function ($query) use ($receiver_id, $sender_id) {
            $query->where('receiver_id', $receiver_id)
                ->where('sender_id', $sender_id)
                ->orWhere(function ($query) use ($receiver_id, $sender_id) {
                    $query->where('receiver_id', $sender_id)
                            ->where('sender_id', $receiver_id);
                });
        })
        ->orderBy('created_at', 'asc')
        ->get();



        return response()->json([
            'status' => 'success',
            'messages' => $messages
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request,$id)
    {
        try {
            // Validate incoming request
            $validatedData = $request->validate([
                'sender_id' => 'required|uuid',
                'username' => 'required|string|max:255',
                'message' => 'required|string|max:1000',
                'room' => 'nullable|string|max:100'
            ]);

            // Create chat message in database
            $chatMessage = Message::create([
                'id' => Str::uuid()->toString(),
                'sender_id' => $validatedData['sender_id'],
                'receiver_id' => $id,
                'username' => $validatedData['username'],
                'message' => $validatedData['message'],
                'room' => $validatedData['room'] ?? null
            ]);

            // Prepare payload for WebSocket
            $payload = [
                'id' => $chatMessage->id,
                'username' => $chatMessage->username,
                'message' => $chatMessage->message,
                'sender_id' => $chatMessage->sender_id,
                'receiver_id' => $chatMessage->receiver_id,
                'room' => $chatMessage->room,
                'timestamp' => $chatMessage->created_at,
                'type' => 'private_message'
            ];

            // Send to WebSocket service
            $client = new Client([
                'timeout' => 5.0,
                'connect_timeout' => 3.0
            ]);

            $response = $client->post('http://localhost:3000/send-message', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'json' => $payload
            ]);

            // Log the message
            Log::info("Message stored and sent", [
                'message_id' => $chatMessage->id,
                'username' => $chatMessage->username
            ]);


            return response()->json([
                'status' => 'success',
                'message' => 'Message sent successfully',
                'data' => $payload
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (RequestException $e) {
            Log::error('WebSocket message send failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send message',
                'details' => $e->getMessage()
            ], 500);

        } catch (\Exception $e) {
            Log::error('Unexpected error in message store', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
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
        //
    }
}
