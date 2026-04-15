<?php

namespace App\Tests\Unit;

use App\Entity\ResetPasswordRequest;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class ResetPasswordRequestTest extends TestCase {

    private User $user;
    private ResetPasswordRequest $request;

    public function setUp(): void
    {
        $this->user = new User();
        $this->request = new ResetPasswordRequest(
            $this->user,
            new \DateTimeImmutable('+ 1 hour'),
            'my_selector',
            'my_hash_token'
        );
    }

    public function testGetUserReturnsTheExpectedUser(): void
    {
        $this->assertSame($this->user, $this->request->getUser());
    }
}
