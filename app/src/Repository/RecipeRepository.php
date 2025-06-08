<?php

/**
 * Recipe repository.
 */

namespace App\Repository;

use App\Dto\RecipeListFiltersDto;
use App\Entity\Recipe;
use App\Entity\Category;
use App\Entity\Tag;
use App\Entity\User;
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
     * @param User|null            $author  User entity
     * @param RecipeListFiltersDto $filters Filters
     *
     * @return QueryBuilder Query Builder
     */
    public function queryAll(?User $author, RecipeListFiltersDto $filters): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('recipe')
            ->select(
                'partial recipe.{id, createdAt, updatedAt, title}',
                'partial category.{id, title}',
                'partial tags.{id, title}'
            )
            ->join('recipe.category', 'category')
            ->leftJoin('recipe.tags', 'tags');

        // Jeśli filtrujemy tylko własne przepisy i jest podany author, dodaj warunek
        if ($filters->onlyMine && null !== $author) {
            $queryBuilder->andWhere('recipe.author = :author')
                ->setParameter('author', $author);
        }

        return $this->applyFiltersToList($queryBuilder, $filters);
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

    /**
     * Count recipes by user.
     *
     * @param User $author User
     *
     * @return int Number of recipes user owns
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function countByUser(User $author): int
    {
        $qb = $this->createQueryBuilder('recipe');

        return (int) $qb->select($qb->expr()->countDistinct('recipe.id'))
            ->where('recipe.author = :author')
            ->setParameter(':author', $author)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Save entity.
     *
     * @param Recipe $recipe Recipe entity
     */
    public function save(Recipe $recipe): void
    {
        $this->getEntityManager()->persist($recipe);
        $this->getEntityManager()->flush();
    }

    /**
     * Delete entity.
     *
     * @param Recipe $recipe Recipe entity
     */
    public function delete(Recipe $recipe): void
    {
        $this->getEntityManager()->remove($recipe);
        $this->getEntityManager()->flush();
    }

    /**
     * Apply filters to paginated list.
     *
     * @param QueryBuilder         $queryBuilder  Query builder
     * @param RecipeListFiltersDto $filters       Filters
     * @param User|null            $author        Currently logged-in user
     *
     * @return QueryBuilder Query builder
     */
    private function applyFiltersToList(QueryBuilder $queryBuilder, RecipeListFiltersDto $filters, ?User $author): QueryBuilder
    {
        if ($filters->category instanceof Category) {
            $queryBuilder->andWhere('category = :category')
                ->setParameter('category', $filters->category);
        }

        if ($filters->tag instanceof Tag) {
            $queryBuilder->andWhere('tags IN (:tag)')
                ->setParameter('tag', $filters->tag);
        }

        if ($filters->onlyMine && $author !== null) {
            $queryBuilder->andWhere('author = :author')
                ->setParameter('author', $author);
        }

        return $queryBuilder;
    }
}
