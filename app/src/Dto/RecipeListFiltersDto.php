<?php

/**
 * Recipe list filters DTO.
 */

namespace App\Dto;

use App\Entity\Category;
use App\Entity\Tag;

/**
 * Class RecipeListFiltersDto.
 */
class RecipeListFiltersDto
{
    /**
     * Constructor.
     *
     * @param Category|null $category   Category entity
     * @param Tag|null      $tag        Tag entity
     * @param bool|null     $onlyMine   Filter to show only user's own recipes
     */
    public function __construct(public readonly ?Category $category, public readonly ?Tag $tag, public readonly ?bool $onlyMine = false)
    {
    }
}