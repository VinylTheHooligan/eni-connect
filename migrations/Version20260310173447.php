<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260310173447 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE registration DROP FOREIGN KEY `FK_62A8A7A7AF4C7531`');
        $this->addSql('ALTER TABLE registration ADD CONSTRAINT FK_62A8A7A7AF4C7531 FOREIGN KEY (outing_id) REFERENCES outing (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE registration DROP FOREIGN KEY FK_62A8A7A7AF4C7531');
        $this->addSql('ALTER TABLE registration ADD CONSTRAINT `FK_62A8A7A7AF4C7531` FOREIGN KEY (outing_id) REFERENCES outing (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
