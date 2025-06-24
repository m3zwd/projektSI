<?php

/**
 * Tag service interface.
 */

namespace App\Service;

use App\Entity\Recipe;
use App\Entity\Tag;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface TagServiceInterface.
 */
interface TagServiceInterface
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
     * Find recipes by tag.
     *
     * @param Tag $tag Tag entity
     *
     * @return array|Recipe[]
     */
    public function getRecipesByTag(Tag $tag): array;

    /**
     * Find by title.
     *
     * @param string $title Tag title
     *
     * @return Tag|null Tag entity
     */
    public function findOneByTitle(string $title): ?Tag;

    /**
     * Save entity.
     *
     * @param Tag $tag Tag entity
     */
    public function save(Tag $tag): void;

    /**
     * Delete entity.
     *
     * @param Tag $tag Tag entity
     */
    public function delete(Tag $tag): void;
}
