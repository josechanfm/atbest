<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use App\Models\Member;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
  
/*
  [
  {
    "year": 2014,
    "abbr_en": "MEDRA",
    "abbr_zh": "",
    "abbr_pt": "",
    "name_en": "Macao Education Development and Research Association",
    "name_zh": "澳門教育發展研究學會",
    "name_pt": "",
    "address": "",
    "phone": "",
    "href": "atbest.net",
    "email": "",
    "president": "--",
    "card_style": "card_01"
  },
  {
    "year": 2017,
    "abbr_en": "STEAM",
    "abbr_zh": "",
    "abbr_pt": "",
    "name_en": "Science and Technology Education Association of Macau",
    "name_zh": "澳門科技教育協會",
    "name_pt": "",
    "address": "",
    "phone": "",
    "href": "atbest.net",
    "email": "",
    "president": "--",
    "card_style": "card_01"
  },
  {
    "year": 2014,
    "abbr_en": "MPMPA",
    "abbr_zh": "",
    "abbr_pt": "",
    "name_en": "Macao Project Management Professional Association",
    "name_zh": "澳門項目管理師協會",
    "name_pt": "",
    "address": "澳門羅理基博士大馬路600-E號, 第一國際商業中心P22-08",
    "phone": "",
    "href": "mpmpa.org.mo",
    "email": "info@mpmpa.org.mo",
    "president": "--",
    "card_style": "card_01"
  }
]
*/



        $jsonPath = database_path('seeders/data/organizations.json');
        
        if (!File::exists($jsonPath)) {
            $this->command->error("JSON file not found at: {$jsonPath}");
            return;
        }

        $jsonContent = File::get($jsonPath);
        // Remove UTF-8 BOM if present
        $jsonContent = preg_replace('/^\xEF\xBB\xBF/', '', $jsonContent);

        $organizations = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error('JSON error: ' . json_last_error_msg());
            $this->command->error('First 200 chars of file:');
            $this->command->error(substr($jsonContent, 0, 200));
            return;
        }

        $count = 0;
        foreach ($organizations as $org) {
            Organization::create($org);
            $count++;
        }


        $organization=Organization::where('abbr_en','ATEC')->first();
        $member=Member::where('email','organizer@example.com')->first();
        $member->organization_id=$organization->id;
        $member->save();
        // $organization->users()->attach($member->user);
        

        $organization=Organization::where('abbr_en','ATEC')->first();
        $member=Member::where('email','member1@example.com')->first();
        $member->organization_id=$organization->id;
        $member->is_organizer=true;
        $member->save();
        $organization=Organization::where('abbr_en','ATEC')->first();
        $member=Member::where('email','member2@example.com')->first();
        $member->organization_id=$organization->id;
        $member->is_organizer=false;
        $member->save();
        $organization=Organization::where('abbr_en','ATEC')->first();
        $member=Member::where('email','member3@example.com')->first();
        $member->organization_id=$organization->id;
        $member->is_organizer=false;
        $member->save();
        //Member::whereBetween('id',[11,13])->update(['organization_id'=>$organization->id]);
        // $member=Member::find(1);
        // $organization->members()->attach($member);
        // $member=Member::find(2);
        // $organization->members()->attach($member);
        // $member=Member::find(3);
        // $organization->members()->attach($member);


    }
}

