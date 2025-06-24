<?php

/**
 * Rating service.
 */

namespace App\Service;

use App\Entity\Rating;
use App\Entity\Recipe;
use App\Entity\User;
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
     * Calculate average ratings.
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
     * Update average ratings.
     *
     * @param Recipe $recipe Recipe entity
     */
    public function updateAverageRating(Recipe $recipe): void
    {
        $average = $this->ratingRepository->calculateAvg($recipe);
        $recipe->setAverageRating($average);
        $this->recipeRepository->save($recipe);
    }

    /**
     * Get list of ratings for the recipe.
     *
     * @param Recipe $recipe Recipe entity
     *
     * @return Rating[] List of ratings
     */
    public function getRecipeRatings(Recipe $recipe): array
    {
        return $this->ratingRepository->findBy(['recipe' => $recipe], ['createdAt' => 'DESC']);
    }

    /**
     * Count ratings.
     *
     * @param Recipe $recipe Recipe entity
     *
     * @return int Number of ratings
     */
    public function countRatings(Recipe $recipe): int
    {
        return $this->ratingRepository->countRatings($recipe);
    }

    /**
     * Create rating.
     *
     * @param Recipe $recipe Recipe entity
     * @param User   $author Rating author
     *
     * @return Rating Rating entity
     */
    public function createRating(Recipe $recipe, User $author): Rating
    {
        $rating = new Rating();
        $rating->setRecipe($recipe);
        $rating->setAuthor($author);
        $rating->setCreatedAt(new \DateTimeImmutable());

        return $rating;
    }

    /**
     * Get rating for given recipe (method used for editing or deleting).
     *
     * @param int    $id     Rating ID
     * @param Recipe $recipe Recipe entity the rating belongs to
     *
     * @return Rating|null The rating if found and belongs to the recipe, or null
     */
    public function getRatingForRecipe(int $id, Recipe $recipe): ?Rating
    {
        $rating = $this->ratingRepository->find($id);

        return ($rating && $rating->getRecipe() === $recipe) ? $rating : null;
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
}
