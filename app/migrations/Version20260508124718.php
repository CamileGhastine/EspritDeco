<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260508124718 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create cart and cart_line tables + relations';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE cart (
            id INT AUTO_INCREMENT NOT NULL,
            created_at DATETIME NOT NULL,
            status VARCHAR(255) NOT NULL,
            user_id INT DEFAULT NULL,
            UNIQUE INDEX UNIQ_BA388B7A76ED395 (user_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4');

        $this->addSql('CREATE TABLE cart_line (
            id INT AUTO_INCREMENT NOT NULL,
            quantity INT NOT NULL,
            unit_price DOUBLE PRECISION NOT NULL,
            cart_id INT NOT NULL,
            product_id INT NOT NULL,
            INDEX IDX_3EF1B4CF1AD5CDBF (cart_id),
            INDEX IDX_3EF1B4CF4584665A (product_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4');

        $this->addSql('ALTER TABLE cart 
            ADD CONSTRAINT FK_BA388B7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');

        $this->addSql('ALTER TABLE cart_line 
            ADD CONSTRAINT FK_3EF1B4CF1AD5CDBF FOREIGN KEY (cart_id) REFERENCES cart (id)');

        $this->addSql('ALTER TABLE cart_line 
            ADD CONSTRAINT FK_3EF1B4CF4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cart DROP FOREIGN KEY FK_BA388B7A76ED395');
        $this->addSql('ALTER TABLE cart_line DROP FOREIGN KEY FK_3EF1B4CF1AD5CDBF');
        $this->addSql('ALTER TABLE cart_line DROP FOREIGN KEY FK_3EF1B4CF4584665A');

        $this->addSql('DROP TABLE cart');
        $this->addSql('DROP TABLE cart_line');
    }
}