<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\Article;

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
            'articles' => Article::publics(),
            'welcomeMessage'=>Article::where('category_code','WELCOME')->where('organization_id',0)->first()
        ]);
    }
}
