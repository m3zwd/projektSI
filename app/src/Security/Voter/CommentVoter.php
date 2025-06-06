<?php

/**
 * Comment voter.
 */

namespace App\Security\Voter;

use App\Entity\Comment;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class CommentVoter.
 */
final class CommentVoter extends Voter
{
    /**
     * Delete permission.
     *
     * @const string
     */
    public const DELETE = 'COMMENT_DELETE';

    /**
     * Edit permission.
     *
     * @const string
     */
    public const EDIT = 'COMMENT_EDIT';

    /**
     * View permission.
     *
     * @const string
     */
    public const VIEW = 'COMMENT_VIEW';

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
            && $subject instanceof Comment;
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

        if (!$subject instanceof Comment) {
            return false;
        }

        return match ($attribute) {
            self::EDIT => $this->canEdit($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            default => false,
        };
    }

    /**
     * Checks if user can edit comment.
     *
     * @param Comment       $comment Comment entity
     * @param UserInterface $user   User
     *
     * @return bool Result
     */
    private function canEdit(Comment $comment, UserInterface $user): bool
    {
        // edytowac moze tylko autor komentarza
        return $comment->getAuthor() === $user;
    }

    /**
     * Checks if user can delete comment.
     *
     * @param Comment       $comment Comment entity
     * @param UserInterface $user   User
     *
     * @return bool Result
     */
    private function canDelete(Comment $comment, UserInterface $user): bool
    {
        // autor komentarza zawsze moze usunac
        if ($comment->getAuthor() === $user) {
            return true;
        }

        // jesli uzytkownik jest adminem
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            // jesli autor komentarza rowniez jest adminem, to zabron usuwania
            if (in_array('ROLE_ADMIN', $comment->getAuthor()->getRoles(), true)) {
                return false;
            }
            // adnim moze usuwac kom zwyklych uzytkownikow
            return true;
        }

        return false;
    }
}
