<?php

/**
 * Rating service interface.
 */

namespace App\Service;

use App\Entity\Rating;
use App\Entity\Recipe;

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

    /**
     * Calculate average rating for recipe.
     *
     * @param Recipe $recipe Recipe entity
     *
     * @return float
     */
    public function calculateAvg(Recipe $recipe): float;
}
