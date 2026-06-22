<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260622085525 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE episode (id BLOB NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, description CLOB NOT NULL, duration INTEGER NOT NULL, series_name VARCHAR(255) NOT NULL, episode_number INTEGER NOT NULL, season_number INTEGER NOT NULL, broadcast_at DATETIME DEFAULT NULL, status VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DDAA1CDA989D9B62 ON episode (slug)');
        $this->addSql('CREATE TABLE episode_tag (episode_id BLOB NOT NULL, tag_id BLOB NOT NULL, PRIMARY KEY (episode_id, tag_id), CONSTRAINT FK_BEBD579A362B62A0 FOREIGN KEY (episode_id) REFERENCES episode (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BEBD579ABAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_BEBD579A362B62A0 ON episode_tag (episode_id)');
        $this->addSql('CREATE INDEX IDX_BEBD579ABAD26311 ON episode_tag (tag_id)');
        $this->addSql('CREATE TABLE media_collection (id BLOB NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, description CLOB NOT NULL, collection_type VARCHAR(50) NOT NULL, is_published BOOLEAN NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F668ABA6989D9B62 ON media_collection (slug)');
        $this->addSql('CREATE TABLE collection_article (collection_id BLOB NOT NULL, article_id BLOB NOT NULL, PRIMARY KEY (collection_id, article_id), CONSTRAINT FK_56167CB7514956FD FOREIGN KEY (collection_id) REFERENCES media_collection (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_56167CB77294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_56167CB7514956FD ON collection_article (collection_id)');
        $this->addSql('CREATE INDEX IDX_56167CB77294869C ON collection_article (article_id)');
        $this->addSql('CREATE TABLE collection_episode (collection_id BLOB NOT NULL, episode_id BLOB NOT NULL, PRIMARY KEY (collection_id, episode_id), CONSTRAINT FK_89866E0B514956FD FOREIGN KEY (collection_id) REFERENCES media_collection (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_89866E0B362B62A0 FOREIGN KEY (episode_id) REFERENCES episode (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_89866E0B514956FD ON collection_episode (collection_id)');
        $this->addSql('CREATE INDEX IDX_89866E0B362B62A0 ON collection_episode (episode_id)');
        $this->addSql('CREATE TABLE collection_tag (collection_id BLOB NOT NULL, tag_id BLOB NOT NULL, PRIMARY KEY (collection_id, tag_id), CONSTRAINT FK_AB0018E7514956FD FOREIGN KEY (collection_id) REFERENCES media_collection (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_AB0018E7BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_AB0018E7514956FD ON collection_tag (collection_id)');
        $this->addSql('CREATE INDEX IDX_AB0018E7BAD26311 ON collection_tag (tag_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE episode');
        $this->addSql('DROP TABLE episode_tag');
        $this->addSql('DROP TABLE media_collection');
        $this->addSql('DROP TABLE collection_article');
        $this->addSql('DROP TABLE collection_episode');
        $this->addSql('DROP TABLE collection_tag');
    }
}
