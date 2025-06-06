<?php
/**
 * RecipeListInputFiltersDto resolver.
 */

namespace App\Resolver;

use App\Dto\RecipeListInputFiltersDto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * RecipeListInputFiltersDto class.
 */
class RecipeListInputFiltersDtoResolver implements ValueResolverInterface
{
    /**
     * Returns the possible value(s).
     *
     * @param Request          $request  HTTP Request
     * @param ArgumentMetadata $argument Argument metadata
     *
     * @return iterable Iterable
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $argumentType = $argument->getType();

        if (!$argumentType || !is_a($argumentType, RecipeListInputFiltersDto::class, true)) {
            return [];
        }

        $categoryId = $request->query->get('categoryId');
        $tagId = $request->query->get('tagId');
        $onlyMine = filter_var($request->query->get('onlyMine', false), FILTER_VALIDATE_BOOLEAN);

        return [new RecipeListInputFiltersDto($categoryId, $tagId, $onlyMine)];
    }
}
