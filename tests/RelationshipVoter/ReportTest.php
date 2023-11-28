<?php

declare(strict_types=1);

namespace App\Tests\RelationshipVoter;

use App\Classes\VoterPermissions;
use App\Entity\ReportInterface;
use App\Entity\UserInterface;
use App\RelationshipVoter\Report as Voter;
use App\Service\SessionUserPermissionChecker;
use Mockery as m;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ReportTest extends AbstractBase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->permissionChecker = m::mock(SessionUserPermissionChecker::class);
        $this->voter = new Voter($this->permissionChecker);
    }

    public function testAllowsRootFullAccess()
    {
        $this->checkRootEntityAccess(m::mock(ReportInterface::class));
    }

    public function testCanView()
    {
        $token = $this->createMockTokenWithNonRootSessionUser();
        $user = $token->getUser();
        $entity = m::mock(ReportInterface::class);
        $entity->shouldReceive('getUser')->andReturn(m::mock(UserInterface::class));
        $user->shouldReceive('isTheUser')->andReturn(true);
        $response = $this->voter->vote($token, $entity, [VoterPermissions::VIEW]);
        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $response, "View allowed");
    }

    public function testCanEdit()
    {
        $token = $this->createMockTokenWithNonRootSessionUser();
        $user = $token->getUser();
        $entity = m::mock(ReportInterface::class);
        $entity->shouldReceive('getUser')->andReturn(m::mock(UserInterface::class));
        $user->shouldReceive('isTheUser')->andReturn(true);
        $response = $this->voter->vote($token, $entity, [VoterPermissions::EDIT]);
        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $response, "Edit allowed");
    }

    public function testCanNotEdit()
    {
        $token = $this->createMockTokenWithNonRootSessionUser();
        $user = $token->getUser();
        $entity = m::mock(ReportInterface::class);
        $entity->shouldReceive('getUser')->andReturn(m::mock(UserInterface::class));
        $user->shouldReceive('isTheUser')->andReturn(false);
        $response = $this->voter->vote($token, $entity, [VoterPermissions::EDIT]);
        $this->assertEquals(VoterInterface::ACCESS_DENIED, $response, "Edit denied");
    }

    public function testCanDelete()
    {
        $token = $this->createMockTokenWithNonRootSessionUser();
        $user = $token->getUser();
        $entity = m::mock(ReportInterface::class);
        $entity->shouldReceive('getUser')->andReturn(m::mock(UserInterface::class));
        $user->shouldReceive('isTheUser')->andReturn(true);
        $response = $this->voter->vote($token, $entity, [VoterPermissions::DELETE]);
        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $response, "Delete allowed");
    }

    public function testCanNotDelete()
    {
        $token = $this->createMockTokenWithNonRootSessionUser();
        $user = $token->getUser();
        $entity = m::mock(ReportInterface::class);
        $entity->shouldReceive('getUser')->andReturn(m::mock(UserInterface::class));
        $user->shouldReceive('isTheUser')->andReturn(false);
        $response = $this->voter->vote($token, $entity, [VoterPermissions::DELETE]);
        $this->assertEquals(VoterInterface::ACCESS_DENIED, $response, "Delete denied");
    }

    public function testCanCreate()
    {
        $token = $this->createMockTokenWithNonRootSessionUser();
        $user = $token->getUser();
        $entity = m::mock(ReportInterface::class);
        $entity->shouldReceive('getUser')->andReturn(m::mock(UserInterface::class));
        $user->shouldReceive('isTheUser')->andReturn(true);
        $response = $this->voter->vote($token, $entity, [VoterPermissions::CREATE]);
        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $response, "Create allowed");
    }

    public function testCanNotCreate()
    {
        $token = $this->createMockTokenWithNonRootSessionUser();
        $user = $token->getUser();
        $entity = m::mock(ReportInterface::class);
        $entity->shouldReceive('getUser')->andReturn(m::mock(UserInterface::class));
        $user->shouldReceive('isTheUser')->andReturn(false);
        $response = $this->voter->vote($token, $entity, [VoterPermissions::CREATE]);
        $this->assertEquals(VoterInterface::ACCESS_DENIED, $response, "Create denied");
    }

    public function supportsTypeProvider(): array
    {
        return [
            [ReportInterface::class, true],
            [self::class, false],
        ];
    }

    public function supportsAttributesProvider(): array
    {
        return [
            [VoterPermissions::VIEW, true],
            [VoterPermissions::CREATE, true],
            [VoterPermissions::DELETE, true],
            [VoterPermissions::EDIT, true],
            [VoterPermissions::LOCK, false],
            [VoterPermissions::UNLOCK, false],
            [VoterPermissions::ROLLOVER, false],
            [VoterPermissions::CREATE_TEMPORARY_FILE, false],
            [VoterPermissions::VIEW_DRAFT_CONTENTS, false],
            [VoterPermissions::VIEW_VIRTUAL_LINK, false],
            [VoterPermissions::ARCHIVE, false],
        ];
    }
}
