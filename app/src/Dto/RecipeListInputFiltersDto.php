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
     * @param array $categoryIds Category identifiers
     * @param array $tagIds      Tags identifier
     * @param bool  $onlyMine    User's recipes identifier
     */
    public function __construct(public readonly array $categoryIds = [], public readonly array $tagIds = [], public readonly bool $onlyMine = false)
    {
    }
}
