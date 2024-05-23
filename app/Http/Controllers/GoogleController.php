<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Google\Client;
use App\Models\ThreadHistory;
use Google\Service\Gmail;
use Carbon\Carbon;

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
        $client_id = env('GOOGLE_CLIENT_ID');
        $client_secret = env('GOOGLE_CLIENT_SECRET');
        $refresh_token = env('GOOGLE_REFRESH_TOKEN');

        // Set up the Google Client
        $client = new Client();
        $client->setClientId($client_id);
        $client->setClientSecret($client_secret);
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');
        $client->refreshToken($refresh_token);

        $gmail = new Gmail($client);
        try {
            $response = $gmail->users_threads->listUsersThreads('me');
            $threads = $response->getThreads();
            if (empty($threads)) {
                return response()->json(['message' => 'No threads found.']);
            }
            
            return response()->json(['threads' => $threads]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
    public function listSearch($searchItem = 'in:inbox is:unread category:primary')
    {
        $client_id = env('GOOGLE_CLIENT_ID');
        $client_secret = env('GOOGLE_CLIENT_SECRET');
        $refresh_token = env('GOOGLE_REFRESH_TOKEN');
        $currentTimestamp = Carbon::now();

        // Set up the Google Client
        $client = new Client();
        $client->setClientId($client_id);
        $client->setClientSecret($client_secret);
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');
        $client->refreshToken($refresh_token);

        $gmail = new Gmail($client);
        try {
            $response = $gmail->users_threads->listUsersThreads('me',['q' => $searchItem]);
            $threads = $response->getThreads();
            if (empty($threads)) {
                return response()->json(['message' => 'No threads found.']);
            }
            $existingThreadIds = ThreadHistory::latest('id')->take(20)->pluck('threadId')->toArray();
            $newThreads = [];
            foreach ($threads as $thread) {
                $threadId = $thread->getId();
                $historyId = $thread->getHistoryId();
                $snippet = $thread->getSnippet();
    
                // Check if the thread is already in the database
                if (!in_array($threadId, $existingThreadIds)) {
                    // If it's a new thread, add to the newThreads array
                    $newThreads[] = [
                        'threadId' => $threadId,
                        'historyId' => $historyId,
                        'snippet' => $snippet,
                        'created_at'=> $currentTimestamp,
                        'updated_at'=> $currentTimestamp
                    ];
                }
            }
            usort($newThreads, function ($a, $b) {
                return $a['historyId'] <=> $b['historyId'];
            });
            if (!empty($newThreads)) {
                info("New messages in inbox.");
                info($newThreads);
                ThreadHistory::insert($newThreads);
            } else {
                info("No new messages found.");
            }
            return response()->json(['threads' => $threads]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }

    
    }
}
