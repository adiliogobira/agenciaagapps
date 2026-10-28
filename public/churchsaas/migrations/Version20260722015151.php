<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260722015151 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE service_slot (id INT AUTO_INCREMENT NOT NULL, schedule_id INT NOT NULL, deacon_id INT DEFAULT NULL, church_id INT NOT NULL, activity VARCHAR(60) NOT NULL, notes VARCHAR(150) DEFAULT NULL, accepted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', status VARCHAR(20) DEFAULT \'aberta\' NOT NULL, INDEX IDX_624D948BA40BC2D5 (schedule_id), INDEX IDX_624D948B85B72CF2 (deacon_id), INDEX IDX_624D948BC1538FD4 (church_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE slot_withdrawal (id INT AUTO_INCREMENT NOT NULL, slot_id INT NOT NULL, deacon_id INT DEFAULT NULL, church_id INT NOT NULL, deacon_name VARCHAR(150) NOT NULL, reason LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_DF4620759E5119C (slot_id), INDEX IDX_DF4620785B72CF2 (deacon_id), INDEX IDX_DF46207C1538FD4 (church_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE service_slot ADD CONSTRAINT FK_624D948BA40BC2D5 FOREIGN KEY (schedule_id) REFERENCES schedule (id)');
        $this->addSql('ALTER TABLE service_slot ADD CONSTRAINT FK_624D948B85B72CF2 FOREIGN KEY (deacon_id) REFERENCES deacon (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE service_slot ADD CONSTRAINT FK_624D948BC1538FD4 FOREIGN KEY (church_id) REFERENCES church (id)');
        $this->addSql('ALTER TABLE slot_withdrawal ADD CONSTRAINT FK_DF4620759E5119C FOREIGN KEY (slot_id) REFERENCES service_slot (id)');
        $this->addSql('ALTER TABLE slot_withdrawal ADD CONSTRAINT FK_DF4620785B72CF2 FOREIGN KEY (deacon_id) REFERENCES deacon (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE slot_withdrawal ADD CONSTRAINT FK_DF46207C1538FD4 FOREIGN KEY (church_id) REFERENCES church (id)');
        $this->addSql('ALTER TABLE deacon ADD leader TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE service_slot DROP FOREIGN KEY FK_624D948BA40BC2D5');
        $this->addSql('ALTER TABLE service_slot DROP FOREIGN KEY FK_624D948B85B72CF2');
        $this->addSql('ALTER TABLE service_slot DROP FOREIGN KEY FK_624D948BC1538FD4');
        $this->addSql('ALTER TABLE slot_withdrawal DROP FOREIGN KEY FK_DF4620759E5119C');
        $this->addSql('ALTER TABLE slot_withdrawal DROP FOREIGN KEY FK_DF4620785B72CF2');
        $this->addSql('ALTER TABLE slot_withdrawal DROP FOREIGN KEY FK_DF46207C1538FD4');
        $this->addSql('DROP TABLE service_slot');
        $this->addSql('DROP TABLE slot_withdrawal');
        $this->addSql('ALTER TABLE deacon DROP leader');
    }
}
