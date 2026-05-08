<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260508130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix image table foreign key for product';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045F4584665A');
        $this->addSql('ALTER TABLE image 
            ADD CONSTRAINT FK_C53D045F4584665A 
            FOREIGN KEY (product_id) 
            REFERENCES product (id) 
            ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045F4584665A');
        $this->addSql('ALTER TABLE image 
            ADD INDEX IDX_C53D045F4584665A (product_id)
        ');
    }
}