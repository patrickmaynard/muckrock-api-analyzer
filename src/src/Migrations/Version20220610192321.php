<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220610192321 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE IF NOT EXISTS ranking (id INT AUTO_INCREMENT NOT NULL, date DATETIME NOT NULL, cities LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE major_city CHANGE average_response_time average_response_time INT DEFAULT NULL, CHANGE success_rate success_rate DOUBLE PRECISION DEFAULT NULL, CHANGE last_update last_update DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE post CHANGE content content LONGTEXT NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE IF EXISTS ranking');
        $this->addSql('ALTER TABLE major_city CHANGE average_response_time average_response_time INT DEFAULT NULL, CHANGE success_rate success_rate DOUBLE PRECISION DEFAULT \'NULL\', CHANGE last_update last_update DATE DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE post CHANGE content content VARCHAR(5000) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
