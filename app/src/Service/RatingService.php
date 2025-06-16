<?php

/**
 * Rating service.
 */

namespace App\Service;

use App\Entity\Rating;
use App\Repository\RatingRepository;

/**
 * Class RatingService.
 */
class RatingService implements RatingServiceInterface
{
    /**
     * Constructor.
     *
     * @param RatingRepository $ratingRepository Rating repository
     */
    public function __construct(private readonly RatingRepository $ratingRepository)
    {
    }

    /**
     * Save entity.
     *
     * @param Rating $rating Rating entity
     */
    public function save(Rating $rating): void
    {
        $this->ratingRepository->save($rating);
    }

    /**
     * Delete entity.
     *
     * @param Rating $rating Rating entity
     */
    public function delete(Rating $rating): void
    {
        $this->ratingRepository->delete($rating);
    }
}
