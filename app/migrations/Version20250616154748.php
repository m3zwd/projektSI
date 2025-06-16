<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250616154748 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE ratings DROP average_rating
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_AUTHOR_RECIPE ON ratings (author_id, recipe_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recipes ADD average_rating DOUBLE PRECISION NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_AUTHOR_RECIPE ON ratings
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ratings ADD average_rating DOUBLE PRECISION NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recipes DROP average_rating
        SQL);
    }
}
