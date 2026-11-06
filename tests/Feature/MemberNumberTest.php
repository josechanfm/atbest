<?php

namespace Tests\Feature;

use App\Models\Member;
use Tests\TestCase;

class MemberNumberTest extends TestCase
{
    public function test_member_number_is_appended_to_serialized_member(): void
    {
        $member = new Member();
        $member->id = 42;
        $member->organization_id = 7;

        $this->assertSame('000700042', $member->member_number);
        $this->assertSame('000700042', $member->toArray()['member_number']);
    }
}
