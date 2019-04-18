<?php

namespace Tests;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\TestCase;

class FriendshipsEventsTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->sender = factory(\App\Models\User::class)->create();
        $this->recipient = factory(\App\Models\User::class)->create();

    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    /** @test */
    public function friend_request_is_sent()
    {
        Event::shouldReceive('fire')->once()->withArgs(['friendships.sent']);
        // $this->expectsEvents('friendships.sent');
        $this->sender->befriend($this->recipient);
        // Event::assertDispatched('friendships.sent', 1);

    }

    /** @test */
    public function friend_request_is_accepted()
    {
        $this->sender->befriend($this->recipient);
        Event::shouldReceive('dispatch')->once()->withArgs(['friendships.accepted', Mockery::any()]);
        // Event::assertDispatched('friendships.accepted', 2);

        $this->recipient->acceptFriendRequest($this->sender);
    }

    // /** @test */
    // public function friend_request_is_denied()
    // {
    //     $this->sender->befriend($this->recipient);
    //     // Event::shouldReceive('fire')->once()->withArgs(['friendships.denied', Mockery::any()]);

    //     $this->recipient->denyFriendRequest($this->sender);
    // }

    // /** @test */
    // public function friend_is_blocked()
    // {
    //     $this->sender->befriend($this->recipient);
    //     $this->recipient->acceptFriendRequest($this->sender);
    //     // Event::shouldReceive('fire')->once()->withArgs(['friendships.blocked', Mockery::any()]);

    //     $this->recipient->blockFriend($this->sender);
    // }

    // /** @test */
    // public function friend_is_unblocked()
    // {
    //     $this->sender->befriend($this->recipient);
    //     $this->recipient->acceptFriendRequest($this->sender);
    //     $this->recipient->blockFriend($this->sender);
    //     // Event::shouldReceive('fire')->once()->withArgs(['friendships.unblocked', Mockery::any()]);

    //     $this->recipient->unblockFriend($this->sender);
    // }

    // /** @test */
    // public function friendship_is_cancelled()
    // {
    //     $this->sender->befriend($this->recipient);
    //     $this->recipient->acceptFriendRequest($this->sender);
    //     // Event::shouldReceive('fire')->once()->withArgs(['friendships.cancelled', Mockery::any()]);

    //     $this->recipient->unfriend($this->sender);
    // }
}