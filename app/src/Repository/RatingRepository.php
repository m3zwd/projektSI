<?php

/**
 * Rating repository.
 */

namespace App\Repository;

use App\Entity\Rating;
use App\Entity\Recipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class RatingRepository.
 *
 * @extends ServiceEntityRepository<Rating>
 */
class RatingRepository extends ServiceEntityRepository
{
    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rating::class);
    }

    /**
     * Save entity.
     *
     * @param Rating $rating Rating entity
     */
    public function save(Rating $rating): void
    {
        $this->getEntityManager()->persist($rating);
        $this->getEntityManager()->flush();
    }

    /**
     * Delete entity.
     *
     * @param Rating $rating Rating entity
     */
    public function delete(Rating $rating): void
    {
        $this->getEntityManager()->remove($rating);
        $this->getEntityManager()->flush();
    }

    /**
     * Calculate average rating for recipe.
     *
     * @param Recipe $recipe Recipe entity
     *
     * @return float Average rating value
     */
    public function calculateAvg(Recipe $recipe): float
    {
        $result = $this->createQueryBuilder('rating')
            ->select('AVG(rating.value) AS ranking')
            ->where('rating.recipe = :recipe')
            ->setParameter('recipe', $recipe)
            ->getQuery()
            ->getSingleScalarResult();

        return null !== $result ? (float) $result : 0.0;
    }
}
