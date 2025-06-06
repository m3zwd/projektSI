<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Recipe;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

/**
 * Class CommentFixtures.
 */
class CommentFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    public function loadData(): void
    {
        if (!$this->manager instanceof ObjectManager || !$this->faker instanceof Generator) {
            return;
        }

        $this->createMany(100, 'comment', function (int $i) {
            $comment = new Comment();
            $comment->setContent($this->faker->sentence($this->faker->numberBetween(5, 15)));
            $comment->setCreatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-90 days')
                )
            );

            $author = $this->getRandomReference('user', User::class);
            $comment->setAuthor($author);

            $recipe = $this->getRandomReference('recipe', Recipe::class);
            $comment->setRecipe($recipe);

            return $comment;
        });

        $this->manager->flush();
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class, RecipeFixtures::class];
    }
}
