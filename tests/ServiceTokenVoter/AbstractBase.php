<?php

declare(strict_types=1);

namespace App\Tests\ServiceTokenVoter;

use App\Classes\ServiceTokenUserInterface;
use App\Classes\SessionUserInterface;
use App\Tests\TestCase;
use Mockery as m;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractBase extends TestCase
{
    protected Voter $voter;

    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->voter);
    }

    abstract public function supportsTypeProvider(): array;

    /**
     * @dataProvider supportsTypeProvider
     */
    public function testSupportType(string $className, bool $isSupported): void
    {
        $this->assertEquals($this->voter->supportsType($className), $isSupported);
    }

    abstract public function supportsAttributesProvider(): array;

    /**
     * @dataProvider supportsAttributesProvider
     */
    public function testSupportAttributes(string $attribute, bool $isSupported): void
    {
        $this->assertEquals($this->voter->supportsAttribute($attribute), $isSupported);
    }

    protected function createMockToken(
        ?UserInterface $tokenUser,
        ?array $writeableSchoolIds = null,
    ): TokenInterface {
        $mockToken = m::mock(TokenInterface::class);
        $mockToken
            ->shouldReceive('hasAttribute')
            ->with('writeable_schools')
            ->andReturn(true);
        $mockToken
            ->shouldReceive('getAttribute')
            ->with('writeable_schools')
            ->andReturn($writeableSchoolIds);
        $mockToken->shouldReceive('getUser')->andReturn($tokenUser);
        return $mockToken;
    }

    protected function createMockTokenWithServiceTokenUser(?array $writeableSchoolIds = null): TokenInterface
    {
        return $this->createMockToken(m::mock(ServiceTokenUserInterface::class), $writeableSchoolIds);
    }

    protected function createMockTokenWithoutServiceTokenUser(?array $writeableSchoolIds = null): TokenInterface
    {
        return $this->createMockToken(null, $writeableSchoolIds);
    }

    protected function createMockTokenWithSessionUser(?array $writeableSchoolIds = null): TokenInterface
    {
        return $this->createMockToken(m::mock(SessionUserInterface::class), $writeableSchoolIds);
    }
}
