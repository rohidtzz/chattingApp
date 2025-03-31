<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

use Illuminate\Validation\ValidationException;
use GuzzleHttp\Exception\RequestException;
use Auth;

class UserController extends Controller
{
    public function getOnlineUser()
    {
        try {


            // Send to WebSocket service
            $client = new Client([
                'timeout' => 5.0,
                'connect_timeout' => 3.0
            ]);

            $response = $client->get('http://localhost:3000/online-users');

            // Log the message
            Log::info("Data stored and sent", [
                'data' => $response->getBody()->getContents()
            ]);

            $data = (string) $response->getBody();


            return response()->json([
                'status' => 'success',
                'message' => 'Data sent successfully',
                'data' => $data
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (RequestException $e) {
            Log::error('WebSocket data send failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send data',
                'details' => $e->getMessage()
            ], 500);

        } catch (\Exception $e) {
            Log::error('Unexpected error in data store', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
