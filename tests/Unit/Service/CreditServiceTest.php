<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Service\CreditService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class CreditServiceTest extends TestCase
{
    private CreditService $creditService;
    private $em;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->creditService = new CreditService($this->em);
    }

    // -------------------- HAS ENOUGH CREDIT --------------------

    public function testHasEnoughCreditReturnsTrue(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getCredit')->willReturn(100.0);

        $this->assertTrue($this->creditService->hasEnoughCredit($user, 50.0));
    }

    public function testHasEnoughCreditReturnsFalse(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getCredit')->willReturn(30.0);

        $this->assertFalse($this->creditService->hasEnoughCredit($user, 50.0));
    }

    // -------------------- DEDUCT CREDIT --------------------

    public function testDeductCreditSuccess(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getCredit')->willReturn(100.0);
        $user->expects($this->once())
            ->method('setCredit')
            ->with(70.0); // 100 - 30

        $this->em->expects($this->once())->method('persist')->with($user);
        $this->em->expects($this->once())->method('flush');

        $this->creditService->deductCredit($user, 30.0);
    }

    public function testDeductCreditThrowsExceptionWhenInsufficient(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getCredit')->willReturn(20.0);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Crédit insuffisant pour effectuer cette opération.');

        $this->creditService->deductCredit($user, 50.0);
    }

    // -------------------- ADD CREDIT --------------------

    public function testAddCredit(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getCredit')->willReturn(50.0);
        $user->expects($this->once())
            ->method('setCredit')
            ->with(80.0); // 50 + 30

        $this->em->expects($this->once())->method('persist')->with($user);
        $this->em->expects($this->once())->method('flush');

        $this->creditService->addCredit($user, 30.0);
    }
}
