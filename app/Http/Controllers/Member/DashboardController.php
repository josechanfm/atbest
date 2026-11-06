<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Config;
use App\Models\Feature;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user && $user->hasRole('admin')) {
            return redirect()->intended('/admin');
        }

        $member = $user->member;
        if (!$member) {
            return redirect()->route('guest.dashboard');
        }

        if (!session()->has('member')) {
            session()->put([
                'member' => $member,
                'organization' => $member->organization,
            ]);
        }

        $member->load('organization');
        $member->name_card_link = $member->nameCardLink();
        $org = session('member')->organization;

        $features = Feature::whereBelongsTo($org)
            ->orderBy('sequence')
            ->limit(4)
            ->get();

        $articles = Article::whereBelongsTo($org)
            ->where('category_code', 'NEWS')
            ->where('published', true)
            ->orderBy('sequence', 'DESC')
            ->get();

        $cardStyle = Config::item('card_styles')[$org->card_style] ?? 'card_01';

        return Inertia::render('Member/Dashboard', [
            'member'    => $member,
            'members'   => $user->members()->get(),
            'features'  => $features,
            'articles'  => $articles,
            'cardStyle' => $cardStyle,
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
