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

        /*
        $categoryId = $request->query->get('categoryId');
        $tagId = $request->query->get('tagId');
        $onlyMine = filter_var($request->query->get('onlyMine', false), FILTER_VALIDATE_BOOLEAN);

        return [new RecipeListInputFiltersDto($categoryId, $tagId, $onlyMine)];

        błąd: Argument #1 ($categoryId) must be of type ?int, string given, in RecipeListInputFiltersDtoResolver.php on line 38
        i na odwrót z tagId
        */

        // raw - wartość pobrana bezpośrednio z requestu, surowa jeszcze niezmieniona, moze byc int string null itd
        $categoryIdRaw = $request->query->get('categoryId');
        //$tagIdRaw = $request->query->get('tagId');
        $tagIdsRaw = $request->query->all('tags'); // tablica tagów zamiast pojedynczego tagu
        $onlyMineRaw = $request->query->get('onlyMine', false);

        // konwersja na ?int
        // jesli $categoryIdRaw jest liczbą, to wartosc jest rzutowana na typ całkowity
        $categoryId = is_numeric($categoryIdRaw) ? (int) $categoryIdRaw : null;

        // filtrowanie tylko numerycznych tagów
        $tagIds = array_filter($tagIdsRaw, fn($id) => is_numeric($id));
        $tagIds = array_map('intval', $tagIds);

        // konwersja na bool
        $onlyMine = filter_var($onlyMineRaw, FILTER_VALIDATE_BOOLEAN);

        /*
        symfony wymaga, żeby metoda resolve() zwracala iterowalny wynik np. tablice.
        yield zwraca pojedynczy, iterowalny element bez tworzenia całej tablicy.
        tutaj lepszy niz return, bo zwracamy tylko 1 obiekt
        */
        yield new RecipeListInputFiltersDto($categoryId, $tagIds, $onlyMine);
    }
}
