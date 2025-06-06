<?php

/**
 * Recipe repository.
 */

namespace App\Repository;

use App\Dto\RecipeListInputFiltersDto;
use App\Entity\Recipe;
use App\Entity\Category;
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
     * @return QueryBuilder Query Builder
     */
    public function queryAll(): QueryBuilder
    {
        return $this->createQueryBuilder('recipe')
            ->select(
                'partial recipe.{id, createdAt, updatedAt, title}',
                'partial category.{id, title}',
                'partial tags.{id, title}'
            )
            ->join('recipe.category', 'category')
            ->leftJoin('recipe.tags', 'tags');
        // ->andWhere('recipe.author = :author')
        // ->setParameter('author', $author);
        // jak autor nie jest nullem to trzeba dolaczyc te linijki, jak jest null to bez nich
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
     * Query recipes by filters.
     *
     * @param User|null $user User
     *
     * @param RecipeListInputFiltersDto $filters
     *
     * @return QueryBuilder
     */
    public function queryByFilters(?User $user, RecipeListInputFiltersDto $filters): QueryBuilder
    {
        $qb = $this->createQueryBuilder('recipe')
            ->select(
                'partial recipe.{id, createdAt, updatedAt, title}',
                'partial category.{id, title}',
                'partial tags.{id, title}'
            )
            ->join('recipe.category', 'category')
            ->leftJoin('recipe.tags', 'tags');

        // tylko jeśli filtr onlyMine jest ustawiony i użytkownik jest zalogowany
        if ($filters->onlyMine && $user !== null) {
            $qb->andWhere('recipe.author = :author')
                ->setParameter('author', $user);
        }

        if ($filters->categoryId !== null) {
            $qb->andWhere('category.id = :categoryId')
                ->setParameter('categoryId', $filters->categoryId);
        }

        if ($filters->tagId !== null) {
            $qb->andWhere(':tagId MEMBER OF recipe.tags')
                ->setParameter('tagId', $filters->tagId);
        }

        return $qb;
    }
}
