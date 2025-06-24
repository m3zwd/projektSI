<?php

/**
 * Rating voter.
 */

namespace App\Security\Voter;

use App\Entity\Rating;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class RatingVoter.
 */
final class RatingVoter extends Voter
{
    /**
     * Delete permisssion.
     *
     * @const string
     */
    public const DELETE = 'RATING_DELETE';

    /**
     * Edit permission.
     *
     * @const string
     */
    public const EDIT = 'RATING_EDIT';

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute
     * @param mixed  $subject   The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool Result
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::DELETE, self::EDIT])
            && $subject instanceof Rating;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string         $attribute Permission name
     * @param mixed          $subject   Object
     * @param TokenInterface $token     Security token
     *
     * @return bool Vote result
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        if (!$subject instanceof Rating) {
            return false;
        }

        return match ($attribute) {
            self::EDIT => $this->canEdit($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            default => false,
        };
    }

    /**
     * Checks if user can edit rating.
     *
     * @param Rating        $rating Rating entity
     * @param UserInterface $user   User
     *
     * @return bool Result
     */
    private function canEdit(Rating $rating, UserInterface $user): bool
    {
        return $rating->getAuthor() === $user;
    }

    /**
     * Checks if user can delete rating.
     *
     * @param Rating        $rating Rating entity
     * @param UserInterface $user   User
     *
     * @return bool Result
     */
    private function canDelete(Rating $rating, UserInterface $user): bool
    {
        if ($rating->getAuthor() === $user) {
            return true;
        }

        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return !in_array('ROLE_ADMIN', $rating->getAuthor()->getRoles(), true);
        }

        return false;
    }
}
