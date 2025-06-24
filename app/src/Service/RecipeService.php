<?php

/**
 * Recipe service.
 */

namespace App\Service;

use App\Dto\RecipeListInputFiltersDto;
use App\Entity\Recipe;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\RecipeRepository;
use App\Repository\TagRepository;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
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
     * @param RecipeRepository   $recipeRepository   Recipe repository
     * @param PaginatorInterface $paginator          Paginator
     * @param CategoryRepository $categoryRepository Category repository
     * @param TagRepository      $tagRepository      Tag repository
     * @param RatingServiceInterface $ratingService  Rating service
     */
    public function __construct(private readonly RecipeRepository $recipeRepository, private readonly PaginatorInterface $paginator, private readonly CategoryRepository $categoryRepository, private readonly TagRepository $tagRepository, private readonly RatingServiceInterface $ratingService)
    {
    }

    /**
     * Get paginated list.
     *
     * @param int                       $page    Page number
     * @param User|null                 $author  Currently logged-in user
     * @param RecipeListInputFiltersDto $filters Filters
     *
     * @return PaginationInterface<SlidingPagination> Paginated list
     */
    public function getPaginatedList(int $page, ?User $author, RecipeListInputFiltersDto $filters): PaginationInterface
    {
        $query = $this->recipeRepository->queryByFilters($author, $filters);

        return $this->paginator->paginate(
            $query,
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE,
            [
                'sortFieldAllowList' => ['recipe.id', 'recipe.createdAt', 'recipe.updatedAt', 'recipe.title', 'category.title', 'recipe.averageRating'],
                'defaultSortFieldName' => 'recipe.updatedAt',
                'defaultSortDirection' => 'desc',
            ]
        );
    }

    /**
     * Get average rating for each recipe on the list.
     *
     * @param PaginationInterface $pagination Pagination
     */
    public function getAvgRatingsList(PaginationInterface $pagination): void
    {
        foreach ($pagination as $recipe) {
            $averageRating = $this->ratingService->calculateAvg($recipe);
            $recipe->setAverageRating($averageRating);
        }
    }

    /**
     * Get all categories and tags.
     *
     * @return array
     */
    public function getCategoriesAndTags(): array
    {
        return [
            'categories' => $this->categoryRepository->findAll(),
            'tags' => $this->tagRepository->findAll(),
        ];
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
