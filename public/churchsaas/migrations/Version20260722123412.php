<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260722123412 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE announcement (id INT AUTO_INCREMENT NOT NULL, church_id INT NOT NULL, title VARCHAR(150) NOT NULL, body LONGTEXT NOT NULL, channel VARCHAR(20) DEFAULT \'mural\' NOT NULL, audience VARCHAR(20) DEFAULT \'todos\' NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', sent_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_4DB9D91CC1538FD4 (church_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_user (id INT AUTO_INCREMENT NOT NULL, church_id INT NOT NULL, member_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, full_name VARCHAR(120) NOT NULL, active TINYINT(1) DEFAULT 1 NOT NULL, INDEX IDX_88BDF3E9C1538FD4 (church_id), UNIQUE INDEX UNIQ_88BDF3E97597D3FE (member_id), UNIQUE INDEX uniq_user_email (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE campaign (id INT AUTO_INCREMENT NOT NULL, church_id INT NOT NULL, name VARCHAR(150) NOT NULL, goal_amount NUMERIC(12, 2) NOT NULL, start_date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', end_date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', active TINYINT(1) DEFAULT 1 NOT NULL, INDEX IDX_1F1512DDC1538FD4 (church_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE church (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(180) NOT NULL, slug VARCHAR(100) NOT NULL, cnpj VARCHAR(20) DEFAULT NULL, phone VARCHAR(30) DEFAULT NULL, email VARCHAR(180) DEFAULT NULL, address LONGTEXT DEFAULT NULL, plan VARCHAR(20) DEFAULT \'basic\' NOT NULL, active TINYINT(1) DEFAULT 1 NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_90CDDD45989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE class_attendance (id INT AUTO_INCREMENT NOT NULL, school_class_id INT NOT NULL, student_id INT NOT NULL, church_id INT NOT NULL, date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', present TINYINT(1) DEFAULT 1 NOT NULL, INDEX IDX_DBEEFF3314463F54 (school_class_id), INDEX IDX_DBEEFF33CB944F1A (student_id), INDEX IDX_DBEEFF33C1538FD4 (church_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE deacon (id INT AUTO_INCREMENT NOT NULL, member_id INT NOT NULL, church_id INT NOT NULL, ordination_date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', areas JSON DEFAULT NULL, active TINYINT(1) DEFAULT 1 NOT NULL, leader TINYINT(1) DEFAULT 0 NOT NULL, UNIQUE INDEX UNIQ_F211CAFA7597D3FE (member_id), INDEX IDX_F211CAFAC1538FD4 (church_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, church_id INT NOT NULL, type VARCHAR(30) NOT NULL, name VARCHAR(150) NOT NULL, starts_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ends_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', location VARCHAR(200) DEFAULT NULL, fee NUMERIC(10, 2) DEFAULT NULL, capacity INT DEFAULT NULL, INDEX IDX_3BAE0AA7C1538FD4 (church_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event_registration (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, member_id INT DEFAULT NULL, church_id INT NOT NULL, participant_name VARCHAR(150) NOT NULL, payment_status VARCHAR(20) DEFAULT \'pendente\' NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_8FBBAD5471F7E88B (event_id), INDEX IDX_8FBBAD547597D3FE (member_id), INDEX IDX_8FBBAD54C1538FD4 (church_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE financial_category (id INT AUTO_INCREMENT NOT NULL, church_id INT NOT NULL, name VARCHAR(100) NOT NULL, direction VARCHAR(10) NOT NULL, INDEX IDX_AB7952C5C1538FD4 (church_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE inventory_item (id INT AUTO_INCREMENT NOT NULL, church_id INT NOT NULL, name VARCHAR(120) NOT NULL, category VARCHAR(20) DEFAULT \'mantimento\' NOT NULL, unit VARCHAR(20) DEFAULT NULL, quantity NUMERIC(10, 2) DEFAULT \'0\' NOT NULL, min_quantity NUMERIC(10, 2) DEFAULT NULL, INDEX IDX_55BDEA30C1538FD4 (church_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE inventory_movement (id INT AUTO_INCREMENT NOT NULL, item_id INT NOT NULL, registered_by_id INT DEFAULT NULL, church_id INT NOT NULL, direction VARCHAR(10) NOT NULL, quantity NUMERIC(10, 2) NOT NULL, donor VARCHAR(120) DEFAULT NULL, notes VARCHAR(200) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_40972F66126F525E (item_id), INDEX IDX_40972F6627E92E18 (registered_by_id), INDEX IDX_40972F66C1538FD4 (church_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `member` (id INT AUTO_INCREMENT NOT NULL, ministry_id INT DEFAULT NULL, church_id INT NOT NULL, full_name VARCHAR(150) NOT NULL, cpf VARCHAR(14) DEFAULT NULL, rg VARCHAR(20) DEFAULT NULL, birth_date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', gender VARCHAR(20) DEFAULT NULL, marital_status VARCHAR(20) DEFAULT NULL, phone VARCHAR(30) DEFAULT NULL, email VARCHAR(180) DEFAULT NULL, address LONGTEXT DEFAULT NULL, baptism_date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', membership_date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', entry_type VARCHAR(30) DEFAULT NULL, church_role VARCHAR(40) DEFAULT \'membro\' NOT NULL, status VARCHAR(20) DEFAULT \'ativo\' NOT NULL, photo_path VARCHAR(255) DEFAULT NULL, documents JSON DEFAULT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_70E4FA78C7266135 (ministry_id), INDEX IDX_70E4FA78C1538FD4 (church_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ministry (id INT AUTO_INCREMENT NOT NULL, leader_id INT DEFAULT NULL, church_id INT NOT NULL, name VARCHAR(120) NOT NULL, description LONGTEXT DEFAULT NULL, INDEX IDX_889C053173154ED4 (leader_id), INDEX IDX_889C0531C1538FD4 (church_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, church_id INT NOT NULL, title VARCHAR(150) NOT NULL, message LONGTEXT NOT NULL, icon VARCHAR(40) DEFAULT \'bell\' NOT NULL, link VARCHAR(255) DEFAULT NULL, is_read TINYINT(1) DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_BF5476CAA76ED395 (user_id), INDEX IDX_BF5476CAC1538FD4 (church_id), INDEX idx_notif_user (user_id, is_read), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pastoral_appointment (id INT AUTO_INCREMENT NOT NULL, member_id INT DEFAULT NULL, pastor_id INT DEFAULT NULL, church_id INT NOT NULL, type VARCHAR(30) NOT NULL, scheduled_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', subject VARCHAR(200) DEFAULT NULL, confidential_notes LONGTEXT DEFAULT NULL, status VARCHAR(20) DEFAULT \'agendado\' NOT NULL, INDEX IDX_F917AE87597D3FE (member_id), INDEX IDX_F917AE848C6696 (pastor_id), INDEX IDX_F917AE8C1538FD4 (church_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE prayer_request (id INT AUTO_INCREMENT NOT NULL, church_id INT NOT NULL, requester_name VARCHAR(150) NOT NULL, request LONGTEXT NOT NULL, confidential TINYINT(1) DEFAULT 0 NOT NULL, answered TINYINT(1) DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_1482DAB0C1538FD4 (church_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE schedule (id INT AUTO_INCREMENT NOT NULL, church_id INT NOT NULL, type VARCHAR(30) NOT NULL, title VARCHAR(150) NOT NULL, scheduled_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', location VARCHAR(150) DEFAULT NULL, notes LONGTEXT DEFAULT NULL, INDEX IDX_5A3811FBC1538FD4 (church_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE schedule_assignment (id INT AUTO_INCREMENT NOT NULL, schedule_id INT NOT NULL, deacon_id INT NOT NULL, church_id INT NOT NULL, position VARCHAR(80) DEFAULT NULL, presence VARCHAR(20) DEFAULT \'escalado\' NOT NULL, INDEX IDX_600F33F8A40BC2D5 (schedule_id), INDEX IDX_600F33F885B72CF2 (deacon_id), INDEX IDX_600F33F8C1538FD4 (church_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE school_class (id INT AUTO_INCREMENT NOT NULL, teacher_id INT DEFAULT NULL, church_id INT NOT NULL, name VARCHAR(120) NOT NULL, age_group VARCHAR(80) DEFAULT NULL, INDEX IDX_33B1AF8541807E1D (teacher_id), INDEX IDX_33B1AF85C1538FD4 (church_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE service_slot (id INT AUTO_INCREMENT NOT NULL, schedule_id INT NOT NULL, deacon_id INT DEFAULT NULL, church_id INT NOT NULL, activity VARCHAR(60) NOT NULL, notes VARCHAR(150) DEFAULT NULL, accepted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', status VARCHAR(20) DEFAULT \'aberta\' NOT NULL, INDEX IDX_624D948BA40BC2D5 (schedule_id), INDEX IDX_624D948B85B72CF2 (deacon_id), INDEX IDX_624D948BC1538FD4 (church_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE slot_withdrawal (id INT AUTO_INCREMENT NOT NULL, slot_id INT NOT NULL, deacon_id INT DEFAULT NULL, church_id INT NOT NULL, deacon_name VARCHAR(150) NOT NULL, reason LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_DF4620759E5119C (slot_id), INDEX IDX_DF4620785B72CF2 (deacon_id), INDEX IDX_DF46207C1538FD4 (church_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE student (id INT AUTO_INCREMENT NOT NULL, member_id INT DEFAULT NULL, church_id INT NOT NULL, full_name VARCHAR(150) NOT NULL, INDEX IDX_B723AF337597D3FE (member_id), INDEX IDX_B723AF33C1538FD4 (church_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE student_class (student_id INT NOT NULL, school_class_id INT NOT NULL, INDEX IDX_657C6002CB944F1A (student_id), INDEX IDX_657C600214463F54 (school_class_id), PRIMARY KEY(student_id, school_class_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transaction (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, member_id INT DEFAULT NULL, campaign_id INT DEFAULT NULL, church_id INT NOT NULL, direction VARCHAR(10) NOT NULL, kind VARCHAR(20) NOT NULL, amount NUMERIC(12, 2) NOT NULL, occurred_at DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', description VARCHAR(200) DEFAULT NULL, payment_method VARCHAR(20) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_723705D112469DE2 (category_id), INDEX IDX_723705D17597D3FE (member_id), INDEX IDX_723705D1F639F774 (campaign_id), INDEX IDX_723705D1C1538FD4 (church_id), INDEX idx_tx_date (occurred_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE visitor (id INT AUTO_INCREMENT NOT NULL, invited_by_id INT DEFAULT NULL, converted_member_id INT DEFAULT NULL, church_id INT NOT NULL, full_name VARCHAR(150) NOT NULL, phone VARCHAR(30) DEFAULT NULL, email VARCHAR(180) DEFAULT NULL, type VARCHAR(20) DEFAULT \'visitante\' NOT NULL, first_visit_date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', integration_stage VARCHAR(30) DEFAULT \'novo\' NOT NULL, visit_count INT DEFAULT 1 NOT NULL, notes LONGTEXT DEFAULT NULL, INDEX IDX_CAE5E19FA7B4A7E3 (invited_by_id), UNIQUE INDEX UNIQ_CAE5E19F36D8B202 (converted_member_id), INDEX IDX_CAE5E19FC1538FD4 (church_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE announcement ADD CONSTRAINT FK_4DB9D91CC1538FD4 FOREIGN KEY (church_id) REFERENCES church (id)');
        $this->addSql('ALTER TABLE app_user ADD CONSTRAINT FK_88BDF3E9C1538FD4 FOREIGN KEY (church_id) REFERENCES church (id)');
        $this->addSql('ALTER TABLE app_user ADD CONSTRAINT FK_88BDF3E97597D3FE FOREIGN KEY (member_id) REFERENCES `member` (id)');
        $this->addSql('ALTER TABLE campaign ADD CONSTRAINT FK_1F1512DDC1538FD4 FOREIGN KEY (church_id) REFERENCES church (id)');
        $this->addSql('ALTER TABLE class_attendance ADD CONSTRAINT FK_DBEEFF3314463F54 FOREIGN KEY (school_class_id) REFERENCES school_class (id)');
        $this->addSql('ALTER TABLE class_attendance ADD CONSTRAINT FK_DBEEFF33CB944F1A FOREIGN KEY (student_id) REFERENCES student (id)');
        $this->addSql('ALTER TABLE class_attendance ADD CONSTRAINT FK_DBEEFF33C1538FD4 FOREIGN KEY (church_id) REFERENCES church (id)');
        $this->addSql('ALTER TABLE deacon ADD CONSTRAINT FK_F211CAFA7597D3FE FOREIGN KEY (member_id) REFERENCES `member` (id)');
        $this->addSql('ALTER TABLE deacon ADD CONSTRAINT FK_F211CAFAC1538FD4 FOREIGN KEY (church_id) REFERENCES church (id)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7C1538FD4 FOREIGN KEY (church_id) REFERENCES church (id)');
        $this->addSql('ALTER TABLE event_registration ADD CONSTRAINT FK_8FBBAD5471F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE event_registration ADD CONSTRAINT FK_8FBBAD547597D3FE FOREIGN KEY (member_id) REFERENCES `member` (id)');
        $this->addSql('ALTER TABLE event_registration ADD CONSTRAINT FK_8FBBAD54C1538FD4 FOREIGN KEY (church_id) REFERENCES church (id)');
        $this->addSql('ALTER TABLE financial_category ADD CONSTRAINT FK_AB7952C5C1538FD4 FOREIGN KEY (church_id) REFERENCES church (id)');
        $this->addSql('ALTER TABLE inventory_item ADD CONSTRAINT FK_55BDEA30C1538FD4 FOREIGN KEY (church_id) REFERENCES church (id)');
        $this->addSql('ALTER TABLE inventory_movement ADD CONSTRAINT FK_40972F66126F525E FOREIGN KEY (item_id) REFERENCES inventory_item (id)');
        $this->addSql('ALTER TABLE inventory_movement ADD CONSTRAINT FK_40972F6627E92E18 FOREIGN KEY (registered_by_id) REFERENCES app_user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE inventory_movement ADD CONSTRAINT FK_40972F66C1538FD4 FOREIGN KEY (church_id) REFERENCES church (id)');
        $this->addSql('ALTER TABLE `member` ADD CONSTRAINT FK_70E4FA78C7266135 FOREIGN KEY (ministry_id) REFERENCES ministry (id)');
        $this->addSql('ALTER TABLE `member` ADD CONSTRAINT FK_70E4FA78C1538FD4 FOREIGN KEY (church_id) REFERENCES church (id)');
        $this->addSql('ALTER TABLE ministry ADD CONSTRAINT FK_889C053173154ED4 FOREIGN KEY (leader_id) REFERENCES `member` (id)');
        $this->addSql('ALTER TABLE ministry ADD CONSTRAINT FK_889C0531C1538FD4 FOREIGN KEY (church_id) REFERENCES church (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAC1538FD4 FOREIGN KEY (church_id) REFERENCES church (id)');
        $this->addSql('ALTER TABLE pastoral_appointment ADD CONSTRAINT FK_F917AE87597D3FE FOREIGN KEY (member_id) REFERENCES `member` (id)');
        $this->addSql('ALTER TABLE pastoral_appointment ADD CONSTRAINT FK_F917AE848C6696 FOREIGN KEY (pastor_id) REFERENCES app_user (id)');
        $this->addSql('ALTER TABLE pastoral_appointment ADD CONSTRAINT FK_F917AE8C1538FD4 FOREIGN KEY (church_id) REFERENCES church (id)');
        $this->addSql('ALTER TABLE prayer_request ADD CONSTRAINT FK_1482DAB0C1538FD4 FOREIGN KEY (church_id) REFERENCES church (id)');
        $this->addSql('ALTER TABLE schedule ADD CONSTRAINT FK_5A3811FBC1538FD4 FOREIGN KEY (church_id) REFERENCES church (id)');
        $this->addSql('ALTER TABLE schedule_assignment ADD CONSTRAINT FK_600F33F8A40BC2D5 FOREIGN KEY (schedule_id) REFERENCES schedule (id)');
        $this->addSql('ALTER TABLE schedule_assignment ADD CONSTRAINT FK_600F33F885B72CF2 FOREIGN KEY (deacon_id) REFERENCES deacon (id)');
        $this->addSql('ALTER TABLE schedule_assignment ADD CONSTRAINT FK_600F33F8C1538FD4 FOREIGN KEY (church_id) REFERENCES church (id)');
        $this->addSql('ALTER TABLE school_class ADD CONSTRAINT FK_33B1AF8541807E1D FOREIGN KEY (teacher_id) REFERENCES `member` (id)');
        $this->addSql('ALTER TABLE school_class ADD CONSTRAINT FK_33B1AF85C1538FD4 FOREIGN KEY (church_id) REFERENCES church (id)');
        $this->addSql('ALTER TABLE service_slot ADD CONSTRAINT FK_624D948BA40BC2D5 FOREIGN KEY (schedule_id) REFERENCES schedule (id)');
        $this->addSql('ALTER TABLE service_slot ADD CONSTRAINT FK_624D948B85B72CF2 FOREIGN KEY (deacon_id) REFERENCES deacon (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE service_slot ADD CONSTRAINT FK_624D948BC1538FD4 FOREIGN KEY (church_id) REFERENCES church (id)');
        $this->addSql('ALTER TABLE slot_withdrawal ADD CONSTRAINT FK_DF4620759E5119C FOREIGN KEY (slot_id) REFERENCES service_slot (id)');
        $this->addSql('ALTER TABLE slot_withdrawal ADD CONSTRAINT FK_DF4620785B72CF2 FOREIGN KEY (deacon_id) REFERENCES deacon (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE slot_withdrawal ADD CONSTRAINT FK_DF46207C1538FD4 FOREIGN KEY (church_id) REFERENCES church (id)');
        $this->addSql('ALTER TABLE student ADD CONSTRAINT FK_B723AF337597D3FE FOREIGN KEY (member_id) REFERENCES `member` (id)');
        $this->addSql('ALTER TABLE student ADD CONSTRAINT FK_B723AF33C1538FD4 FOREIGN KEY (church_id) REFERENCES church (id)');
        $this->addSql('ALTER TABLE student_class ADD CONSTRAINT FK_657C6002CB944F1A FOREIGN KEY (student_id) REFERENCES student (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE student_class ADD CONSTRAINT FK_657C600214463F54 FOREIGN KEY (school_class_id) REFERENCES school_class (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D112469DE2 FOREIGN KEY (category_id) REFERENCES financial_category (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D17597D3FE FOREIGN KEY (member_id) REFERENCES `member` (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1F639F774 FOREIGN KEY (campaign_id) REFERENCES campaign (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1C1538FD4 FOREIGN KEY (church_id) REFERENCES church (id)');
        $this->addSql('ALTER TABLE visitor ADD CONSTRAINT FK_CAE5E19FA7B4A7E3 FOREIGN KEY (invited_by_id) REFERENCES `member` (id)');
        $this->addSql('ALTER TABLE visitor ADD CONSTRAINT FK_CAE5E19F36D8B202 FOREIGN KEY (converted_member_id) REFERENCES `member` (id)');
        $this->addSql('ALTER TABLE visitor ADD CONSTRAINT FK_CAE5E19FC1538FD4 FOREIGN KEY (church_id) REFERENCES church (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE announcement DROP FOREIGN KEY FK_4DB9D91CC1538FD4');
        $this->addSql('ALTER TABLE app_user DROP FOREIGN KEY FK_88BDF3E9C1538FD4');
        $this->addSql('ALTER TABLE app_user DROP FOREIGN KEY FK_88BDF3E97597D3FE');
        $this->addSql('ALTER TABLE campaign DROP FOREIGN KEY FK_1F1512DDC1538FD4');
        $this->addSql('ALTER TABLE class_attendance DROP FOREIGN KEY FK_DBEEFF3314463F54');
        $this->addSql('ALTER TABLE class_attendance DROP FOREIGN KEY FK_DBEEFF33CB944F1A');
        $this->addSql('ALTER TABLE class_attendance DROP FOREIGN KEY FK_DBEEFF33C1538FD4');
        $this->addSql('ALTER TABLE deacon DROP FOREIGN KEY FK_F211CAFA7597D3FE');
        $this->addSql('ALTER TABLE deacon DROP FOREIGN KEY FK_F211CAFAC1538FD4');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7C1538FD4');
        $this->addSql('ALTER TABLE event_registration DROP FOREIGN KEY FK_8FBBAD5471F7E88B');
        $this->addSql('ALTER TABLE event_registration DROP FOREIGN KEY FK_8FBBAD547597D3FE');
        $this->addSql('ALTER TABLE event_registration DROP FOREIGN KEY FK_8FBBAD54C1538FD4');
        $this->addSql('ALTER TABLE financial_category DROP FOREIGN KEY FK_AB7952C5C1538FD4');
        $this->addSql('ALTER TABLE inventory_item DROP FOREIGN KEY FK_55BDEA30C1538FD4');
        $this->addSql('ALTER TABLE inventory_movement DROP FOREIGN KEY FK_40972F66126F525E');
        $this->addSql('ALTER TABLE inventory_movement DROP FOREIGN KEY FK_40972F6627E92E18');
        $this->addSql('ALTER TABLE inventory_movement DROP FOREIGN KEY FK_40972F66C1538FD4');
        $this->addSql('ALTER TABLE `member` DROP FOREIGN KEY FK_70E4FA78C7266135');
        $this->addSql('ALTER TABLE `member` DROP FOREIGN KEY FK_70E4FA78C1538FD4');
        $this->addSql('ALTER TABLE ministry DROP FOREIGN KEY FK_889C053173154ED4');
        $this->addSql('ALTER TABLE ministry DROP FOREIGN KEY FK_889C0531C1538FD4');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAC1538FD4');
        $this->addSql('ALTER TABLE pastoral_appointment DROP FOREIGN KEY FK_F917AE87597D3FE');
        $this->addSql('ALTER TABLE pastoral_appointment DROP FOREIGN KEY FK_F917AE848C6696');
        $this->addSql('ALTER TABLE pastoral_appointment DROP FOREIGN KEY FK_F917AE8C1538FD4');
        $this->addSql('ALTER TABLE prayer_request DROP FOREIGN KEY FK_1482DAB0C1538FD4');
        $this->addSql('ALTER TABLE schedule DROP FOREIGN KEY FK_5A3811FBC1538FD4');
        $this->addSql('ALTER TABLE schedule_assignment DROP FOREIGN KEY FK_600F33F8A40BC2D5');
        $this->addSql('ALTER TABLE schedule_assignment DROP FOREIGN KEY FK_600F33F885B72CF2');
        $this->addSql('ALTER TABLE schedule_assignment DROP FOREIGN KEY FK_600F33F8C1538FD4');
        $this->addSql('ALTER TABLE school_class DROP FOREIGN KEY FK_33B1AF8541807E1D');
        $this->addSql('ALTER TABLE school_class DROP FOREIGN KEY FK_33B1AF85C1538FD4');
        $this->addSql('ALTER TABLE service_slot DROP FOREIGN KEY FK_624D948BA40BC2D5');
        $this->addSql('ALTER TABLE service_slot DROP FOREIGN KEY FK_624D948B85B72CF2');
        $this->addSql('ALTER TABLE service_slot DROP FOREIGN KEY FK_624D948BC1538FD4');
        $this->addSql('ALTER TABLE slot_withdrawal DROP FOREIGN KEY FK_DF4620759E5119C');
        $this->addSql('ALTER TABLE slot_withdrawal DROP FOREIGN KEY FK_DF4620785B72CF2');
        $this->addSql('ALTER TABLE slot_withdrawal DROP FOREIGN KEY FK_DF46207C1538FD4');
        $this->addSql('ALTER TABLE student DROP FOREIGN KEY FK_B723AF337597D3FE');
        $this->addSql('ALTER TABLE student DROP FOREIGN KEY FK_B723AF33C1538FD4');
        $this->addSql('ALTER TABLE student_class DROP FOREIGN KEY FK_657C6002CB944F1A');
        $this->addSql('ALTER TABLE student_class DROP FOREIGN KEY FK_657C600214463F54');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D112469DE2');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D17597D3FE');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1F639F774');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1C1538FD4');
        $this->addSql('ALTER TABLE visitor DROP FOREIGN KEY FK_CAE5E19FA7B4A7E3');
        $this->addSql('ALTER TABLE visitor DROP FOREIGN KEY FK_CAE5E19F36D8B202');
        $this->addSql('ALTER TABLE visitor DROP FOREIGN KEY FK_CAE5E19FC1538FD4');
        $this->addSql('DROP TABLE announcement');
        $this->addSql('DROP TABLE app_user');
        $this->addSql('DROP TABLE campaign');
        $this->addSql('DROP TABLE church');
        $this->addSql('DROP TABLE class_attendance');
        $this->addSql('DROP TABLE deacon');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE event_registration');
        $this->addSql('DROP TABLE financial_category');
        $this->addSql('DROP TABLE inventory_item');
        $this->addSql('DROP TABLE inventory_movement');
        $this->addSql('DROP TABLE `member`');
        $this->addSql('DROP TABLE ministry');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE pastoral_appointment');
        $this->addSql('DROP TABLE prayer_request');
        $this->addSql('DROP TABLE schedule');
        $this->addSql('DROP TABLE schedule_assignment');
        $this->addSql('DROP TABLE school_class');
        $this->addSql('DROP TABLE service_slot');
        $this->addSql('DROP TABLE slot_withdrawal');
        $this->addSql('DROP TABLE student');
        $this->addSql('DROP TABLE student_class');
        $this->addSql('DROP TABLE transaction');
        $this->addSql('DROP TABLE visitor');
    }
}
