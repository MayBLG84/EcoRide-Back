<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260112172622 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_ride_passenger_eval ON evaluation');
        $this->addSql('ALTER TABLE ride ADD estimated_duration INT DEFAULT 0 NOT NULL');
        $this->addSql('DROP INDEX uniq_ride_user ON ride_passenger');
        $this->addSql('ALTER TABLE user ADD avg_rating DOUBLE PRECISION DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ride DROP estimated_duration');
        $this->addSql('CREATE UNIQUE INDEX uniq_ride_user ON ride_passenger (ride_id, user_id)');
        $this->addSql('ALTER TABLE user DROP avg_rating');
        $this->addSql('CREATE UNIQUE INDEX uniq_ride_passenger_eval ON evaluation (ride_id, passenger_id)');
    }
}
