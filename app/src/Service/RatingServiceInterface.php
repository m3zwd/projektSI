<?php

/**
 * Rating service interface.
 */

namespace App\Service;

use App\Entity\Rating;

/**
 * Interface RatingServiceInterface.
 */
interface RatingServiceInterface
{
    /**
     * Save entity.
     *
     * @param Rating $rating Rating entity
     */
    public function save(Rating $rating): void;

    /**
     * Delete entity.
     *
     * @param Rating $rating Rating entity
     */
    public function delete(Rating $rating): void;
}
