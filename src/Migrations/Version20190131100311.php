<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190131100311 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE round_player (round_id INT NOT NULL, player_id INT NOT NULL, INDEX IDX_5CAE43C9A6005CA0 (round_id), INDEX IDX_5CAE43C999E6F5DF (player_id), PRIMARY KEY(round_id, player_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE round_player ADD CONSTRAINT FK_5CAE43C9A6005CA0 FOREIGN KEY (round_id) REFERENCES round (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE round_player ADD CONSTRAINT FK_5CAE43C999E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE player CHANGE tournament_id tournament_id INT DEFAULT NULL, CHANGE title title VARCHAR(3) DEFAULT NULL, CHANGE gender gender VARCHAR(1) DEFAULT NULL, CHANGE rating_type rating_type VARCHAR(1) DEFAULT NULL, CHANGE pairing_number pairing_number INT DEFAULT NULL, CHANGE club club VARCHAR(255) DEFAULT NULL, CHANGE league league VARCHAR(3) DEFAULT NULL');
        $this->addSql('ALTER TABLE round CHANGE tournament_id tournament_id INT DEFAULT NULL, CHANGE start_date start_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE game CHANGE round_id round_id INT DEFAULT NULL, CHANGE white_id white_id INT DEFAULT NULL, CHANGE black_id black_id INT DEFAULT NULL, CHANGE result result VARCHAR(3) DEFAULT NULL, CHANGE pgn pgn VARCHAR(3) DEFAULT NULL, CHANGE $whiteFloat $whiteFloat VARCHAR(255) DEFAULT NULL, CHANGE $blackFloat $blackFloat VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE affiliate CHANGE title title VARCHAR(3) DEFAULT NULL');
        $this->addSql('ALTER TABLE tournament CHANGE current_round_id current_round_id INT DEFAULT NULL, CHANGE hom_number hom_number INT DEFAULT NULL, CHANGE start_date start_date DATE DEFAULT NULL, CHANGE end_date end_date DATE DEFAULT NULL, CHANGE nb_rounds nb_rounds INT DEFAULT NULL, CHANGE time_control_type time_control_type INT DEFAULT NULL, CHANGE time_control time_control VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE round_player');
        $this->addSql('ALTER TABLE affiliate CHANGE title title VARCHAR(3) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE game CHANGE round_id round_id INT DEFAULT NULL, CHANGE white_id white_id INT DEFAULT NULL, CHANGE black_id black_id INT DEFAULT NULL, CHANGE $whiteFloat $whiteFloat VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE $blackFloat $blackFloat VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE result result VARCHAR(3) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE pgn pgn VARCHAR(3) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE player CHANGE tournament_id tournament_id INT DEFAULT NULL, CHANGE title title VARCHAR(3) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE gender gender VARCHAR(1) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE rating_type rating_type VARCHAR(1) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE club club VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE league league VARCHAR(3) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE pairing_number pairing_number INT DEFAULT NULL');
        $this->addSql('ALTER TABLE round CHANGE tournament_id tournament_id INT DEFAULT NULL, CHANGE start_date start_date DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE tournament CHANGE current_round_id current_round_id INT DEFAULT NULL, CHANGE hom_number hom_number INT DEFAULT NULL, CHANGE start_date start_date DATE DEFAULT \'NULL\', CHANGE end_date end_date DATE DEFAULT \'NULL\', CHANGE nb_rounds nb_rounds INT DEFAULT NULL, CHANGE time_control_type time_control_type INT DEFAULT NULL, CHANGE time_control time_control VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
    }
}
