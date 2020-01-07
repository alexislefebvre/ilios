<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Traits\IdentifiableEntityInterface;
use App\Traits\LearnerGroupsEntityInterface;
use App\Traits\TitledEntityInterface;
use App\Traits\CoursesEntityInterface;
use App\Traits\UsersEntityInterface;

/**
 * Interface CohortInterface
 */
interface CohortInterface extends
    IdentifiableEntityInterface,
    TitledEntityInterface,
    CoursesEntityInterface,
    LoggableEntityInterface,
    LearnerGroupsEntityInterface,
    UsersEntityInterface
{
    /**
     * @param ProgramYearInterface $programYear
     */
    public function setProgramYear(ProgramYearInterface $programYear = null);

    /**
     * @return ProgramYearInterface
     */
    public function getProgramYear();

    /**
     * Get the school we belong to
     * @return SchoolInterface|null
     */
    public function getSchool();

    /**
     * Gets the program that this cohort belongs to.
     * @return ProgramInterface|null
     */
    public function getProgram();
}
