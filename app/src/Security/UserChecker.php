<?php

/**
 * User checker.
 */

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class UserChecker.
 */
class UserChecker implements UserCheckerInterface
{
    /**
     * Constructor.
     *
     * @param TranslatorInterface $translator Translator
     */
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Checks user account status before authentication.
     *
     * @param UserInterface $user The user being authenticated
     *
     * @throws CustomUserMessageAccountStatusException If the user is blocked
     */
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if ($user->isBlocked()) {
            throw new CustomUserMessageAccountStatusException(
                $this->translator->trans('error.account_blocked')
            );
        }
    }

    /**
     * Checks user account status after authentication.
     *
     * @param UserInterface $user The user that has been authenticated
     */
    public function checkPostAuth(UserInterface $user): void
    {
    }
}
