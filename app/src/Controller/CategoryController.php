<?php

/**
 * Category controller.
 */

namespace App\Controller;

use App\Entity\Category;
use App\Service\CategoryServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Class CategoryController.
 */
class CategoryController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param CategoryServiceInterface $categoryService Category service
     */
    public function __construct(private readonly CategoryServiceInterface $categoryService)
    {
    }

    /**
     * Index action.
     *
     * @param int $page Page number
     *
     * @return Response HTTP Response
     */
    #[Route(
        '/category/',
        name: 'category_index',
        methods: 'GET'
    )]
    public function index(#[MapQueryParameter] int $page = 1): Response
    {
        $pagination = $this->categoryService->getPaginatedList($page);

        return $this->render('category/index.html.twig', ['pagination' => $pagination]);
    }

    /**
     * View action.
     *
     * @param Category $category Category entity
     *
     * @return Response HTTP Response
     */
    #[Route(
        '/category/{id}',
        name: 'category_view',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET'
    )]
    public function view(Category $category): Response
    {
        $recipes = $this->categoryService->getRecipesByCategory($category);

        return $this->render(
            'category/view.html.twig',
            [
                'category' => $category,
                'recipes' => $recipes,
            ]
        );
    }
}
