public function up(Schema $schema): void
{
    // FK peut ne pas exister → on évite le crash
    $this->addSql('SET @fk_exists := (
        SELECT COUNT(*)
        FROM information_schema.TABLE_CONSTRAINTS
        WHERE CONSTRAINT_NAME = "FK_C53D045F4584665A"
        AND TABLE_NAME = "image"
    )');

    $this->addSql('SET @sql := IF(@fk_exists > 0,
        "ALTER TABLE image DROP FOREIGN KEY FK_C53D045F4584665A",
        "SELECT 1"
    )');

    $this->addSql('PREPARE stmt FROM @sql');
    $this->addSql('EXECUTE stmt');
    $this->addSql('DEALLOCATE PREPARE stmt');

    // ⚠️ IMPORTANT : ne change PAS path → name si ça existe déjà
    // donc on supprime cette ligne si ta colonne est déjà "name"
    // $this->addSql('ALTER TABLE image CHANGE path name VARCHAR(255) NOT NULL');

    $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045F4584665A 
        FOREIGN KEY (product_id) REFERENCES product (id)');
}