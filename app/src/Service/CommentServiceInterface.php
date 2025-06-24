<?php

/**
 * Comment service interface.
 */

namespace App\Service;

use App\Entity\Comment;
use App\Entity\Recipe;
use App\Entity\User;

/**
 * Interface CommentServiceInterface.
 */
interface CommentServiceInterface
{
    /**
     * Get list of comments for the recipe.
     *
     * @param Recipe $recipe Recipe entity
     */
    public function getRecipeComments(Recipe $recipe): array;

    /**
     * Create comment.
     *
     * @param Recipe $recipe Recipe entity
     * @param User   $author Comment author
     */
    public function createComment(Recipe $recipe, User $author): Comment;

    /**
     * Get comment for given recipe (method used for editing or deleting).
     *
     * @param int    $id     Comment ID
     * @param Recipe $recipe Recipe entity the comment belongs to
     */
    public function getCommentForRecipe(int $id, Recipe $recipe): ?Comment;

    /**
     * Save entity.
     *
     * @param Comment $comment Comment entity
     */
    public function save(Comment $comment): void;

    /**
     * Delete entity.
     *
     * @param Comment $comment Comment entity
     */
    public function delete(Comment $comment): void;
}
