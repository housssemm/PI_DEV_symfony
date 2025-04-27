<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250405144548 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Synchronisation des tables user et sous-classes après mise à jour de User.php';
    }

    public function up(Schema $schema): void
    {
        // Pas de changement pour user si elle est déjà correcte
        // Vérifie si userType existe et supprime-la si nécessaire
        if ($schema->getTable('user')->hasColumn('userType')) {
            $this->addSql('ALTER TABLE user DROP COLUMN userType');
        }

        // Tables des sous-classes (inchangées car correctes)
        $this->addSql('CREATE TEMPORARY TABLE __temp__adherent AS SELECT poids, taille, age, genre, objectif_personnel, niveau_activite, nom_entreprise, description_adherent, adresse_adherent, telephone_adherent, id FROM adherent');
        $this->addSql('DROP TABLE adherent');
        $this->addSql('CREATE TABLE adherent (poids DOUBLE PRECISION NOT NULL, taille DOUBLE PRECISION NOT NULL, age INTEGER NOT NULL, genre VARCHAR(10) NOT NULL, objectif_personnel VARCHAR(255) NOT NULL, niveau_activite VARCHAR(50) NOT NULL, nom_entreprise VARCHAR(255) NOT NULL, description_adherent CLOB NOT NULL, adresse_adherent VARCHAR(255) NOT NULL, telephone_adherent VARCHAR(8) NOT NULL, id INTEGER NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_90D3F060BF396750 FOREIGN KEY (id) REFERENCES user (id) ON DELETE CASCADE)');
        $this->addSql('INSERT INTO adherent (poids, taille, age, genre, objectif_personnel, niveau_activite, nom_entreprise, description_adherent, adresse_adherent, telephone_adherent, id) SELECT poids, taille, age, genre, objectif_personnel, niveau_activite, nom_entreprise, description_adherent, adresse_adherent, telephone_adherent, id FROM __temp__adherent');
        $this->addSql('DROP TABLE __temp__adherent');

        $this->addSql('CREATE TEMPORARY TABLE __temp__coach AS SELECT annee_experience, certificat_valide, specialite, note, cv, id FROM coach');
        $this->addSql('DROP TABLE coach');
        $this->addSql('CREATE TABLE coach (annee_experience INTEGER NOT NULL, certificat_valide BOOLEAN DEFAULT NULL, specialite VARCHAR(255) NOT NULL, note INTEGER DEFAULT NULL, cv VARCHAR(255) NOT NULL, id INTEGER NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_3F596DCCBF396750 FOREIGN KEY (id) REFERENCES user (id) ON DELETE CASCADE)');
        $this->addSql('INSERT INTO coach (annee_experience, certificat_valide, specialite, note, cv, id) SELECT annee_experience, certificat_valide, specialite, note, cv, id FROM __temp__coach');
        $this->addSql('DROP TABLE __temp__coach');

        $this->addSql('CREATE TEMPORARY TABLE __temp__createur_evenement AS SELECT nom_organisation, description_createur, adresse_createur, telephone_createur, certificat_valide, id FROM createur_evenement');
        $this->addSql('DROP TABLE createur_evenement');
        $this->addSql('CREATE TABLE createur_evenement (nom_organisation VARCHAR(255) NOT NULL, description_createur CLOB NOT NULL, adresse_createur VARCHAR(255) NOT NULL, telephone_createur VARCHAR(8) NOT NULL, certificat_valide BOOLEAN DEFAULT NULL, id INTEGER NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_3D8BA3E1BF396750 FOREIGN KEY (id) REFERENCES user (id) ON DELETE CASCADE)');
        $this->addSql('INSERT INTO createur_evenement (nom_organisation, description_createur, adresse_createur, telephone_createur, certificat_valide, id) SELECT nom_organisation, description_createur, adresse_createur, telephone_createur, certificat_valide, id FROM __temp__createur_evenement');
        $this->addSql('DROP TABLE __temp__createur_evenement');

        $this->addSql('CREATE TEMPORARY TABLE __temp__investisseurproduit AS SELECT nom_entreprise, description_investisseur, adresse_investisseur, telephone_investisseur, id FROM investisseurproduit');
        $this->addSql('DROP TABLE investisseurproduit');
        $this->addSql('CREATE TABLE investisseurproduit (nom_entreprise VARCHAR(255) NOT NULL, description_investisseur CLOB NOT NULL, adresse_investisseur VARCHAR(255) NOT NULL, telephone_investisseur VARCHAR(8) NOT NULL, id INTEGER NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_9F6FEA6CBF396750 FOREIGN KEY (id) REFERENCES user (id) ON DELETE CASCADE)');
        $this->addSql('INSERT INTO investisseurproduit (nom_entreprise, description_investisseur, adresse_investisseur, telephone_investisseur, id) SELECT nom_entreprise, description_investisseur, adresse_investisseur, telephone_investisseur, id FROM __temp__investisseurproduit');
        $this->addSql('DROP TABLE __temp__investisseurproduit');
    }

    public function down(Schema $schema): void
    {
        // Réversibilité ajustée
        $this->addSql('ALTER TABLE user ADD COLUMN userType VARCHAR(255) DEFAULT NULL'); // Si tu veux vraiment revenir à userType

        $this->addSql('CREATE TEMPORARY TABLE __temp__adherent AS SELECT poids, taille, age, genre, objectif_personnel, niveau_activite, nom_entreprise, description_adherent, adresse_adherent, telephone_adherent, id FROM adherent');
        $this->addSql('DROP TABLE adherent');
        $this->addSql('CREATE TABLE adherent (poids DOUBLE PRECISION NOT NULL, taille DOUBLE PRECISION NOT NULL, age INTEGER NOT NULL, genre VARCHAR(10) NOT NULL, objectif_personnel VARCHAR(255) NOT NULL, niveau_activite VARCHAR(50) NOT NULL, nom_entreprise VARCHAR(255) NOT NULL, description_adherent CLOB NOT NULL, adresse_adherent VARCHAR(255) NOT NULL, telephone_adherent VARCHAR(8) NOT NULL, id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, CONSTRAINT FK_90D3F060BF396750 FOREIGN KEY (id) REFERENCES user (id) ON DELETE CASCADE)');
        $this->addSql('INSERT INTO adherent (poids, taille, age, genre, objectif_personnel, niveau_activite, nom_entreprise, description_adherent, adresse_adherent, telephone_adherent, id) SELECT poids, taille, age, genre, objectif_personnel, niveau_activite, nom_entreprise, description_adherent, adresse_adherent, telephone_adherent, id FROM __temp__adherent');
        $this->addSql('DROP TABLE __temp__adherent');

        $this->addSql('CREATE TEMPORARY TABLE __temp__coach AS SELECT annee_experience, certificat_valide, specialite, note, cv, id FROM coach');
        $this->addSql('DROP TABLE coach');
        $this->addSql('CREATE TABLE coach (annee_experience INTEGER NOT NULL, certificat_valide BOOLEAN DEFAULT NULL, specialite VARCHAR(255) NOT NULL, note INTEGER DEFAULT NULL, cv VARCHAR(255) NOT NULL, id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, CONSTRAINT FK_3F596DCCBF396750 FOREIGN KEY (id) REFERENCES user (id) ON DELETE CASCADE)');
        $this->addSql('INSERT INTO coach (annee_experience, certificat_valide, specialite, note, cv, id) SELECT annee_experience, certificat_valide, specialite, note, cv, id FROM __temp__coach');
        $this->addSql('DROP TABLE __temp__coach');

        $this->addSql('CREATE TEMPORARY TABLE __temp__createur_evenement AS SELECT nom_organisation, description_createur, adresse_createur, telephone_createur, certificat_valide, id FROM createur_evenement');
        $this->addSql('DROP TABLE createur_evenement');
        $this->addSql('CREATE TABLE createur_evenement (nom_organisation VARCHAR(255) NOT NULL, description_createur CLOB NOT NULL, adresse_createur VARCHAR(255) NOT NULL, telephone_createur VARCHAR(8) NOT NULL, certificat_valide BOOLEAN DEFAULT NULL, id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, CONSTRAINT FK_3D8BA3E1BF396750 FOREIGN KEY (id) REFERENCES user (id) ON DELETE CASCADE)');
        $this->addSql('INSERT INTO createur_evenement (nom_organisation, description_createur, adresse_createur, telephone_createur, certificat_valide, id) SELECT nom_organisation, description_createur, adresse_createur, telephone_createur, certificat_valide, id FROM __temp__createur_evenement');
        $this->addSql('DROP TABLE __temp__createur_evenement');

        $this->addSql('CREATE TEMPORARY TABLE __temp__investisseurproduit AS SELECT nom_entreprise, description_investisseur, adresse_investisseur, telephone_investisseur, id FROM investisseurproduit');
        $this->addSql('DROP TABLE investisseurproduit');
        $this->addSql('CREATE TABLE investisseurproduit (nom_entreprise VARCHAR(255) NOT NULL, description_investisseur CLOB NOT NULL, adresse_investisseur VARCHAR(255) NOT NULL, telephone_investisseur VARCHAR(8) NOT NULL, id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, CONSTRAINT FK_9F6FEA6CBF396750 FOREIGN KEY (id) REFERENCES user (id) ON DELETE CASCADE)');
        $this->addSql('INSERT INTO investisseurproduit (nom_entreprise, description_investisseur, adresse_investisseur, telephone_investisseur, id) SELECT nom_entreprise, description_investisseur, adresse_investisseur, telephone_investisseur, id FROM __temp__investisseurproduit');
        $this->addSql('DROP TABLE __temp__investisseurproduit');
    }
}