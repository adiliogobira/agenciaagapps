<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260723232503 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE communion_seat (id INT AUTO_INCREMENT NOT NULL, table_id INT NOT NULL, member_id INT DEFAULT NULL, church_id INT NOT NULL, role VARCHAR(30) NOT NULL, person_name VARCHAR(150) NOT NULL, notes VARCHAR(200) DEFAULT NULL, INDEX IDX_870726BBECFF285C (table_id), INDEX IDX_870726BB7597D3FE (member_id), INDEX IDX_870726BBC1538FD4 (church_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE communion_table (id INT AUTO_INCREMENT NOT NULL, schedule_id INT NOT NULL, church_id INT NOT NULL, notes LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_EF17A3FA40BC2D5 (schedule_id), INDEX IDX_EF17A3FC1538FD4 (church_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE inventory_item (id INT AUTO_INCREMENT NOT NULL, church_id INT NOT NULL, name VARCHAR(120) NOT NULL, category VARCHAR(20) DEFAULT \'mantimento\' NOT NULL, unit VARCHAR(20) DEFAULT NULL, quantity NUMERIC(10, 2) DEFAULT \'0\' NOT NULL, min_quantity NUMERIC(10, 2) DEFAULT NULL, INDEX IDX_55BDEA30C1538FD4 (church_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE inventory_movement (id INT AUTO_INCREMENT NOT NULL, item_id INT NOT NULL, registered_by_id INT DEFAULT NULL, church_id INT NOT NULL, direction VARCHAR(10) NOT NULL, quantity NUMERIC(10, 2) NOT NULL, donor VARCHAR(120) DEFAULT NULL, notes VARCHAR(200) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_40972F66126F525E (item_id), INDEX IDX_40972F6627E92E18 (registered_by_id), INDEX IDX_40972F66C1538FD4 (church_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE member_ministry (member_id INT NOT NULL, ministry_id INT NOT NULL, INDEX IDX_7F41A6DB7597D3FE (member_id), INDEX IDX_7F41A6DBC7266135 (ministry_id), PRIMARY KEY(member_id, ministry_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, church_id INT NOT NULL, title VARCHAR(150) NOT NULL, message LONGTEXT NOT NULL, icon VARCHAR(40) DEFAULT \'bell\' NOT NULL, link VARCHAR(255) DEFAULT NULL, is_read TINYINT(1) DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_BF5476CAA76ED395 (user_id), INDEX IDX_BF5476CAC1538FD4 (church_id), INDEX idx_notif_user (user_id, is_read), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE communion_seat ADD CONSTRAINT FK_870726BBECFF285C FOREIGN KEY (table_id) REFERENCES communion_table (id)');
        $this->addSql('ALTER TABLE communion_seat ADD CONSTRAINT FK_870726BB7597D3FE FOREIGN KEY (member_id) REFERENCES `member` (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE communion_seat ADD CONSTRAINT FK_870726BBC1538FD4 FOREIGN KEY (church_id) REFERENCES church (id)');
        $this->addSql('ALTER TABLE communion_table ADD CONSTRAINT FK_EF17A3FA40BC2D5 FOREIGN KEY (schedule_id) REFERENCES schedule (id)');
        $this->addSql('ALTER TABLE communion_table ADD CONSTRAINT FK_EF17A3FC1538FD4 FOREIGN KEY (church_id) REFERENCES church (id)');
        $this->addSql('ALTER TABLE inventory_item ADD CONSTRAINT FK_55BDEA30C1538FD4 FOREIGN KEY (church_id) REFERENCES church (id)');
        $this->addSql('ALTER TABLE inventory_movement ADD CONSTRAINT FK_40972F66126F525E FOREIGN KEY (item_id) REFERENCES inventory_item (id)');
        $this->addSql('ALTER TABLE inventory_movement ADD CONSTRAINT FK_40972F6627E92E18 FOREIGN KEY (registered_by_id) REFERENCES app_user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE inventory_movement ADD CONSTRAINT FK_40972F66C1538FD4 FOREIGN KEY (church_id) REFERENCES church (id)');
        $this->addSql('ALTER TABLE member_ministry ADD CONSTRAINT FK_7F41A6DB7597D3FE FOREIGN KEY (member_id) REFERENCES `member` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE member_ministry ADD CONSTRAINT FK_7F41A6DBC7266135 FOREIGN KEY (ministry_id) REFERENCES ministry (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAC1538FD4 FOREIGN KEY (church_id) REFERENCES church (id)');
        $this->addSql('ALTER TABLE `member` DROP FOREIGN KEY FK_70E4FA78C7266135');
        $this->addSql('DROP INDEX IDX_70E4FA78C7266135 ON `member`');
        $this->addSql('ALTER TABLE `member` DROP ministry_id');
        $this->addSql('ALTER TABLE ministry ADD active TINYINT(1) DEFAULT 1 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE communion_seat DROP FOREIGN KEY FK_870726BBECFF285C');
        $this->addSql('ALTER TABLE communion_seat DROP FOREIGN KEY FK_870726BB7597D3FE');
        $this->addSql('ALTER TABLE communion_seat DROP FOREIGN KEY FK_870726BBC1538FD4');
        $this->addSql('ALTER TABLE communion_table DROP FOREIGN KEY FK_EF17A3FA40BC2D5');
        $this->addSql('ALTER TABLE communion_table DROP FOREIGN KEY FK_EF17A3FC1538FD4');
        $this->addSql('ALTER TABLE inventory_item DROP FOREIGN KEY FK_55BDEA30C1538FD4');
        $this->addSql('ALTER TABLE inventory_movement DROP FOREIGN KEY FK_40972F66126F525E');
        $this->addSql('ALTER TABLE inventory_movement DROP FOREIGN KEY FK_40972F6627E92E18');
        $this->addSql('ALTER TABLE inventory_movement DROP FOREIGN KEY FK_40972F66C1538FD4');
        $this->addSql('ALTER TABLE member_ministry DROP FOREIGN KEY FK_7F41A6DB7597D3FE');
        $this->addSql('ALTER TABLE member_ministry DROP FOREIGN KEY FK_7F41A6DBC7266135');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAC1538FD4');
        $this->addSql('DROP TABLE communion_seat');
        $this->addSql('DROP TABLE communion_table');
        $this->addSql('DROP TABLE inventory_item');
        $this->addSql('DROP TABLE inventory_movement');
        $this->addSql('DROP TABLE member_ministry');
        $this->addSql('DROP TABLE notification');
        $this->addSql('ALTER TABLE `member` ADD ministry_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE `member` ADD CONSTRAINT FK_70E4FA78C7266135 FOREIGN KEY (ministry_id) REFERENCES ministry (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_70E4FA78C7266135 ON `member` (ministry_id)');
        $this->addSql('ALTER TABLE ministry DROP active');
    }
}
