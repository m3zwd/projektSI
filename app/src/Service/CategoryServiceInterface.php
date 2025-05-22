<?php

/**
 * Category service interface.
 */

namespace App\Service;

use App\Entity\Category;
use App\Entity\Recipe;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface CategoryServiceInterface.
 */
interface CategoryServiceInterface
{
    /**
     * Get paginated list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page): PaginationInterface;

    /**
     * Find recipes by category.
     *
     * @param Category $category Category entity
     *
     * @return array<Recipe> List of recipes
     */
    public function getRecipesByCategory(Category $category): array;
}
