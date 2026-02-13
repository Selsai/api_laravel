<?php

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function test_uses_professional_email_returns_true_with_professional_email(): void
    {
        $user = new User();
        $user->email = 'john@entreprise.com';

        $this->assertTrue($user->usesProfessionalEmail());
    }

    public function test_uses_professional_email_returns_false_with_gmail(): void
    {
        $user = new User();
        $user->email = 'john@gmail.com';

        $this->assertFalse($user->usesProfessionalEmail());
    }
}