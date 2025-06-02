<?php

/**
 * Recipe controller.
 */

namespace App\Controller;

use App\Entity\Recipe;
use App\Entity\User;
use App\Form\Type\RecipeType;
use App\Security\Voter\RecipeVoter;
use App\Service\RecipeServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
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
     * @param RecipeServiceInterface $recipeService Recipe service
     * @param TranslatorInterface    $translator    Translator
     */
    public function __construct(private readonly RecipeServiceInterface $recipeService, private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Index action.
     *
     * @param int $page Page number
     *
     * @return Response HTTP response
     */
    #[Route(
        '/recipe/',
        name: 'recipe_index',
        methods: 'GET'
    )]
    public function index(#[MapQueryParameter] int $page = 1): Response
    {
        $pagination = $this->recipeService->getPaginatedList($page);

        return $this->render('recipe/index.html.twig', ['pagination' => $pagination]);
    }

    /**
     * View action.
     *
     * @param Recipe $recipe Recipe entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/recipe/{id}',
        name: 'recipe_view',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET'
    )]
    public function view(Recipe $recipe): Response
    {
        return $this->render(
            'recipe/view.html.twig',
            ['recipe' => $recipe]
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
