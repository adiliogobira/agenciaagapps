CREATE DATABASE IF NOT EXISTS agapps_prospeccao CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE agapps_prospeccao;

-- Guarda apenas o histórico comercial próprio e o identificador do lugar.
-- Revise as políticas do provedor antes de persistir qualquer campo retornado pela API.
CREATE TABLE IF NOT EXISTS prospects (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  provider VARCHAR(30) NOT NULL DEFAULT 'google_places',
  provider_place_id VARCHAR(255) NOT NULL,
  company_name VARCHAR(255) NOT NULL,
  state_code CHAR(2) NOT NULL,
  city VARCHAR(120) NOT NULL,
  neighborhood VARCHAR(120) NULL,
  segment VARCHAR(160) NULL,
  website_url VARCHAR(500) NULL,
  website_status ENUM('unknown','not_listed','possible_issue','ok','needs_review') NOT NULL DEFAULT 'unknown',
  commercial_phone VARCHAR(40) NULL,
  contact_status ENUM('not_contacted','prepared','contacted','replied','interested','not_interested','do_not_contact','converted') NOT NULL DEFAULT 'not_contacted',
  notes TEXT NULL,
  last_contact_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_provider_place (provider, provider_place_id),
  KEY idx_city_segment (state_code, city, segment),
  KEY idx_contact_status (contact_status),
  KEY idx_website_status (website_status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS prospect_contacts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  prospect_id BIGINT UNSIGNED NOT NULL,
  channel ENUM('whatsapp','phone','email','other') NOT NULL DEFAULT 'whatsapp',
  outcome ENUM('prepared','sent','replied','interested','not_interested','no_response','opt_out') NOT NULL DEFAULT 'prepared',
  message_text TEXT NULL,
  notes TEXT NULL,
  contacted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_contact_prospect FOREIGN KEY (prospect_id) REFERENCES prospects(id) ON DELETE CASCADE,
  KEY idx_prospect_contacted (prospect_id, contacted_at)
) ENGINE=InnoDB;
