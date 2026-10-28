<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260722141540 extends AbstractMigration
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
        $this->addSql('CREATE TABLE member_ministry (member_id INT NOT NULL, ministry_id INT NOT NULL, INDEX IDX_7F41A6DB7597D3FE (member_id), INDEX IDX_7F41A6DBC7266135 (ministry_id), PRIMARY KEY(member_id, ministry_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE communion_seat ADD CONSTRAINT FK_870726BBECFF285C FOREIGN KEY (table_id) REFERENCES communion_table (id)');
        $this->addSql('ALTER TABLE communion_seat ADD CONSTRAINT FK_870726BB7597D3FE FOREIGN KEY (member_id) REFERENCES `member` (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE communion_seat ADD CONSTRAINT FK_870726BBC1538FD4 FOREIGN KEY (church_id) REFERENCES church (id)');
        $this->addSql('ALTER TABLE communion_table ADD CONSTRAINT FK_EF17A3FA40BC2D5 FOREIGN KEY (schedule_id) REFERENCES schedule (id)');
        $this->addSql('ALTER TABLE communion_table ADD CONSTRAINT FK_EF17A3FC1538FD4 FOREIGN KEY (church_id) REFERENCES church (id)');
        $this->addSql('ALTER TABLE member_ministry ADD CONSTRAINT FK_7F41A6DB7597D3FE FOREIGN KEY (member_id) REFERENCES `member` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE member_ministry ADD CONSTRAINT FK_7F41A6DBC7266135 FOREIGN KEY (ministry_id) REFERENCES ministry (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE inventory_item CHANGE quantity quantity NUMERIC(10, 2) DEFAULT \'0\' NOT NULL');
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
        $this->addSql('ALTER TABLE member_ministry DROP FOREIGN KEY FK_7F41A6DB7597D3FE');
        $this->addSql('ALTER TABLE member_ministry DROP FOREIGN KEY FK_7F41A6DBC7266135');
        $this->addSql('DROP TABLE communion_seat');
        $this->addSql('DROP TABLE communion_table');
        $this->addSql('DROP TABLE member_ministry');
        $this->addSql('ALTER TABLE inventory_item CHANGE quantity quantity NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL');
        $this->addSql('ALTER TABLE `member` ADD ministry_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE `member` ADD CONSTRAINT FK_70E4FA78C7266135 FOREIGN KEY (ministry_id) REFERENCES ministry (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_70E4FA78C7266135 ON `member` (ministry_id)');
        $this->addSql('ALTER TABLE ministry DROP active');
    }
}
