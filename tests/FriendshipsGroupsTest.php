<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;

/*
 * Test User Personal Friend Groups
 */
class FriendshipsGroupsTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function user_can_add_a_friend_to_a_group()
    {

        $sender = factory(\App\User::class)->create();
        $recipient = factory(\App\User::class)->create();

        $sender->befriend($recipient);
        $recipient->acceptFriendRequest($sender);

        $this->assertTrue((boolean) $recipient->groupFriend($sender, 'acquaintances'));
        $this->assertTrue((boolean) $sender->groupFriend($recipient, 'family'));

        // it only adds a friend to a group once
        $this->assertFalse((boolean) $sender->groupFriend($recipient, 'family'));

        // expect that users have been attached to specified groups
        $this->assertCount(1, $sender->getFriends(0, 'family'));
        $this->assertCount(1, $recipient->getFriends(0, 'acquaintances'));

        $this->assertEquals($recipient->id, $sender->getFriends(0, 'family')->first()->id);
        $this->assertEquals($sender->id, $recipient->getFriends(0, 'acquaintances')->first()->id);

    }

    /** @test */
    public function user_cannot_add_a_non_friend_to_a_group()
    {
        $sender = factory(\App\Models\User::class)->create();
        $stranger = factory(\App\Models\User::class)->create();

        $this->assertFalse((boolean) $sender->groupFriend($stranger, 'family'));
        $this->assertCount(0, $sender->getFriends(0, 'family'));
    }

    /** @test */
    public function user_can_remove_a_friend_from_group()
    {
        $sender = factory(\App\Models\User::class)->create();
        $recipient = factory(\App\Models\User::class)->create();

        $sender->befriend($recipient);
        $recipient->acceptFriendRequest($sender);

        $recipient->groupFriend($sender, 'acquaintances');
        $recipient->groupFriend($sender, 'family');

        $this->assertEquals(1, $recipient->ungroupFriend($sender, 'acquaintances'));

        $this->assertCount(0, $sender->getFriends(0, 'acquaintances'));

        // expect that friend has been removed from acquaintances but not family
        $this->assertCount(0, $recipient->getFriends(0, 'acquaintances'));
        $this->assertCount(1, $recipient->getFriends(0, 'family'));
    }

    /** @test */
    public function user_cannot_remove_a_non_existing_friend_from_group()
    {
        $sender = factory(\App\Models\User::class)->create();
        $recipient = factory(\App\Models\User::class)->create();
        $recipient2 = factory(\App\Models\User::class)->create();

        $sender->befriend($recipient);

        $this->assertEquals(0, $recipient->ungroupFriend($sender, 'acquaintances'));
        $this->assertEquals(0, $recipient2->ungroupFriend($sender, 'acquaintances'));
    }

    // /** @test */
    // public function user_can_remove_a_friend_from_all_groups()
    // {
    //     $sender = factory(\App\Models\User::class)->create();
    //     $recipient = factory(\App\Models\User::class)->create();

    //     $sender->befriend($recipient);
    //     $recipient->acceptFriendRequest($sender);

    //     $sender->groupFriend($recipient, 'family');
    //     $sender->groupFriend($recipient, 'acquaintances');

    //     $sender->ungroupFriend($recipient);

    //     $this->assertCount(0, $sender->getFriends(0, 'family'));
    //     $this->assertCount(0, $sender->getFriends(0, 'acquaintances'));
    // }

    /** @test */
    public function it_returns_friends_of_a_group()
    {
        $sender = factory(\App\Models\User::class)->create();
        $recipients = factory(\App\Models\User::class, 10)->create();

        foreach ($recipients as $key => $recipient) {

            $sender->befriend($recipient);
            $recipient->acceptFriendRequest($sender);

            if ($key % 2 === 0) {
                $sender->groupFriend($recipient, 'family');
            }

        }

        $this->assertCount(5, $sender->getFriends(0, 'family'));
        $this->assertCount(10, $sender->getFriends());
    }

    /** @test */
    public function it_returns_all_user_friendships_by_group()
    {
        $sender = factory(\App\Models\User::class)->create();
        $recipients = factory(\App\Models\User::class, 5)->create();

        foreach ($recipients as $key => $recipient) {

            $sender->befriend($recipient);

            if ($key < 4) {

                $recipient->acceptFriendRequest($sender);
                if ($key < 3) {
                    $sender->groupFriend($recipient, 'acquaintances');
                } else {
                    $sender->groupFriend($recipient, 'family');
                }

            } else {
                $recipient->denyFriendRequest($sender);
            }

        }
        //Assertions

        $this->assertCount(3, $sender->getAllFriendships('acquaintances'));
        $this->assertCount(1, $sender->getAllFriendships('family'));
        $this->assertCount(0, $sender->getAllFriendships('close-friends'));
        $this->assertCount(0, $sender->getAllFriendships('whatever'));
    }

    /** @test */
    public function it_returns_accepted_user_friendships_by_group()
    {
        $sender = factory(\App\Models\User::class)->create();
        $recipients = factory(\App\Models\User::class, 4)->create();

        foreach ($recipients as $recipient) {
            $sender->befriend($recipient);
        }

        $recipients[0]->acceptFriendRequest($sender);
        $recipients[1]->acceptFriendRequest($sender);
        $recipients[2]->denyFriendRequest($sender);

        $sender->groupFriend($recipients[0], 'family');
        $sender->groupFriend($recipients[1], 'family');

        $this->assertCount(2, $sender->getAcceptedFriendships('family'));

    }

    /** @test */
    public function it_returns_accepted_user_friendships_number_by_group()
    {
        $sender = factory(\App\Models\User::class)->create();
        $recipients = factory(\App\Models\User::class, 20)->create()->chunk(5);

        foreach ($recipients->shift() as $recipient) {
            $sender->befriend($recipient);
            $recipient->acceptFriendRequest($sender);
            $sender->groupFriend($recipient, 'acquaintances');
        }

        //Assertions

        $this->assertEquals(5, $sender->getFriendsCount('acquaintances'));
        $this->assertEquals(0, $sender->getFriendsCount('family'));
        $this->assertEquals(0, $recipient->getFriendsCount('acquaintances'));
        $this->assertEquals(0, $recipient->getFriendsCount('family'));
    }

    /** @test */
    public function it_returns_user_friends_by_group_per_page()
    {
        $sender = factory(\App\Models\User::class)->create();
        $recipients = factory(\App\Models\User::class, 6)->create();

        foreach ($recipients as $recipient) {
            $sender->befriend($recipient);
        }

        $recipients[0]->acceptFriendRequest($sender);
        $recipients[1]->acceptFriendRequest($sender);

        $recipients[2]->denyFriendRequest($sender);

        $recipients[3]->acceptFriendRequest($sender);
        $recipients[4]->acceptFriendRequest($sender);

        $sender->groupFriend($recipients[0], 'acquaintances');
        $sender->groupFriend($recipients[1], 'acquaintances');
        $sender->groupFriend($recipients[3], 'acquaintances');
        $sender->groupFriend($recipients[4], 'acquaintances');

        $sender->groupFriend($recipients[0], 'close-friends');
        $sender->groupFriend($recipients[3], 'close-friends');

        $sender->groupFriend($recipients[4], 'family');

        //Assertions

        $this->assertCount(2, $sender->getFriends(2, 'acquaintances'));
        $this->assertCount(4, $sender->getFriends('', 'acquaintances'));
        $this->assertCount(4, $sender->getFriends(10, 'acquaintances'));

        $this->assertCount(2, $sender->getFriends('', 'close-friends'));
        $this->assertCount(1, $sender->getFriends(1, 'close-friends'));

        $this->assertCount(1, $sender->getFriends('', 'family'));

        $this->containsOnlyInstancesOf(\App\User::class, $sender->getFriends(0, 'acquaintances'));
    }

}
