<?php

/**
 * Recipe controller.
 */

namespace App\Controller;

use App\Dto\RecipeListInputFiltersDto;
use App\Entity\Rating;
use App\Entity\Recipe;
use App\Entity\User;
use App\Form\Type\CommentType;
use App\Form\Type\RatingType;
use App\Form\Type\RecipeType;
use App\Repository\CommentRepository;
use App\Repository\RatingRepository;
use App\Resolver\RecipeListInputFiltersDtoResolver;
use App\Security\Voter\RecipeVoter;
use App\Service\CommentService;
use App\Service\RatingService;
use App\Service\RatingServiceInterface;
use App\Service\RecipeServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class RecipeController.
 */
class RecipeController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param RecipeServiceInterface $recipeService      Recipe service
     * @param RatingService          $ratingService      Rating service
     * @param CommentService         $commentService     Comment service
     * @param TranslatorInterface    $translator         Translator
     */
    public function __construct(private readonly RecipeServiceInterface $recipeService, private readonly RatingService $ratingService, private readonly CommentService $commentService, private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Index action.
     *
     * @param RecipeListInputFiltersDto $filters       Input filters
     * @param int                       $page          Page number
     *
     * @return Response HTTP response
     */
    #[Route(
        '/recipe/',
        name: 'recipe_index',
        methods: 'GET'
    )]
    public function index(#[MapQueryString(resolver: RecipeListInputFiltersDtoResolver::class)] RecipeListInputFiltersDto $filters, #[MapQueryParameter] int $page = 1): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();
        $pagination = $this->recipeService->getPaginatedList(
            $page,
            $user,
            $filters
        );

        $this->recipeService->getAvgRatingsList($pagination);

        $categoriesAndTags = $this->recipeService->getCategoriesAndTags();

        return $this->render(
            'recipe/index.html.twig',
            [
                'pagination' => $pagination,
                'categories' => $categoriesAndTags['categories'],
                'tags' => $categoriesAndTags['tags'],
                'filters' => $filters,
            ]
        );
    }

    /**
     * View action.
     *
     * @param Request                $request           HTTP request
     * @param Recipe                 $recipe            Recipe entity
     * @param CommentRepository      $commentRepository View comments
     * @param RatingServiceInterface $ratingService     Rating service interface
     * @param RatingRepository       $ratingRepository  Rating repository
     *
     * @return Response HTTP response
     */
    #[Route(
        '/recipe/{id}',
        name: 'recipe_view',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET|POST'
    )]
    public function view(Request $request, Recipe $recipe, RatingServiceInterface $ratingService, RatingRepository $ratingRepository): Response
    {
        $comments = $this->commentService->getRecipeComments($recipe);

        /** @var User $author */
        $author = $this->getUser();
        $comment = $this->commentService->createComment($author, $recipe);
        $commentForm = $this->createForm(CommentType::class, $comment);

        $editCommentId = $request->request->get('edit_comment_id');
        $editComment = null;
        $editCommentForm = null;
        if ($editCommentId) {
            $editComment = $this->commentService->getCommentForRecipe($editCommentId, $recipe);

            if (!$editComment || !$this->isGranted('COMMENT_EDIT', $editComment)) {
                $this->addFlash('error', $this->translator->trans('message.access_denied'));
                return $this->redirectToRoute('recipe_view', ['id' => $recipe->getId()]);
            }

            $editCommentForm = $this->createForm(CommentType::class, $editComment);
            $editCommentForm->handleRequest($request);

            if ($editCommentForm->isSubmitted() && $editCommentForm->isValid()) {
                $this->commentService->save($editComment);
                $this->addFlash('success', $this->translator->trans('message.edited_successfully'));
                return $this->redirectToRoute('recipe_view', ['id' => $recipe->getId()]);
            }
        }

        $deleteCommentId = $request->request->get('delete_comment_id');
        if ($deleteCommentId) {
            $deleteCommentId = $this->commentService->getCommentForRecipe($deleteCommentId, $recipe);

            if ($deleteCommentId && $deleteCommentId->getRecipe() === $recipe && $this->isGranted('COMMENT_DELETE', $deleteCommentId)) {
                $this->commentService->delete($deleteCommentId);
                $this->addFlash('success', $this->translator->trans('message.deleted_successfully'));
            }

            return $this->redirectToRoute('recipe_view', ['id' => $recipe->getId()]);
        }

        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $this->commentService->save($comment);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('recipe_view', ['id' => $recipe->getId()]);
        }

        /**
         * View average rating for recipe.
         */
        $this->ratingService->updateAverageRating($recipe);

        /**
         * Add rating.
         */
        $rating = new Rating();
        $rating->setRecipe($recipe);
        $rating->setAuthor($this->getUser());
        $rating->setCreatedAt(new \DateTimeImmutable());

        $ratingForm = $this->createForm(RatingType::class, $rating);
        $ratingForm->handleRequest($request);

        if ($ratingForm->isSubmitted() && $ratingForm->isValid()) {
            $ratingService->save($rating);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('recipe_view', ['id' => $recipe->getId()]);
        }

        /**
         * Count ratings.
         */
        $ratingCount = $ratingRepository->countRatings($recipe);

        return $this->render(
            'recipe/view.html.twig',
            [
                'recipe' => $recipe,
                'comments' => $comments,
                'comment_form' => $commentForm->createView(),
                'edit_comment_form' => $editCommentForm?->createView(),
                'edit_comment' => $editComment,
                'rating_form' => $ratingForm->createView(),
                'ratingCount' => $ratingCount,
            ]
        );
    }

    /**
     * Create action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route(
        '/recipe/create',
        name: 'recipe_create',
        methods: 'GET|POST',
    )]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $recipe = new Recipe();
        $recipe->setAuthor($user);
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->recipeService->save($recipe);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('recipe_index');
        }

        return $this->render(
            'recipe/create.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Edit action.
     *
     * @param Request $request HTTP request
     * @param Recipe  $recipe  Recipe entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/recipe/{id}/edit',
        name: 'recipe_edit',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET|PUT'
    )]
    #[IsGranted(RecipeVoter::EDIT, subject: 'recipe')]
    public function edit(Request $request, Recipe $recipe): Response
    {
        $form = $this->createForm(
            RecipeType::class,
            $recipe,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('recipe_edit', ['id' => $recipe->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->recipeService->save($recipe);

            $this->addFlash(
                'success',
                $this->translator->trans('message.edited_successfully')
            );

            return $this->redirectToRoute('recipe_index');
        }

        return $this->render(
            'recipe/edit.html.twig',
            [
                'form' => $form->createView(),
                'recipe' => $recipe,
            ]
        );
    }

    /**
     * Delete action.
     *
     * @param Request $request HTTP request
     * @param Recipe  $recipe  Recipe entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/recipe/{id}/delete',
        name: 'recipe_delete',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET|DELETE'
    )]
    #[IsGranted(RecipeVoter::DELETE, subject: 'recipe')]
    public function delete(Request $request, Recipe $recipe): Response
    {
        $form = $this->createForm(FormType::class, $recipe, [
            'method' => 'DELETE',
            'action' => $this->generateUrl('recipe_delete', ['id' => $recipe->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->recipeService->delete($recipe);

            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );

            return $this->redirectToRoute('recipe_index');
        }

        return $this->render(
            'recipe/delete.html.twig',
            [
                'form' => $form->createView(),
                'recipe' => $recipe,
            ]
        );
    }
}
