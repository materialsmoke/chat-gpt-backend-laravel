<?php

use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\AuthApiController;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:api'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', [AuthApiController::class, 'login']);
Route::post('register', [AuthApiController::class, 'register']);
Route::middleware(['auth:api'])->group(function(){
    Route::resource('chats', ChatController::class);    
});

Route::get('curl', function(){
    $url = request('url');
    $client = HttpClient::create();
    $browser = new HttpBrowser($client);
    $crawler = $browser->request('GET', $url);

    // Filter out unnecessary elements
    $crawler->filterXpath('//script | //style | //meta | //header | //footer | //nav')->each(function ($crawler) {
        foreach ($crawler as $node) {
            $node->parentNode->removeChild($node);
        }
    });

    $content = $crawler->filter('body')->text();

    return response()->json([
        'content' => $content
    ]);
});
