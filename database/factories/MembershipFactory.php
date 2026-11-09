<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Membership\Models\Member;
use Modules\Membership\Models\Membership;

/**
 * @extends Factory<Membership>
 */
class MembershipFactory extends Factory
{
    protected $model = Membership::class;

    public function definition(): array
    {
        return [
            'member_id' => Member::factory(),
            'membership_number' => 'KTA-'.Str::upper(Str::random(10)),
            'type' => 'basic',
            'status' => 'active',
            'started_at' => now(),
            'ended_at' => now()->addYear(),
        ];
    }
}
