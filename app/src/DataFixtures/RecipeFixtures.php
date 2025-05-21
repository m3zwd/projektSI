<?php

/**
 * Recipe fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Recipe;

/**
 * Class RecipeFixtures.
 */
class RecipeFixtures extends AbstractBaseFixtures
{
    /**
     * Load data.
     */
    public function loadData(): void
    {
        for ($i = 0; $i < 10; ++$i) {
            $task = new Recipe();
            $task->setTitle($this->faker->sentence);
            $task->setCreatedAt(
                \DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-100 days', '-1 days'))
            );
            $task->setUpdatedAt(
                \DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-100 days', '-1 days'))
            );
            $this->manager->persist($task);
        }

        $this->manager->flush();
    }
}
