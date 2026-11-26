<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Forum;
use App\Models\ForumContent;
use Illuminate\Http\Request;

class ForumContentController extends Controller
{
    //

    public function destroy(ForumContent $content)
    {
        $content->delete();

        return redirect()->back();
    }   
}
