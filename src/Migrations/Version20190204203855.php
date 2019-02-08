<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190204203855 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE game CHANGE round_id round_id INT DEFAULT NULL, CHANGE white_id white_id INT DEFAULT NULL, CHANGE black_id black_id INT DEFAULT NULL, CHANGE result result VARCHAR(3) DEFAULT NULL, CHANGE pgn pgn VARCHAR(3) DEFAULT NULL, CHANGE $whiteFloat $whiteFloat VARCHAR(255) DEFAULT NULL, CHANGE $blackFloat $blackFloat VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE tournament DROP FOREIGN KEY FK_BD5FB8D93B78268A');
        $this->addSql('DROP INDEX UNIQ_BD5FB8D93B78268A ON tournament');
        $this->addSql('ALTER TABLE tournament DROP current_round_id, CHANGE hom_number hom_number INT DEFAULT NULL, CHANGE start_date start_date DATE DEFAULT NULL, CHANGE end_date end_date DATE DEFAULT NULL, CHANGE nb_rounds nb_rounds INT DEFAULT NULL, CHANGE time_control_type time_control_type INT DEFAULT NULL, CHANGE time_control time_control VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BD5FB8D9989D9B62 ON tournament (slug)');
        $this->addSql('ALTER TABLE player CHANGE tournament_id tournament_id INT DEFAULT NULL, CHANGE title title VARCHAR(3) DEFAULT NULL, CHANGE gender gender VARCHAR(1) DEFAULT NULL, CHANGE rating_type rating_type VARCHAR(1) DEFAULT NULL, CHANGE pairing_number pairing_number INT DEFAULT NULL, CHANGE club club VARCHAR(255) DEFAULT NULL, CHANGE league league VARCHAR(3) DEFAULT NULL, CHANGE category category VARCHAR(3) DEFAULT NULL, CHANGE federation federation VARCHAR(3) DEFAULT NULL');
        $this->addSql('ALTER TABLE round ADD exempt_id INT DEFAULT NULL, CHANGE tournament_id tournament_id INT DEFAULT NULL, CHANGE start_date start_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE round ADD CONSTRAINT FK_C5EEEA346623EE80 FOREIGN KEY (exempt_id) REFERENCES player (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C5EEEA346623EE80 ON round (exempt_id)');
        $this->addSql('ALTER TABLE affiliate CHANGE title title VARCHAR(3) DEFAULT NULL, CHANGE rapid rapid INT DEFAULT NULL, CHANGE blitz blitz INT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE affiliate CHANGE title title VARCHAR(3) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE rapid rapid INT DEFAULT NULL, CHANGE blitz blitz INT DEFAULT NULL');
        $this->addSql('ALTER TABLE game CHANGE round_id round_id INT DEFAULT NULL, CHANGE white_id white_id INT DEFAULT NULL, CHANGE black_id black_id INT DEFAULT NULL, CHANGE $whiteFloat $whiteFloat VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE $blackFloat $blackFloat VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE result result VARCHAR(3) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE pgn pgn VARCHAR(3) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE player CHANGE tournament_id tournament_id INT DEFAULT NULL, CHANGE title title VARCHAR(3) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE category category VARCHAR(3) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE gender gender VARCHAR(1) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE rating_type rating_type VARCHAR(1) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE club club VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE league league VARCHAR(3) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE federation federation VARCHAR(3) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE pairing_number pairing_number INT DEFAULT NULL');
        $this->addSql('ALTER TABLE round DROP FOREIGN KEY FK_C5EEEA346623EE80');
        $this->addSql('DROP INDEX UNIQ_C5EEEA346623EE80 ON round');
        $this->addSql('ALTER TABLE round DROP exempt_id, CHANGE tournament_id tournament_id INT DEFAULT NULL, CHANGE start_date start_date DATETIME DEFAULT \'NULL\'');
        $this->addSql('DROP INDEX UNIQ_BD5FB8D9989D9B62 ON tournament');
        $this->addSql('ALTER TABLE tournament ADD current_round_id INT DEFAULT NULL, CHANGE hom_number hom_number INT DEFAULT NULL, CHANGE start_date start_date DATE DEFAULT \'NULL\', CHANGE end_date end_date DATE DEFAULT \'NULL\', CHANGE nb_rounds nb_rounds INT DEFAULT NULL, CHANGE time_control_type time_control_type INT DEFAULT NULL, CHANGE time_control time_control VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE tournament ADD CONSTRAINT FK_BD5FB8D93B78268A FOREIGN KEY (current_round_id) REFERENCES round (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BD5FB8D93B78268A ON tournament (current_round_id)');
    }
}
