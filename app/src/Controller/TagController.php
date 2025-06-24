<?php

/**
 * Tag controller.
 */

namespace App\Controller;

use App\Entity\Tag;
use App\Service\TagServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class TagController.
 */
class TagController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param TagServiceInterface $tagService Tag service
     * @param TranslatorInterface $translator Translator
     */
    public function __construct(private readonly TagServiceInterface $tagService, private readonly TranslatorInterface $translator)
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
        '/tag/',
        name: 'tag_index',
        methods: 'GET'
    )]
    public function index(#[MapQueryParameter] int $page = 1): Response
    {
        $pagination = $this->tagService->getPaginatedList($page);

        return $this->render('tag/index.html.twig', ['pagination' => $pagination]);
    }

    /**
     * View action.
     *
     * @param Tag $tag Tag entity
     *
     * @return Response HTTP Response
     */
    #[Route(
        '/tag/{id}',
        name: 'tag_view',
        requirements: ['id' => '\d+'],
        methods: 'GET'
    )]
    public function view(Tag $tag): Response
    {
        $recipes = $this->tagService->getRecipesByTag($tag);

        return $this->render('tag/view.html.twig', [
            'tag' => $tag,
            'recipes' => $recipes,
        ]);
    }
}
