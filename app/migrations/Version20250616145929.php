<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250616145929 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE ratings (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, recipe_id INT NOT NULL, value INT NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', average_rating DOUBLE PRECISION NOT NULL, INDEX IDX_CEB607C9F675F31B (author_id), INDEX IDX_CEB607C959D8A214 (recipe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ratings ADD CONSTRAINT FK_CEB607C9F675F31B FOREIGN KEY (author_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ratings ADD CONSTRAINT FK_CEB607C959D8A214 FOREIGN KEY (recipe_id) REFERENCES recipes (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE ratings DROP FOREIGN KEY FK_CEB607C9F675F31B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ratings DROP FOREIGN KEY FK_CEB607C959D8A214
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE ratings
        SQL);
    }
}
