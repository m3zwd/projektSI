<?php

/**
 * Tag service.
 */

namespace App\Service;

use App\Entity\Recipe;
use App\Entity\Tag;
use App\Repository\RecipeRepository;
use App\Repository\TagRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class TagService.
 */
class TagService implements TagServiceInterface
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
     * @param TagRepository      $tagRepository    Tag repository
     * @param RecipeRepository   $recipeRepository Recipe repository
     * @param PaginatorInterface $paginator        Paginator
     */
    public function __construct(private readonly TagRepository $tagRepository, private readonly RecipeRepository $recipeRepository, private readonly PaginatorInterface $paginator)
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
            $this->tagRepository->queryAll(),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE,
            [
                'sortFieldAllowList' => ['tag.id', 'tag.createdAt', 'tag.updatedAt', 'tag.title'],
                'defaultSortFieldName' => 'tag.updatedAt',
                'defaultSortDirection' => 'desc',
            ]
        );
    }

    /**
     * Get recipes by tag.
     *
     * @param Tag $tag Tag entity
     *
     * @return array|Recipe[]
     */
    public function getRecipesByTag(Tag $tag): array
    {
        return $this->recipeRepository->findByTag($tag);
    }

    /**
     * Find by title.
     *
     * @param string $title Tag title
     *
     * @return Tag|null Tag entity
     */
    public function findOneByTitle(string $title): ?Tag
    {
        return $this->tagRepository->findOneByTitle($title);
    }

    /**
     * Save entity.
     *
     * @param Tag $tag Tag entity
     */
    public function save(Tag $tag): void
    {
        $this->tagRepository->save($tag);
    }

    /**
     * Delete entity.
     *
     * @param Tag $tag Tag entity
     */
    public function delete(Tag $tag): void
    {
        $this->tagRepository->delete($tag);
    }
}
