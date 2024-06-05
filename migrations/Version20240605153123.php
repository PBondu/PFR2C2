<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240605153123 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contract CHANGE sign_datetime sign_datetime DATETIME DEFAULT NULL, CHANGE locbegin_datetime locbegin_datetime DATETIME DEFAULT NULL, CHANGE locend_datetime locend_datetime DATETIME DEFAULT NULL, CHANGE returning_datetime returning_datetime DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contract CHANGE sign_datetime sign_datetime DATETIME NOT NULL, CHANGE locbegin_datetime locbegin_datetime DATETIME NOT NULL, CHANGE locend_datetime locend_datetime DATETIME NOT NULL, CHANGE returning_datetime returning_datetime DATETIME NOT NULL');
    }
}
