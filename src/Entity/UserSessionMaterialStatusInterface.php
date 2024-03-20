<?php

declare(strict_types=1);

namespace App\Entity;

use App\Traits\IdentifiableEntityInterface;
use App\Traits\TimestampableEntityInterface;

interface UserSessionMaterialStatusInterface extends
    IdentifiableEntityInterface,
    LoggableEntityInterface,
    TimestampableEntityInterface
{
    public const NONE = 0;
    public const STARTED = 1;
    public const COMPLETE = 2;

    public function setUser(UserInterface $user): void;
    public function getUser(): UserInterface;
    public function setMaterial(SessionLearningMaterialInterface $material): void;
    public function getMaterial(): SessionLearningMaterialInterface;
    public function setStatus(int $status): void;
    public function getStatus(): int;
}
