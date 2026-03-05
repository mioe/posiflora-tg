<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260305095645 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE shop (name VARCHAR(255) NOT NULL, id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE telegram_integration (bot_token TEXT NOT NULL, chat_id VARCHAR(255) NOT NULL, enabled BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, id UUID NOT NULL, shop_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4D6BE5084D16C4DD ON telegram_integration (shop_id)');
        $this->addSql('ALTER TABLE telegram_integration ADD CONSTRAINT FK_4D6BE5084D16C4DD FOREIGN KEY (shop_id) REFERENCES shop (id) NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE telegram_integration DROP CONSTRAINT FK_4D6BE5084D16C4DD');
        $this->addSql('DROP TABLE shop');
        $this->addSql('DROP TABLE telegram_integration');
    }
}
