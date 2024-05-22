<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GoogleController extends Controller
{
    //
    public function getAccessToken()
    {
        $client_id = env('GOOGLE_CLIENT_ID');
        $client_secret = env('GOOGLE_CLIENT_SECRET');
        $refresh_token = env('GOOGLE_REFRESH_TOKEN');

        $data = [
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'refresh_token' => $refresh_token,
            'grant_type' => 'refresh_token',
        ];

        try {
            $response = Http::asForm()->post('https://accounts.google.com/o/oauth2/token', $data);
            if ($response->successful()) {
                $accessToken = $response->json()['access_token'];
                return $accessToken;
            } else {
                return response()->json($response->json(), $response->status());
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function readAll(Request $request)
    {
        $accessToken = $this->getAccessToken();
        try {
            $response = Http::withToken($accessToken)->get('https://gmail.googleapis.com/gmail/v1/users/me/messages');
            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                return response()->json($response->json(), $response->status());
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function listThreads()
    {
        $credentials = [
            'client_id' => env('GOOGLE_CLIENT_ID'),
            'client_secret' => env('GOOGLE_CLIENT_SECRET'),
            'refresh_token' => env('GOOGLE_REFRESH_TOKEN'),
            'type' => 'authorized_user'
        ];

        $authJson = json_encode($credentials);
        $auth = Http::withBody($authJson, 'json');
        
        $response = Http::withToken(
            $auth->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'refresh_token',
            ])
            ->json()
        )
        ->get('https://www.googleapis.com/gmail/v1/users/me/threads');

        if ($response->failed()) {
            \Log::error('Failed to list Gmail threads: ' . $response->body());
            return;
        }

        $threads = $response->json('threads');

        if (empty($threads)) {
            echo "No threads found.";
            return;
        }

        echo "Threads: " . json_encode($threads, JSON_PRETTY_PRINT);
    }
}
