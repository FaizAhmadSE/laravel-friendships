<?php
use Hootlex\Friendships\Models\FriendshipGroup;
use Illuminate\Database\Seeder;

class FriendshipGroupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $groups = ['Acquaintances', 'Close Friends', 'Family'];
        foreach ($groups as $group) {
            FriendshipGroup::firstOrCreate(
                ['slug' => str_slug($group)],
                ['name' => $group]
            );
        }
    }
}
