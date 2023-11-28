<?php

declare(strict_types=1);

namespace App\Tests\ServiceTokenVoter;

use App\Classes\VoterPermissions;
use App\Entity\CurriculumInventoryReportInterface;
use App\Entity\CurriculumInventorySequenceBlockInterface;
use App\Entity\SchoolInterface;
use App\ServiceTokenVoter\CurriculumInventorySequenceBlock as Voter;
use Mockery as m;

class CurriculumInventorySequenceBlockTest extends AbstractReadWriteBase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->voter = new Voter();
    }

    public function supportsTypeProvider(): array
    {
        return [
            [CurriculumInventorySequenceBlockInterface::class, true],
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

    public function writePermissionsProvider(): array
    {
        return [
            [VoterPermissions::CREATE],
            [VoterPermissions::DELETE],
            [VoterPermissions::EDIT],
        ];
    }

    protected function createMockSubjectWithSchoolContext(int $schoolId): m\MockInterface
    {
        $subject = $this->createMockSubject();
        $report = m::mock(CurriculumInventoryReportInterface::class);
        $school = m::mock(SchoolInterface::class);

        $subject->shouldReceive('getReport')->andReturn($report);
        $report->shouldReceive('getSchool')->andReturn($school);
        $school->shouldReceive('getId')->andReturn($schoolId);

        return $subject;
    }

    protected function createMockSubject(): m\MockInterface
    {
        return m::mock(CurriculumInventorySequenceBlockInterface::class);
    }
}
