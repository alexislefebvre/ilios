<?php

declare(strict_types=1);

namespace App\RelationshipVoter;

use App\Classes\SessionUserInterface;
use App\Classes\VoterPermissions;
use App\Entity\UserInterface;
use App\Service\SessionUserPermissionChecker;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class User extends AbstractVoter
{
    public function __construct(SessionUserPermissionChecker $permissionChecker)
    {
        parent::__construct(
            $permissionChecker,
            UserInterface::class,
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
        return match ($attribute) {
            VoterPermissions::VIEW => $user->isTheUser($subject) || $user->performsNonLearnerFunction(),
            VoterPermissions::CREATE => $this->permissionChecker->canCreateUser($user, $subject->getSchool()->getId()),
            VoterPermissions::EDIT => $this->permissionChecker->canUpdateUser(
                $user,
                $subject->getSchool()->getId()
            ),
            VoterPermissions::DELETE => $this->permissionChecker->canDeleteUser(
                $user,
                $subject->getSchool()->getId()
            ),
            default => false,
        };
    }
}
