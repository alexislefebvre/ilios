<?php

declare(strict_types=1);

namespace App\ServiceTokenVoter;

use App\Classes\VoterPermissions;
use App\Entity\SessionTypeInterface;

class SessionType extends AbstractReadWriteEntityVoter
{
    public function __construct()
    {
        parent::__construct(
            SessionTypeInterface::class,
            [
                VoterPermissions::CREATE,
                VoterPermissions::VIEW,
                VoterPermissions::EDIT,
                VoterPermissions::DELETE,
            ]
        );
    }

    protected function getSchoolIdFromEntity(mixed $subject): int
    {
        return $subject->getSchool()->getId();
    }
}
