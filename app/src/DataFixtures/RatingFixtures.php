<?php

/**
 * Rating fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Rating;
use App\Entity\Recipe;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

/**
 * Class RatingFixtures.
 */
class RatingFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    /**
     * Load data.
     *
     * @psalm-suppress PossiblyNullPropertyFetch
     * @psalm-suppress PossiblyNullReference
     * @psalm-suppress UnusedClosureParam
     */
    public function loadData(): void
    {
        if (!$this->manager instanceof ObjectManager || !$this->faker instanceof Generator) {
            return;
        }

        /**
         * Zmienna do sprawdzania unikalności autora.
         * Ten sam użytkownik nie może ocenic jednego przepisu więcej niż raz.
         */
        $usedPairs = [];

        $this->createMany(50, 'rating', function (int $i) use (&$usedPairs) {
            $rating = new Rating();

            $author = $this->getRandomReference('user', User::class);
            $recipe = $this->getRandomReference('recipe', Recipe::class);

            /**
             * Sprawdzanie unikalności.
             * $pairKey to unikalny klucz, który zawiera email użytkownika i ID przepisu.
             * isset sprawdza, czy już istnieje taki klucz w $usedPairs.
             * jesli tak, to pomija duplikat - return null
             * jesli nie, to dodaje go do tablicy, żeby wiedzieć, że ta para już sie pojawiła
             */
            $pairKey = $author->getEmail() . '-' . $recipe->getId();

            if (isset($usedPairs[$pairKey])) {
                return null;
            }
            $usedPairs[$pairKey] = true;

            $rating->setValue($this->faker->numberBetween(1, 5));
            $rating->setCreatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-90 days')
                )
            );

            $rating->setAuthor($author);
            $rating->setRecipe($recipe);

            return $rating;
        });

        $this->manager->flush();
    }

    /**
     * Get fixture dependencies.
     *
     * @return string[] of dependencies
     */
    public function getDependencies(): array
    {
        return [UserFixtures::class, RecipeFixtures::class];
    }
}
