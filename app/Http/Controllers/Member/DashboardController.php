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
        $members = Auth()->user()->members;
        if ($members->count()==0) {
            return Inertia::render('Error', [
                'message' => "You are not a register member."
            ]);
        }
        foreach($members as $member){
            $member->organization;
        }
        if(empty(session('member'))){
            session(['member'=>$members[0]]);
        }
        // dd(session('member')->organization);
        // dd(Feature::whereBelongsTo(session('member')->organization)->orderBy('sequence')->limit(4)->get());
        return Inertia::render('Member/Dashboard', [
            'currentMember'=>session('member'),
            'members' => $members,
            //'features'=>Article::whereBelongsTo(session('member')->organization)->where('category_code','FEATURE')->orderBy('sequence')->limit(4)->get(),
            'features'=>Feature::whereBelongsTo(session('member')->organization)->orderBy('sequence')->limit(4)->get(),
            'articles' => Article::whereBelongsTo(session('member')->organization)->where('category_code','NEWS')->orderBy('sequence','DESC')->get(),
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
