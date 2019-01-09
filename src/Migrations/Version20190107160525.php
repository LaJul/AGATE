<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190107160525 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE player (id INT AUTO_INCREMENT NOT NULL, tournament_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, title VARCHAR(3) DEFAULT NULL, gender VARCHAR(1) DEFAULT NULL, rating INT NOT NULL, rating_type VARCHAR(1) DEFAULT NULL, pairing_number INT DEFAULT NULL, points INT NOT NULL, is_active TINYINT(1) NOT NULL, INDEX IDX_98197A6533D1A3E7 (tournament_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE round (id INT AUTO_INCREMENT NOT NULL, tournament_id INT DEFAULT NULL, number INT NOT NULL, start_date DATETIME DEFAULT NULL, INDEX IDX_C5EEEA3433D1A3E7 (tournament_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE game (id INT AUTO_INCREMENT NOT NULL, round_id INT DEFAULT NULL, white_id INT DEFAULT NULL, black_id INT DEFAULT NULL, number INT NOT NULL, whitePoints INT NOT NULL, blackPoints INT NOT NULL, result VARCHAR(3) DEFAULT NULL, pgn VARCHAR(3) DEFAULT NULL, INDEX IDX_232B318CA6005CA0 (round_id), INDEX IDX_232B318CCDBF46EC (white_id), INDEX IDX_232B318CD3E7E37C (black_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE affiliate (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, title VARCHAR(3) DEFAULT NULL, gender VARCHAR(1) NOT NULL, is_active TINYINT(1) NOT NULL, rating INT NOT NULL, rating_type VARCHAR(1) NOT NULL, rapid INT NOT NULL, rapid_type VARCHAR(1) NOT NULL, blitz INT NOT NULL, blitz_type VARCHAR(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tournament (id INT AUTO_INCREMENT NOT NULL, current_round_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, hom_number INT DEFAULT NULL, start_date DATE DEFAULT NULL, end_date DATE DEFAULT NULL, nb_rounds INT DEFAULT NULL, time_control_type INT DEFAULT NULL, time_control VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_BD5FB8D93B78268A (current_round_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE club (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A6533D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id)');
        $this->addSql('ALTER TABLE round ADD CONSTRAINT FK_C5EEEA3433D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id)');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318CA6005CA0 FOREIGN KEY (round_id) REFERENCES round (id)');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318CCDBF46EC FOREIGN KEY (white_id) REFERENCES player (id)');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318CD3E7E37C FOREIGN KEY (black_id) REFERENCES player (id)');
        $this->addSql('ALTER TABLE tournament ADD CONSTRAINT FK_BD5FB8D93B78268A FOREIGN KEY (current_round_id) REFERENCES round (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318CCDBF46EC');
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318CD3E7E37C');
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318CA6005CA0');
        $this->addSql('ALTER TABLE tournament DROP FOREIGN KEY FK_BD5FB8D93B78268A');
        $this->addSql('ALTER TABLE player DROP FOREIGN KEY FK_98197A6533D1A3E7');
        $this->addSql('ALTER TABLE round DROP FOREIGN KEY FK_C5EEEA3433D1A3E7');
        $this->addSql('DROP TABLE player');
        $this->addSql('DROP TABLE round');
        $this->addSql('DROP TABLE game');
        $this->addSql('DROP TABLE affiliate');
        $this->addSql('DROP TABLE tournament');
        $this->addSql('DROP TABLE club');
    }
}
