<?php

/**
 * Recipe fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Recipe;
use App\Entity\Tag;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

/**
 * Class RecipeFixtures.
 *
 * @psalm-suppress MissingConstructor
 */
class RecipeFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
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

        $this->createMany(100, 'recipe', function (int $i) {
            $recipe = new Recipe();
            $recipe->setTitle($this->faker->sentence);
            $recipe->setCreatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );
            $recipe->setUpdatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );
            $category = $this->getRandomReference('category', Category::class);
            $recipe->setCategory($category);

            /** @var Tag[] $tags */
            $tags = $this->getRandomReferenceList('tag', Tag::class, $this->faker->numberBetween(0, 5));
            foreach ($tags as $tag) {
                $recipe->addTag($tag);
            }

            /** @var User $author */
            $author = $this->getRandomReference('user', User::class);
            $recipe->setAuthor($author);

            return $recipe;
        });
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on.
     *
     * @return string[] of dependencies
     *
     * @psalm-return array{0: CategoryFixtures::class}
     */
    public function getDependencies(): array
    {
        return [CategoryFixtures::class, TagFixtures::class, UserFixtures::class];
    }
}
