<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\Article;
use App\Models\Form;
use App\Models\Event;
use Illuminate\Support\Facades\Http;

class WelcomeController extends Controller
{
    public function dashboard(){
        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
            'isMember' => Auth()->user() ? Auth()->user()->member : false,
            'isOrganizer' => Auth()->user() ? Auth()->user()->hasRole('organizer') : false,
            'articles' => Article::recents(),
            'forms' => Form::recents(),
            'events' => Event::recents(),
            'welcomeMessage'=>Article::where('category_code','WELCOME')->where('organization_id',0)->first()
        ]);
    }
    public function getRss(){

        $rssUrl = 'https://govinfohub.gcs.gov.mo/api/rss/n/zh-hant/5';

        try {
            // 使用 Laravel HTTP 客戶端獲取 RSS 內容
            $response = Http::withoutVerifying()->get($rssUrl)
                ;
            
            if (!$response->successful()) {
                return response()->json([
                    'error' => '無法獲取 RSS 內容',
                    'status' => $response->status()
                ], 502);
            }

            // 返回 XML 內容（讓前端解析）
            return response($response->body(), 200)
                  ->header('Content-Type', 'application/xml');

        } catch (\Exception $e) {
            
            return response()->json([
                'error' => '處理 RSS 請求時發生錯誤',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
