<?php

/**
 * Recipe repository.
 */

namespace App\Repository;

use App\Entity\Recipe;
use App\entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class Recipe Repository.
 *
 * @extends ServiceEntityRepository<Recipe>
 */
class RecipeRepository extends ServiceEntityRepository
{
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

    /**
     * Count recipes by category.
     *
     * @param Category $category Category
     *
     * @return int Number of recipes in category
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function countByCategory(Category $category): int
    {
        $qb = $this->createQueryBuilder('recipe');

        return $qb->select($qb->expr()->countDistinct('recipe.id'))
            ->where('recipe.category = :category')
            ->setParameter(':category', $category)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
