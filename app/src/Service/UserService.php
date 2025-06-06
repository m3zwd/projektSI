<?php

/**
 * User service.
 */

namespace App\Service;

use App\Entity\Recipe;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\RecipeRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class UserService.
 */
class UserService implements UserServiceInterface
{
    /**
     * Items per page.
     *
     * Use constants to define configuration options that rarely change instead
     * of specifying them in app/config/config.yml.
     * See https://symfony.com/doc/current/best_practices.html#configuration
     *
     * @constant int
     */
    private const PAGINATOR_ITEMS_PER_PAGE = 10;

    /**
     * Constructor.
     *
     * @param UserRepository     $userRepository     User repository
     * @param RecipeRepository   $recipeRepository   Recipe repository
     * @param PaginatorInterface $paginator          Paginator
     */
    public function __construct(private readonly UserRepository $userRepository, private readonly RecipeRepository $recipeRepository, private readonly PaginatorInterface $paginator)
    {
    }

    /**
     * Get paginated list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedList(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->userRepository->queryAll(),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE,
            [
                'sortFieldAllowList' => ['user.id', 'user.email', 'user.roles', 'user.password'],
                'defaultSortFieldName' => 'user.id',
                'defaultSortDirection' => 'desc',
            ]
        );
    }

    /**
     * Find recipes by user.
     *
     * @param User $user User entity
     *
     * @return array|Recipe[]
     */
    public function getRecipesByUser(User $user): array
    {
        return $this->recipeRepository->findBy(['author' => $user]);
    }

    /**
     * Save entity.
     *
     * @param User $user User entity
     */
    public function save(User $user): void
    {
        $this->userRepository->save($user);
    }

    /**
     * Delete entity.
     *
     * @param User $user User entity
     */
    public function delete(User $user): void
    {
        $this->userRepository->delete($user);
    }

    /**
     * Can User be deleted?
     *
     * @param User $user User entity
     *
     * @return bool Result
     */
    public function canBeDeleted(User $user): bool
    {
        try {
            $result = $this->recipeRepository->countByUser($user);

            return $result <= 0;
        } catch (NoResultException|NonUniqueResultException) {
            return false;
        }
    }
}
