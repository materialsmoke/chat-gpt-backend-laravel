<?php

namespace App\Http\Controllers;

use OpenAI;
use App\Models\Chat;
use App\Models\Message;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreChatRequest;
use App\Http\Requests\UpdateChatRequest;

class ChatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(
            Auth::user()->chats()->orderBy('id', 'desc')->get()->toArray()            
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreChatRequest $request)
    {
        $chatId = $request->chat_id;
        $message = $request->message;
        // we can get the model from user
        $engine = 'gpt-3.5-turbo'; //$request->engine ?? 'gpt-3.5-turbo';

        $chat = Chat::find($chatId);
        if(! $chat){
            $chat = Auth::user()->chats()->orderBy('id', 'desc')->first();
            if(empty($chat) || count($chat->messages()->get()) != 0){
                $chat = Chat::create([
                    'user_id' => Auth::user()->id,
                    'engine' => $engine
                ]);
            }
            
            return response()->json(['id' => $chat->id], 201);
        }

        $message = Message::create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => $message,
        ]);

        $payload = Message::where('chat_id', $chat->id)->get(['role', 'content'])->toArray();

        $client = OpenAI::client(config('app.open_ai_api_key'));
        $response = $client->chat()->create([
            'model' => $chat->engine,
            'messages' => $payload,
        ]);

        $message = Message::create([
            'chat_id' => $chat->id,
            'role' => $response->choices[0]->message->role,
            'content' => $response->choices[0]->message->content,
            'prompt_tokens' => $response->usage->promptTokens,
            'completion_tokens' => $response->usage->completionTokens,
            'total_tokens' => $response->usage->totalTokens,
            'response_id' => $response->id,
            'response_object' => $response->object,
            'model' => $response->model,
        ]);

        return response()->json(['id' => $chat->id], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Chat $chat)
    {
        $messages = $chat->messages()->get(['role', 'content'])->toArray();

        return response()->json($messages);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Chat $chat)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateChatRequest $request, Chat $chat)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Chat $chat)
    {
        //
    }
}
