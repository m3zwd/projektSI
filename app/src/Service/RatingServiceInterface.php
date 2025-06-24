<?php

/**
 * Rating service interface.
 */

namespace App\Service;

use App\Entity\Rating;
use App\Entity\Recipe;
use App\Entity\User;

/**
 * Interface RatingServiceInterface.
 */
interface RatingServiceInterface
{
    /**
     * Calculate average ratings.
     *
     * @param Recipe $recipe Recipe entity
     *
     * @return float Average rating value
     */
    public function calculateAvg(Recipe $recipe): float;

    /**
     * Update average ratings.
     *
     * @param Recipe $recipe Recipe entity
     */
    public function updateAverageRating(Recipe $recipe): void;

    /**
     * Get list of ratings for the recipe.
     *
     * @param Recipe $recipe Recipe entity
     *
     * @return Rating[] List of ratings
     */
    public function getRecipeRatings(Recipe $recipe): array;

    /**
     * Create rating.
     *
     * @param Recipe $recipe Recipe entity
     * @param User   $author Rating author
     *
     * @return Rating Rating entity
     */
    public function createRating(Recipe $recipe, User $author): Rating;

    /**
     * Count ratings.
     *
     * @param Recipe $recipe Recipe entity
     *
     * @return int Number of ratings
     */
    public function countRatings(Recipe $recipe): int;

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
