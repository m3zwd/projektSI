<?php

/**
 * Recipe service.
 */

namespace App\Service;

use App\Dto\RecipeListInputFiltersDto;
use App\Entity\Recipe;
use App\Entity\User;
use App\Repository\RecipeRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class RecipeService.
 */
class RecipeService implements RecipeServiceInterface
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
     * @param RecipeRepository   $recipeRepository Recipe repository
     * @param PaginatorInterface $paginator        Paginator
     */
    public function __construct(private readonly RecipeRepository $recipeRepository, private readonly PaginatorInterface $paginator)
    {
    }

    /**
     * Get paginated list with filters.
     *
     * @param int                         $page    Page number
     * @param User|null                   $user    Currently logged-in user
     * @param RecipeListInputFiltersDto   $filters Filters from query
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedList(int $page, ?User $user, RecipeListInputFiltersDto $filters): PaginationInterface
    {
        $query = $this->recipeRepository->queryByFilters($user, $filters);

        return $this->paginator->paginate(
            #$this->recipeRepository->queryAll(),
            $query,
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE,
            [
                'sortFieldAllowList' => ['recipe.id', 'recipe.createdAt', 'recipe.updatedAt', 'recipe.title', 'category.title'],
                'defaultSortFieldName' => 'recipe.updatedAt',
                'defaultSortDirection' => 'desc',
            ]
        );
    }

    /**
     * Save entity.
     *
     * @param Recipe $recipe Recipe entity
     */
    public function save(Recipe $recipe): void
    {
        $this->recipeRepository->save($recipe);
    }

    /**
     * Delete entity.
     *
     * @param Recipe $recipe Recipe entity
     */
    public function delete(Recipe $recipe): void
    {
        $this->recipeRepository->delete($recipe);
    }
}
