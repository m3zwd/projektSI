<?php

/**
 * Recipe controller.
 */

namespace App\Controller;

use App\Entity\Recipe;
use App\Service\RecipeService;
use App\Service\RecipeServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Class RecipeController.
 */
class RecipeController extends AbstractController
{
    /**
     * Constructor.
     */
    public function __construct(private readonly RecipeServiceInterface $recipeService)
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
}
