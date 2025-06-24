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
     * Count ratings.
     *
     * @param Recipe $recipe Recipe entity
     *
     * @return int Number of ratings
     */
    public function countRatings(Recipe $recipe): int;

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
     * Get rating for given recipe (method used for editing or deleting).
     *
     * @param int    $id     Rating ID
     * @param Recipe $recipe Recipe entity the rating belongs to
     *
     * @return Rating|null The rating if found and belongs to the recipe, or null
     */
    public function getRatingForRecipe(int $id, Recipe $recipe): ?Rating;

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
