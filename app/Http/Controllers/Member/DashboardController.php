<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Inertia\Inertia;
use App\Models\Config;
use App\Models\Feature;
use App\Models\Member;
use App\Models\Organization;

class DashboardController extends Controller
{
    public function index()
    {
        if (empty(auth()->user()->member)) {
            return Inertia::render('Error', [
                'message' => "You are not a register member."
            ]);
        }
        if(empty(session('member'))){
            session(['member'=>auth()->user()->member]);
            session(['organization'=>auth()->user()->member->organiation]);
        }
        // dd(auth()->user()->member->organization);
        return Inertia::render('Member/Dashboard', [
            'member'=> auth()->user()->member->load('organization'),
            'members' => auth()->user()->members,
            'features' => Feature::whereBelongsTo(session('member')->organization)->orderBy('sequence')->limit(4)->get(),
            'articles' => Article::whereBelongsTo(session('member')->organization)->where('category_code','NEWS')->where('published',true)->orderBy('sequence','DESC')->get(),
            'cardStyle' => Config::item('card_styles')[session('member')->organization->card_style],
        ]);
    }
    public function getQrcode()
    {
        $userId = auth()->user()->id;
        $memberId = auth()->user()->id;
        $organizationId = session('member')->organization->id;
        $time = time();
        $text = $organizationId . ',' . $memberId . ',' . $time;
        return $text . ',' . hash('crc32', $text);

    }
}
