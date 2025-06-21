<?php

/**
 * Rating service.
 */

namespace App\Service;

use App\Entity\Rating;
use App\Entity\Recipe;
use App\Repository\RatingRepository;
use App\Repository\RecipeRepository;

/**
 * Class RatingService.
 */
class RatingService implements RatingServiceInterface
{
    /**
     * Constructor.
     *
     * @param RatingRepository $ratingRepository Rating repository
     * @param RecipeRepository $recipeRepository Recipe repository
     */
    public function __construct(private readonly RatingRepository $ratingRepository, private readonly RecipeRepository $recipeRepository)
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
        $this->updateAverageRating($rating->getRecipe());
    }

    /**
     * Delete entity.
     *
     * @param Rating $rating Rating entity
     */
    public function delete(Rating $rating): void
    {
        $recipe = $rating->getRecipe();
        $this->ratingRepository->delete($rating);

        $this->updateAverageRating($recipe);
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
        return $this->ratingRepository->calculateAvg($recipe);
    }

    /**
     * Update average rating.
     *
     * @param Recipe $recipe Recipe entity
     */
    public function updateAverageRating(Recipe $recipe): void
    {
        $average = $this->ratingRepository->calculateAvg($recipe);
        $recipe->setAverageRating($average);
        $this->recipeRepository->save($recipe);
    }
}
