<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251125154346 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE evaluation (id INT AUTO_INCREMENT NOT NULL, ride_id INT NOT NULL, passenger_id INT NOT NULL, status_id INT NOT NULL, treated_by_id INT DEFAULT NULL, validation_passenger TINYINT(1) NOT NULL, rate INT NOT NULL, comment LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', claimed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', concluded_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_1323A575302A8A70 (ride_id), INDEX IDX_1323A5754502E565 (passenger_id), INDEX IDX_1323A5756BF700BD (status_id), INDEX IDX_1323A575794E2304 (treated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE evaluation_status (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(20) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ride (id INT AUTO_INCREMENT NOT NULL, driver_id INT NOT NULL, ride_status_id INT NOT NULL, vehicle_id INT NOT NULL, origin_city VARCHAR(60) NOT NULL, pick_point VARCHAR(255) NOT NULL, departure_date DATETIME NOT NULL, departure_intended_time TIME NOT NULL, departure_real_time TIME DEFAULT NULL, destiny_city VARCHAR(60) NOT NULL, drop_point VARCHAR(255) NOT NULL, arrival_date DATETIME NOT NULL, arrival_estimated_time TIME NOT NULL, arrival_real_time TIME DEFAULT NULL, nb_places_offered INT NOT NULL, nb_places_available INT NOT NULL, price_person DOUBLE PRECISION NOT NULL, smokers_allowed TINYINT(1) NOT NULL, animals_allowed TINYINT(1) NOT NULL, other_preferences VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', cancelled_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_9B3D7CD0C3423909 (driver_id), INDEX IDX_9B3D7CD09CEA7A42 (ride_status_id), INDEX IDX_9B3D7CD0545317D1 (vehicle_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ride_passenger (ride_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_CCF29222302A8A70 (ride_id), INDEX IDX_CCF29222A76ED395 (user_id), PRIMARY KEY(ride_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ride_status (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(20) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(25) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(50) NOT NULL, last_name VARCHAR(255) NOT NULL, nickname VARCHAR(255) NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, telephone VARCHAR(15) DEFAULT NULL, birthday DATETIME NOT NULL, photo LONGBLOB DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', credit DOUBLE PRECISION NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), UNIQUE INDEX UNIQ_NICKNAME (nickname), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_vehicle (user_id INT NOT NULL, vehicle_id INT NOT NULL, INDEX IDX_438DFA8CA76ED395 (user_id), INDEX IDX_438DFA8C545317D1 (vehicle_id), PRIMARY KEY(user_id, vehicle_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_role (user_id INT NOT NULL, role_id INT NOT NULL, INDEX IDX_2DE8C6A3A76ED395 (user_id), INDEX IDX_2DE8C6A3D60322AC (role_id), PRIMARY KEY(user_id, role_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_address (user_id INT NOT NULL, number VARCHAR(6) NOT NULL, street VARCHAR(255) NOT NULL, complement VARCHAR(255) DEFAULT NULL, city VARCHAR(60) NOT NULL, zipcode VARCHAR(10) NOT NULL, country VARCHAR(60) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vehicle (id INT AUTO_INCREMENT NOT NULL, vehicle_brand_id INT NOT NULL, model VARCHAR(255) NOT NULL, color VARCHAR(255) NOT NULL, registration VARCHAR(15) NOT NULL, first_rg_date DATETIME NOT NULL, electric TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', shared_vehicle TINYINT(1) NOT NULL, INDEX IDX_1B80E48699E7DF9C (vehicle_brand_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vehicle_brand (id INT AUTO_INCREMENT NOT NULL, brand VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE evaluation ADD CONSTRAINT FK_1323A575302A8A70 FOREIGN KEY (ride_id) REFERENCES ride (id)');
        $this->addSql('ALTER TABLE evaluation ADD CONSTRAINT FK_1323A5754502E565 FOREIGN KEY (passenger_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE evaluation ADD CONSTRAINT FK_1323A5756BF700BD FOREIGN KEY (status_id) REFERENCES evaluation_status (id)');
        $this->addSql('ALTER TABLE evaluation ADD CONSTRAINT FK_1323A575794E2304 FOREIGN KEY (treated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE ride ADD CONSTRAINT FK_9B3D7CD0C3423909 FOREIGN KEY (driver_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE ride ADD CONSTRAINT FK_9B3D7CD09CEA7A42 FOREIGN KEY (ride_status_id) REFERENCES ride_status (id)');
        $this->addSql('ALTER TABLE ride ADD CONSTRAINT FK_9B3D7CD0545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id)');
        $this->addSql('ALTER TABLE ride_passenger ADD CONSTRAINT FK_CCF29222302A8A70 FOREIGN KEY (ride_id) REFERENCES ride (id)');
        $this->addSql('ALTER TABLE ride_passenger ADD CONSTRAINT FK_CCF29222A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_vehicle ADD CONSTRAINT FK_438DFA8CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_vehicle ADD CONSTRAINT FK_438DFA8C545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3D60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_address ADD CONSTRAINT FK_5543718BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE vehicle ADD CONSTRAINT FK_1B80E48699E7DF9C FOREIGN KEY (vehicle_brand_id) REFERENCES vehicle_brand (id) ON DELETE RESTRICT');
        $this->addSql('CREATE UNIQUE INDEX uniq_ride_passenger_eval ON evaluation (ride_id, passenger_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_ride_user ON ride_passenger (ride_id, user_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX uniq_ride_passenger_eval ON evaluation');
        $this->addSql('DROP INDEX uniq_ride_user ON ride_passenger');
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE evaluation DROP FOREIGN KEY FK_1323A575302A8A70');
        $this->addSql('ALTER TABLE evaluation DROP FOREIGN KEY FK_1323A5754502E565');
        $this->addSql('ALTER TABLE evaluation DROP FOREIGN KEY FK_1323A5756BF700BD');
        $this->addSql('ALTER TABLE evaluation DROP FOREIGN KEY FK_1323A575794E2304');
        $this->addSql('ALTER TABLE ride DROP FOREIGN KEY FK_9B3D7CD0C3423909');
        $this->addSql('ALTER TABLE ride DROP FOREIGN KEY FK_9B3D7CD09CEA7A42');
        $this->addSql('ALTER TABLE ride DROP FOREIGN KEY FK_9B3D7CD0545317D1');
        $this->addSql('ALTER TABLE ride_passenger DROP FOREIGN KEY FK_CCF29222302A8A70');
        $this->addSql('ALTER TABLE ride_passenger DROP FOREIGN KEY FK_CCF29222A76ED395');
        $this->addSql('ALTER TABLE user_vehicle DROP FOREIGN KEY FK_438DFA8CA76ED395');
        $this->addSql('ALTER TABLE user_vehicle DROP FOREIGN KEY FK_438DFA8C545317D1');
        $this->addSql('ALTER TABLE user_role DROP FOREIGN KEY FK_2DE8C6A3A76ED395');
        $this->addSql('ALTER TABLE user_role DROP FOREIGN KEY FK_2DE8C6A3D60322AC');
        $this->addSql('ALTER TABLE user_address DROP FOREIGN KEY FK_5543718BA76ED395');
        $this->addSql('ALTER TABLE vehicle DROP FOREIGN KEY FK_1B80E48699E7DF9C');
        $this->addSql('DROP TABLE evaluation');
        $this->addSql('DROP TABLE evaluation_status');
        $this->addSql('DROP TABLE ride');
        $this->addSql('DROP TABLE ride_passenger');
        $this->addSql('DROP TABLE ride_status');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_vehicle');
        $this->addSql('DROP TABLE user_role');
        $this->addSql('DROP TABLE user_address');
        $this->addSql('DROP TABLE vehicle');
        $this->addSql('DROP TABLE vehicle_brand');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
