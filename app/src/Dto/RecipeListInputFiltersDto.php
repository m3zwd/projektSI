<?php

/**
 * Recipe list input filters DTO.
 */

namespace App\Dto;

/**
 * Class RecipeListInputFiltersDto.
 */
class RecipeListInputFiltersDto
{
    /**
     * Constructor.
     *
     * @param int|null $categoryId Category identifier
     * @param array    $tagIds     Tags identifier
     * @param bool     $onlyMine   User's recipes identifier
     */
    public function __construct(public readonly ?int $categoryId = null, public readonly array $tagIds = [], public readonly bool $onlyMine = false)
    {
    }
}
