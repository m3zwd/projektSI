<?php

/**
 * Recipe repository.
 */

namespace App\Repository;

use App\Entity\Recipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class Recipe Repository.
 *
 * @extends ServiceEntityRepository<Recipe>
 */
class RecipeRepository extends ServiceEntityRepository
{
    /**
     * Items per page.
     *
     * Use constants to define configuration options that rarely change instead
     * of specifying them in configuration files.
     * See https://symfony.com/doc/current/best_practices.html#configuration
     *
     * @constant int
     */
    public const PAGINATOR_ITEMS_PER_PAGE = 10;

    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recipe::class);
    }

    /**
     * Query all records.
     *
     * @return QueryBuilder Query Builder
     */
    public function queryAll(): QueryBuilder
    {
        return $this->createQueryBuilder('recipe')
            ->select(
                'partial recipe.{id, createdAt, updatedAt, title}',
                'partial category.{id, title}'
            )
            ->join('recipe.category', 'category');
    }
}
