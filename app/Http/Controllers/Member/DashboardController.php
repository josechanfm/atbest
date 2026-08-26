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
        $user = auth()->user();

        // 1. Admin redirect
        if ($user && $user->hasRole('admin')) {
            return redirect()->intended('/admin');
        }

        // 2. Check if user has a member record
        if (empty($user->member)) {
            return Inertia::render('Error', [
                'message' => 'You are not a registered member.'
            ]);
        }

        // 3. Store member & organization in session if not already set
        if (!session()->has('member')) {
            session()->put([
                'member' => $user->member,
                'organization' => $user->member->organization,
            ]);
        }

        // 4. Load necessary relations
        $member = $user->member->load('organization');
        $org = session('member')->organization;

        // 5. Fetch data
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

        // 6. Render dashboard
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
