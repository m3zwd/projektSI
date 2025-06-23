<?php

/**
 * Comment service.
 */

namespace App\Service;

use App\Entity\Comment;
use App\Entity\Recipe;
use App\Entity\User;
use App\Repository\CommentRepository;

/**
 * Class CommentService.
 */
class CommentService implements CommentServiceInterface
{
    /**
     * Constructor.
     *
     * @param CommentRepository $commentRepository Comment repository
     */
    public function __construct(private readonly CommentRepository $commentRepository)
    {
    }

    /**
     * Get list of comments for the recipe.
     *
     * @param Recipe $recipe Recipe entity
     *
     * @return Comment[] List of comments
     */
    public function getRecipeComments(Recipe $recipe): array
    {
        return $this->commentRepository->findBy(['recipe' => $recipe], ['createdAt' => 'DESC']);
    }

    /**
     * Create comment.
     *
     * @param User   $author Comment author
     * @param Recipe $recipe Recipe entity
     *
     * @return Comment Comment entity
     */
    public function createComment(User $author, Recipe $recipe): Comment
    {
        $comment = new Comment();
        $comment->setRecipe($recipe);
        $comment->setAuthor($author);
        $comment->setCreatedAt(new \DateTimeImmutable());

        return $comment;
    }

    /**
     * Get comment for given recipe (method used for editing or deleting).
     *
     * @param int    $id     Comment ID
     * @param Recipe $recipe Recipe entity the comment belongs to
     *
     * @return Comment|null The comment if found and belongs to the recipe, or null
     */
    public function getCommentForRecipe(int $id, Recipe $recipe): ?Comment
    {
        $comment = $this->commentRepository->find($id);

        return ($comment && $comment->getRecipe() === $recipe) ? $comment : null;
    }

    /**
     * Save entity.
     *
     * @param Comment $comment Comment entity
     */
    public function save(Comment $comment): void
    {
        $this->commentRepository->save($comment);
    }

    /**
     * Delete entity.
     *
     * @param Comment $comment Comment entity
     */
    public function delete(Comment $comment): void
    {
        $this->commentRepository->delete($comment);
    }
}
