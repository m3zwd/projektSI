<?php

/**
 * Recipe controller.
 */

namespace App\Controller;

use App\Dto\RecipeListInputFiltersDto;
use App\Entity\Comment;
use App\Entity\Recipe;
use App\Entity\User;
use App\Form\Type\CommentType;
use App\Form\Type\RecipeType;
use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use App\Repository\TagRepository;
use App\Resolver\RecipeListInputFiltersDtoResolver;
use App\Security\Voter\RecipeVoter;
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
     * @param TranslatorInterface    $translator         Translator
     * @param CategoryRepository     $categoryRepository Category repository
     * @param TagRepository          $tagRepository      Tag repository
     */
    public function __construct(private readonly RecipeServiceInterface $recipeService, private readonly TranslatorInterface $translator, private readonly CategoryRepository $categoryRepository, private readonly TagRepository $tagRepository)
    {
    }

    /**
     * Index action.
     *
     * @param RecipeListInputFiltersDto $filters Input filters
     * @param int                       $page    Page number
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

        // pobranie wszystkich kategorii i tagów z repozytoriów
        $categories = $this->categoryRepository->findAll();
        $tags = $this->tagRepository->findAll();

        return $this->render(
            'recipe/index.html.twig',
            [
                'pagination' => $pagination,
                'categories' => $categories,
                'tags' => $tags,
                'filters' => $filters,
            ]
        );
    }

    /**
     * View action.
     *
     * @param Request           $request           HTTP request
     * @param Recipe            $recipe            Recipe entity
     * @param CommentRepository $commentRepository View comments
     *
     * @return Response HTTP response
     */
    #[Route(
        '/recipe/{id}',
        name: 'recipe_view',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET|POST'
    )]
    public function view(Request $request, Recipe $recipe, CommentRepository $commentRepository): Response
    {
        // usuwanie komentarza
        $deleteCommentId = $request->request->get('delete_comment_id');
        if ($deleteCommentId) {
            $commentToDelete = $commentRepository->find($deleteCommentId);

            if ($commentToDelete && $commentToDelete->getRecipe() === $recipe) {
                if ($this->isGranted('COMMENT_DELETE', $commentToDelete)) {
                    $commentRepository->delete($commentToDelete);
                    $this->addFlash('success', $this->translator->trans('message.deleted_successfully'));
                }
                /* czy to jest potrzebne wgl, skoro nie ma jak sie dostac do usuwania koma bez uprawnien??
                 else {
                    $this->addFlash('error', $this->translator->trans('message.access_denied'));
                }
                */
            }

            return $this->redirectToRoute('recipe_view', ['id' => $recipe->getId()]);
        }

        // edycja komentarza
        $editCommentId = $request->request->get('edit_comment_id');
        $editComment = null;
        $editForm = null;
        if ($editCommentId) {
            $editComment = $commentRepository->find($editCommentId);
            if (!$editComment || $editComment->getRecipe() !== $recipe || !$this->isGranted('COMMENT_EDIT', $editComment)) {
                $this->addFlash('error', $this->translator->trans('message.access_denied'));

                return $this->redirectToRoute('recipe_view', ['id' => $recipe->getId()]);
            }

            // formularz edycji z istniejącym komentarzem
            $editForm = $this->createForm(CommentType::class, $editComment);
            $editForm->handleRequest($request);

            if ($editForm->isSubmitted() && $editForm->isValid()) {
                $commentRepository->save($editComment);

                $this->addFlash('success', $this->translator->trans('message.edited_successfully'));

                return $this->redirectToRoute('recipe_view', ['id' => $recipe->getId()]);
            }
        }

        // dodawanie komentarza
        $comment = new Comment();
        $comment->setRecipe($recipe);
        $comment->setAuthor($this->getUser());
        $comment->setCreatedAt(new \DateTimeImmutable());
        $form = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commentRepository->save($comment);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('recipe_view', ['id' => $recipe->getId()]);
        }

        // pobieranie komentarzy
        $comments = $commentRepository->findBy(['recipe' => $recipe], ['createdAt' => 'DESC']);

        return $this->render(
            'recipe/view.html.twig',
            [
                'recipe' => $recipe,
                'comments' => $comments,
                'comment_form' => $form->createView(),
                'edit_comment_form' => $editForm?->createView(),
                'edit_comment' => $editComment,
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
