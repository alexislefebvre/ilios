<?php

declare(strict_types=1);

namespace App\RelationshipVoter;

use App\Classes\SessionUserInterface;
use App\Classes\VoterPermissions;
use App\Entity\AssessmentOptionInterface;
use App\Service\SessionUserPermissionChecker;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AssessmentOption extends AbstractVoter
{
    public function __construct(SessionUserPermissionChecker $permissionChecker)
    {
        parent::__construct(
            $permissionChecker,
            AssessmentOptionInterface::class,
            [
                VoterPermissions::CREATE,
                VoterPermissions::VIEW,
                VoterPermissions::EDIT,
                VoterPermissions::DELETE,
            ]
        );
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof SessionUserInterface) {
            return false;
        }
        if ($user->isRoot()) {
            return true;
        }

        if ($subject instanceof AssessmentOptionInterface) {
            return VoterPermissions::VIEW === $attribute;
        }

        return false;
    }
}
