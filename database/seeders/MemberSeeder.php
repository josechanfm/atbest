<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Member;
use App\Models\User;

class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Member::factory()->count(100)->create();
        $member=Member::find(1);
        $user=User::where('email','master@example.com')->first();
        $member->email=$user->email;
        $member->user_id=$user->id;
        $member->save();

        $member=Member::find(2);
        $user=User::where('email','admin@example.com')->first();
        $member->email=$user->email;
        $member->user_id=$user->id;
        $member->save();

        $member=Member::find(3);
        $user=User::where('email','organizer@example.com')->first();
        $member->email=$user->email;
        $member->user_id=$user->id;
        $member->save();

        $member=Member::find(4);
        $user=User::where('email','guardian@example.com')->first();
        $member->email=$user->email;
        $member->user_id=$user->id;
        $member->save();

        $member=Member::find(5);
        $user=User::where('email','member1@example.com')->first();
        $member->email=$user->email;
        $member->user_id=$user->id;
        $member->save();

        $member=Member::find(6);
        $user=User::where('email','member2@example.com')->first();
        $member->email=$user->email;
        $member->user_id=$user->id;
        $member->save();

        $member=Member::find(7);
        $user=User::where('email','member3@example.com')->first();
        $member->email=$user->email;
        $member->user_id=$user->id;
        $member->save();

        Member::find(11)->update(['user_id'=>5,'display_name'=>'Ricky','family_name'=>'唐','given_name'=>'偉杰','email'=>'rickytong@example.com']);
        Member::find(12)->update(['user_id'=>6,'display_name'=>'發哥','family_name'=>'胡','given_name'=>'鴻發']);
        Member::find(13)->update(['user_id'=>7,'display_name'=>'堅哥','family_name'=>'羅','given_name'=>'志堅']);
    }
}

/*
master@example.com
admin@example.com
organizer@example.com
guardian@example.com
member1@example.com
member2@example.com
member3@example.com
*/
