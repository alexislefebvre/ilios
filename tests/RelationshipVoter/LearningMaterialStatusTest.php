<?php

declare(strict_types=1);

namespace App\Tests\RelationshipVoter;

use App\Classes\VoterPermissions;
use App\Entity\LearningMaterialStatusInterface;
use App\RelationshipVoter\LearningMaterialStatus as Voter;
use App\Service\SessionUserPermissionChecker;
use Mockery as m;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class LearningMaterialStatusTest extends AbstractBase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->permissionChecker = m::mock(SessionUserPermissionChecker::class);
        $this->voter = new Voter($this->permissionChecker);
    }

    public function testAllowsRootFullAccess()
    {
        $this->checkRootEntityAccess(m::mock(LearningMaterialStatusInterface::class), [VoterPermissions::VIEW]);
    }

    public function testCanView()
    {
        $token = $this->createMockTokenWithNonRootSessionUser();
        $entity = m::mock(LearningMaterialStatusInterface::class);
        $response = $this->voter->vote($token, $entity, [VoterPermissions::VIEW]);
        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $response, "View allowed");
    }

    public function supportsTypeProvider(): array
    {
        return [
            [LearningMaterialStatusInterface::class, true],
            [self::class, false],
        ];
    }

    public function supportsAttributesProvider(): array
    {
        return [
            [VoterPermissions::VIEW, true],
            [VoterPermissions::CREATE, false],
            [VoterPermissions::DELETE, false],
            [VoterPermissions::EDIT, false],
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
