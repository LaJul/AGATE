<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190125185500 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tournament_tiebreak (tournament_id INT NOT NULL, tiebreak_id INT NOT NULL, INDEX IDX_21CF96FE33D1A3E7 (tournament_id), INDEX IDX_21CF96FE7E0FB13C (tiebreak_id), PRIMARY KEY(tournament_id, tiebreak_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tournament_tiebreak ADD CONSTRAINT FK_21CF96FE33D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tournament_tiebreak ADD CONSTRAINT FK_21CF96FE7E0FB13C FOREIGN KEY (tiebreak_id) REFERENCES tiebreak (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tournament CHANGE current_round_id current_round_id INT DEFAULT NULL, CHANGE hom_number hom_number INT DEFAULT NULL, CHANGE start_date start_date DATE DEFAULT NULL, CHANGE end_date end_date DATE DEFAULT NULL, CHANGE nb_rounds nb_rounds INT DEFAULT NULL, CHANGE time_control_type time_control_type INT DEFAULT NULL, CHANGE time_control time_control VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE player CHANGE tournament_id tournament_id INT DEFAULT NULL, CHANGE title title VARCHAR(3) DEFAULT NULL, CHANGE gender gender VARCHAR(1) DEFAULT NULL, CHANGE rating_type rating_type VARCHAR(1) DEFAULT NULL, CHANGE pairing_number pairing_number INT DEFAULT NULL');
        $this->addSql('ALTER TABLE round CHANGE tournament_id tournament_id INT DEFAULT NULL, CHANGE start_date start_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE game CHANGE round_id round_id INT DEFAULT NULL, CHANGE white_id white_id INT DEFAULT NULL, CHANGE black_id black_id INT DEFAULT NULL, CHANGE result result VARCHAR(3) DEFAULT NULL, CHANGE pgn pgn VARCHAR(3) DEFAULT NULL, CHANGE $whiteFloat $whiteFloat VARCHAR(255) DEFAULT NULL, CHANGE $blackFloat $blackFloat VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE affiliate CHANGE title title VARCHAR(3) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE tournament_tiebreak');
        $this->addSql('ALTER TABLE affiliate CHANGE title title VARCHAR(3) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE game CHANGE round_id round_id INT DEFAULT NULL, CHANGE white_id white_id INT DEFAULT NULL, CHANGE black_id black_id INT DEFAULT NULL, CHANGE $whiteFloat $whiteFloat VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE $blackFloat $blackFloat VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE result result VARCHAR(3) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE pgn pgn VARCHAR(3) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE player CHANGE tournament_id tournament_id INT DEFAULT NULL, CHANGE title title VARCHAR(3) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE gender gender VARCHAR(1) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE rating_type rating_type VARCHAR(1) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE pairing_number pairing_number INT DEFAULT NULL');
        $this->addSql('ALTER TABLE round CHANGE tournament_id tournament_id INT DEFAULT NULL, CHANGE start_date start_date DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE tournament CHANGE current_round_id current_round_id INT DEFAULT NULL, CHANGE hom_number hom_number INT DEFAULT NULL, CHANGE start_date start_date DATE DEFAULT \'NULL\', CHANGE end_date end_date DATE DEFAULT \'NULL\', CHANGE nb_rounds nb_rounds INT DEFAULT NULL, CHANGE time_control_type time_control_type INT DEFAULT NULL, CHANGE time_control time_control VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
    }
}
