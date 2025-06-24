<?php

/**
 * User service interface.
 */

namespace App\Service;

use App\Entity\Recipe;
use App\Entity\User;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface UserServiceInterface.
 */
interface UserServiceInterface
{
    /**
     * Get paginated list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedList(int $page): PaginationInterface;

    /**
     * Find recipes by user.
     *
     * @param User $user User entity
     *
     * @return array<Recipe> List of recipes
     */
    public function getRecipesByUser(User $user): array;

    /**
     * Change user's role based on checkbox.
     *
     * @param User $user    User entity
     * @param bool $isAdmin Checkbox: checked if user should have admin role
     *
     * @return bool True if role was changed, false if operation was blocked (last admin)
     */
    public function changeUserRole(User $user, bool $isAdmin): bool;

    /**
     * Change user's block status based on checkbox.
     *
     * @param User $user      User entity
     * @param bool $isBlocked Checkbox: checked if user should be blocked
     *
     * @return bool True if status was changed, false if operation was blocked (last admin)
     */
    public function changeUserBlockStatus(User $user, bool $isBlocked): bool;

    /**
     * Can User be deleted?
     *
     * @param User $user User entity
     *
     * @return bool Result
     */
    public function canBeDeleted(User $user): bool;

    /**
     * Save entity.
     *
     * @param User $user User entity
     */
    public function save(User $user): void;

    /**
     * Delete entity.
     *
     * @param User $user User entity
     */
    public function delete(User $user): void;
}
