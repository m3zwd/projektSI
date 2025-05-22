<?php

/**
 * Recipe controller.
 */

namespace App\Controller;

use App\Entity\Recipe;
use App\Repository\RecipeRepository;
use Knp\Component\Pager\PaginatorInterface;
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
     * Index action.
     *
     * @param RecipeRepository   $recipeRepository Recipe repository
     * @param PaginatorInterface $paginator        Paginator
     * @param int                $page             Default page number
     *
     * @return Response HTTP response
     */
    #[Route(
        name: 'recipe_index',
        methods: 'GET'
    )]
    public function index(RecipeRepository $recipeRepository, PaginatorInterface $paginator, #[MapQueryParameter] int $page = 1): Response
    {
        $pagination = $paginator->paginate(
            $recipeRepository->queryAll(),
            $page,
            RecipeRepository::PAGINATOR_ITEMS_PER_PAGE,
            [
                'sortFieldAllowList' => ['recipe.id', 'recipe.createdAt', 'recipe.updatedAt', 'recipe.title'],
                'defaultSortFieldName' => 'recipe.updatedAt',
                'defaultSortDirection' => 'desc',
            ]
        );

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
