<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250402203114 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE offrecoach ADD nouveau_tarif NUMERIC(10, 2) NOT NULL, ADD reservation_actuelle INT NOT NULL, ADD reservation_max INT NOT NULL, DROP nouveauTarif, DROP reservationActuelle, DROP reservationMax, CHANGE idCoach idCoach INT DEFAULT NULL');
        $this->addSql('ALTER TABLE offrecoach ADD CONSTRAINT FK_202EA3A34CC8505A FOREIGN KEY (offre_id) REFERENCES offre (id)');
        $this->addSql('ALTER TABLE offrecoach ADD CONSTRAINT FK_202EA3A3289D2ED1 FOREIGN KEY (idCoach) REFERENCES coach (id)');
        $this->addSql('DROP INDEX offre_id ON offrecoach');
        $this->addSql('CREATE INDEX IDX_202EA3A34CC8505A ON offrecoach (offre_id)');
        $this->addSql('DROP INDEX idcoach ON offrecoach');
        $this->addSql('CREATE INDEX IDX_202EA3A3289D2ED1 ON offrecoach (idCoach)');
        $this->addSql('ALTER TABLE offreproduit DROP FOREIGN KEY offreproduit_ibfk_1');
        $this->addSql('ALTER TABLE offreproduit DROP FOREIGN KEY offreproduit_ibfk_2');
        $this->addSql('ALTER TABLE offreproduit DROP FOREIGN KEY offreproduit_ibfk_1');
        $this->addSql('ALTER TABLE offreproduit DROP FOREIGN KEY offreproduit_ibfk_2');
        $this->addSql('ALTER TABLE offreproduit ADD nouveau_prix NUMERIC(10, 2) NOT NULL, ADD quantite_max INT NOT NULL, ADD quantite_vendue INT NOT NULL, DROP nouveauPrix, DROP quantiteMax, DROP quantiteVendue, CHANGE idProduit idProduit INT DEFAULT NULL');
        $this->addSql('ALTER TABLE offreproduit ADD CONSTRAINT FK_B71C6B8F4CC8505A FOREIGN KEY (offre_id) REFERENCES offre (id)');
        $this->addSql('ALTER TABLE offreproduit ADD CONSTRAINT FK_B71C6B8F391C87D5 FOREIGN KEY (idProduit) REFERENCES produit (id)');
        $this->addSql('DROP INDEX offre_id ON offreproduit');
        $this->addSql('CREATE INDEX IDX_B71C6B8F4CC8505A ON offreproduit (offre_id)');
        $this->addSql('DROP INDEX idproduit ON offreproduit');
        $this->addSql('CREATE INDEX IDX_B71C6B8F391C87D5 ON offreproduit (idProduit)');
        $this->addSql('ALTER TABLE offreproduit ADD CONSTRAINT offreproduit_ibfk_1 FOREIGN KEY (offre_id) REFERENCES offre (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE offreproduit ADD CONSTRAINT offreproduit_ibfk_2 FOREIGN KEY (idProduit) REFERENCES produit (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE paiement_planning DROP FOREIGN KEY paiement_planning_ibfk_1');
        $this->addSql('ALTER TABLE paiement_planning DROP FOREIGN KEY paiement_planning_ibfk_2');
        $this->addSql('ALTER TABLE paiement_planning CHANGE id_adherent id_adherent INT DEFAULT NULL, CHANGE id_planning id_planning INT DEFAULT NULL, CHANGE etat_paiement etat_paiement VARCHAR(255) NOT NULL');
        $this->addSql('DROP INDEX id_adherent ON paiement_planning');
        $this->addSql('CREATE INDEX IDX_1E3E47CCC0081CF5 ON paiement_planning (id_adherent)');
        $this->addSql('DROP INDEX id_planning ON paiement_planning');
        $this->addSql('CREATE INDEX IDX_1E3E47CC84425363 ON paiement_planning (id_planning)');
        $this->addSql('ALTER TABLE paiement_planning ADD CONSTRAINT paiement_planning_ibfk_1 FOREIGN KEY (id_adherent) REFERENCES adherent (id)');
        $this->addSql('ALTER TABLE paiement_planning ADD CONSTRAINT paiement_planning_ibfk_2 FOREIGN KEY (id_planning) REFERENCES planning (id)');
        $this->addSql('ALTER TABLE panier DROP FOREIGN KEY fk_user');
        $this->addSql('ALTER TABLE panier CHANGE id_user id_user INT DEFAULT NULL');
        $this->addSql('DROP INDEX fk_user ON panier');
        $this->addSql('CREATE INDEX IDX_24CC0DF26B3CA4B ON panier (id_user)');
        $this->addSql('ALTER TABLE panier ADD CONSTRAINT fk_user FOREIGN KEY (id_user) REFERENCES user (id)');
        $this->addSql('ALTER TABLE panierproduit DROP FOREIGN KEY panierproduit_ibfk_1');
        $this->addSql('ALTER TABLE panierproduit DROP FOREIGN KEY panierproduit_ibfk_2');
        $this->addSql('ALTER TABLE panierproduit CHANGE montant montant DOUBLE PRECISION NOT NULL, CHANGE etat_paiement etat_paiement VARCHAR(255) NOT NULL');
        $this->addSql('DROP INDEX produitid ON panierproduit');
        $this->addSql('CREATE INDEX IDX_656FE9BA59C47661 ON panierproduit (produitId)');
        $this->addSql('DROP INDEX panierid ON panierproduit');
        $this->addSql('CREATE INDEX IDX_656FE9BA728698BC ON panierproduit (panierId)');
        $this->addSql('ALTER TABLE panierproduit ADD CONSTRAINT panierproduit_ibfk_1 FOREIGN KEY (produitId) REFERENCES produit (id)');
        $this->addSql('ALTER TABLE panierproduit ADD CONSTRAINT panierproduit_ibfk_2 FOREIGN KEY (panierId) REFERENCES panier (id)');
        $this->addSql('ALTER TABLE participantevenement DROP FOREIGN KEY participantevenement_ibfk_1');
        $this->addSql('ALTER TABLE participantevenement DROP FOREIGN KEY participantevenement_ibfk_2');
        $this->addSql('ALTER TABLE participantevenement CHANGE etat_paiement etat_paiement VARCHAR(255) DEFAULT NULL');
        $this->addSql('DROP INDEX userid ON participantevenement');
        $this->addSql('CREATE INDEX IDX_2BA7A88364B64DCC ON participantevenement (userId)');
        $this->addSql('DROP INDEX evenementid ON participantevenement');
        $this->addSql('CREATE INDEX IDX_2BA7A883BD959E61 ON participantevenement (evenementId)');
        $this->addSql('ALTER TABLE participantevenement ADD CONSTRAINT participantevenement_ibfk_1 FOREIGN KEY (userId) REFERENCES user (id)');
        $this->addSql('ALTER TABLE participantevenement ADD CONSTRAINT participantevenement_ibfk_2 FOREIGN KEY (evenementId) REFERENCES evenement (id)');
        $this->addSql('ALTER TABLE planning DROP FOREIGN KEY planning_ibfk_1');
        $this->addSql('ALTER TABLE planning DROP FOREIGN KEY planning_ibfk_1');
        $this->addSql('ALTER TABLE planning CHANGE tarif tarif NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE planning ADD CONSTRAINT FK_D499BFF6289D2ED1 FOREIGN KEY (idCoach) REFERENCES coach (id)');
        $this->addSql('DROP INDEX idcoach ON planning');
        $this->addSql('CREATE INDEX IDX_D499BFF6289D2ED1 ON planning (idCoach)');
        $this->addSql('ALTER TABLE planning ADD CONSTRAINT planning_ibfk_1 FOREIGN KEY (idCoach) REFERENCES coach (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY produit_ibfk_2');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY produit_ibfk_1');
        $this->addSql('ALTER TABLE produit CHANGE nom nom VARCHAR(255) DEFAULT NULL, CHANGE description description LONGTEXT DEFAULT NULL, CHANGE etat etat VARCHAR(255) DEFAULT NULL, CHANGE prix prix DOUBLE PRECISION NOT NULL');
        $this->addSql('DROP INDEX idinvestisseur ON produit');
        $this->addSql('CREATE INDEX IDX_29A5EC27F1585D1 ON produit (idInvestisseur)');
        $this->addSql('DROP INDEX categorieid ON produit');
        $this->addSql('CREATE INDEX IDX_29A5EC27FE278E99 ON produit (categorieId)');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT produit_ibfk_2 FOREIGN KEY (categorieId) REFERENCES categorie (id)');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT produit_ibfk_1 FOREIGN KEY (idInvestisseur) REFERENCES investisseurproduit (id)');
        $this->addSql('ALTER TABLE reclamation MODIFY IdReclamation INT NOT NULL');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY reclamation_ibfk_1');
        $this->addSql('DROP INDEX `primary` ON reclamation');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY fk_reclamateur');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY reclamation_ibfk_1');
        $this->addSql('ALTER TABLE reclamation ADD type_r VARCHAR(255) DEFAULT NULL, DROP typeR, CHANGE description description LONGTEXT DEFAULT NULL, CHANGE Id_adherent Id_adherent INT DEFAULT NULL, CHANGE IdReclamation id_reclamation INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT FK_CE60640428AA4EAA FOREIGN KEY (Id_coach) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reclamation ADD PRIMARY KEY (id_reclamation)');
        $this->addSql('DROP INDEX id_destinataire ON reclamation');
        $this->addSql('CREATE INDEX IDX_CE60640428AA4EAA ON reclamation (Id_coach)');
        $this->addSql('DROP INDEX fk_reclamateur ON reclamation');
        $this->addSql('CREATE INDEX IDX_CE60640446949322 ON reclamation (Id_adherent)');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT fk_reclamateur FOREIGN KEY (Id_adherent) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT reclamation_ibfk_1 FOREIGN KEY (Id_coach) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY reponse_ibfk_1');
        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY reponse_ibfk_1');
        $this->addSql('ALTER TABLE reponse CHANGE Contenu contenu LONGTEXT DEFAULT NULL, CHANGE status status VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC79D8A5EFC FOREIGN KEY (Id_Reclamation) REFERENCES reclamation (IdReclamation)');
        $this->addSql('DROP INDEX idreclamation ON reponse');
        $this->addSql('CREATE INDEX IDX_5FB6DEC79D8A5EFC ON reponse (Id_Reclamation)');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT reponse_ibfk_1 FOREIGN KEY (Id_Reclamation) REFERENCES reclamation (IdReclamation) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE seance DROP FOREIGN KEY seance_ibfk_3');
        $this->addSql('ALTER TABLE seance DROP FOREIGN KEY seance_ibfk_1');
        $this->addSql('ALTER TABLE seance DROP FOREIGN KEY seance_ibfk_2');
        $this->addSql('ALTER TABLE seance DROP FOREIGN KEY seance_ibfk_3');
        $this->addSql('ALTER TABLE seance DROP FOREIGN KEY seance_ibfk_1');
        $this->addSql('ALTER TABLE seance DROP FOREIGN KEY seance_ibfk_2');
        $this->addSql('ALTER TABLE seance ADD heure_debut TIME DEFAULT NULL, ADD heure_fin TIME DEFAULT NULL, DROP heureDebut, DROP heureFin, CHANGE Description description LONGTEXT DEFAULT NULL, CHANGE Type type VARCHAR(255) DEFAULT NULL, CHANGE LienVideo lien_video VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE seance ADD CONSTRAINT FK_DF7DFD0E289D2ED1 FOREIGN KEY (idCoach) REFERENCES coach (id)');
        $this->addSql('ALTER TABLE seance ADD CONSTRAINT FK_DF7DFD0EC370DA3B FOREIGN KEY (idAdherent) REFERENCES adherent (id)');
        $this->addSql('ALTER TABLE seance ADD CONSTRAINT FK_DF7DFD0EBB1ADCC6 FOREIGN KEY (Planning_id) REFERENCES planning (id)');
        $this->addSql('DROP INDEX idcoach ON seance');
        $this->addSql('CREATE INDEX IDX_DF7DFD0E289D2ED1 ON seance (idCoach)');
        $this->addSql('DROP INDEX idadherent ON seance');
        $this->addSql('CREATE INDEX IDX_DF7DFD0EC370DA3B ON seance (idAdherent)');
        $this->addSql('DROP INDEX planning_id ON seance');
        $this->addSql('CREATE INDEX IDX_DF7DFD0EBB1ADCC6 ON seance (Planning_id)');
        $this->addSql('ALTER TABLE seance ADD CONSTRAINT seance_ibfk_3 FOREIGN KEY (Planning_id) REFERENCES planning (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE seance ADD CONSTRAINT seance_ibfk_1 FOREIGN KEY (idAdherent) REFERENCES adherent (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE seance ADD CONSTRAINT seance_ibfk_2 FOREIGN KEY (idCoach) REFERENCES coach (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE offrecoach DROP FOREIGN KEY FK_202EA3A34CC8505A');
        $this->addSql('ALTER TABLE offrecoach DROP FOREIGN KEY FK_202EA3A3289D2ED1');
        $this->addSql('ALTER TABLE offrecoach DROP FOREIGN KEY FK_202EA3A34CC8505A');
        $this->addSql('ALTER TABLE offrecoach DROP FOREIGN KEY FK_202EA3A3289D2ED1');
        $this->addSql('ALTER TABLE offrecoach ADD nouveauTarif DOUBLE PRECISION NOT NULL, ADD reservationActuelle INT NOT NULL, ADD reservationMax INT NOT NULL, DROP nouveau_tarif, DROP reservation_actuelle, DROP reservation_max, CHANGE idCoach idCoach INT NOT NULL');
        $this->addSql('DROP INDEX idx_202ea3a34cc8505a ON offrecoach');
        $this->addSql('CREATE INDEX offre_id ON offrecoach (offre_id)');
        $this->addSql('DROP INDEX idx_202ea3a3289d2ed1 ON offrecoach');
        $this->addSql('CREATE INDEX idCoach ON offrecoach (idCoach)');
        $this->addSql('ALTER TABLE offrecoach ADD CONSTRAINT FK_202EA3A34CC8505A FOREIGN KEY (offre_id) REFERENCES offre (id)');
        $this->addSql('ALTER TABLE offrecoach ADD CONSTRAINT FK_202EA3A3289D2ED1 FOREIGN KEY (idCoach) REFERENCES coach (id)');
        $this->addSql('ALTER TABLE offreproduit DROP FOREIGN KEY FK_B71C6B8F4CC8505A');
        $this->addSql('ALTER TABLE offreproduit DROP FOREIGN KEY FK_B71C6B8F391C87D5');
        $this->addSql('ALTER TABLE offreproduit DROP FOREIGN KEY FK_B71C6B8F4CC8505A');
        $this->addSql('ALTER TABLE offreproduit DROP FOREIGN KEY FK_B71C6B8F391C87D5');
        $this->addSql('ALTER TABLE offreproduit ADD nouveauPrix DOUBLE PRECISION NOT NULL, ADD quantiteMax INT NOT NULL, ADD quantiteVendue INT NOT NULL, DROP nouveau_prix, DROP quantite_max, DROP quantite_vendue, CHANGE idProduit idProduit INT NOT NULL');
        $this->addSql('ALTER TABLE offreproduit ADD CONSTRAINT offreproduit_ibfk_1 FOREIGN KEY (offre_id) REFERENCES offre (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE offreproduit ADD CONSTRAINT offreproduit_ibfk_2 FOREIGN KEY (idProduit) REFERENCES produit (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX idx_b71c6b8f4cc8505a ON offreproduit');
        $this->addSql('CREATE INDEX offre_id ON offreproduit (offre_id)');
        $this->addSql('DROP INDEX idx_b71c6b8f391c87d5 ON offreproduit');
        $this->addSql('CREATE INDEX idProduit ON offreproduit (idProduit)');
        $this->addSql('ALTER TABLE offreproduit ADD CONSTRAINT FK_B71C6B8F4CC8505A FOREIGN KEY (offre_id) REFERENCES offre (id)');
        $this->addSql('ALTER TABLE offreproduit ADD CONSTRAINT FK_B71C6B8F391C87D5 FOREIGN KEY (idProduit) REFERENCES produit (id)');
        $this->addSql('ALTER TABLE paiement_planning DROP FOREIGN KEY FK_1E3E47CCC0081CF5');
        $this->addSql('ALTER TABLE paiement_planning DROP FOREIGN KEY FK_1E3E47CC84425363');
        $this->addSql('ALTER TABLE paiement_planning CHANGE etat_paiement etat_paiement ENUM(\'PAYE\', \'EN_ATTENTE\') DEFAULT \'EN_ATTENTE\' NOT NULL, CHANGE id_adherent id_adherent INT NOT NULL, CHANGE id_planning id_planning INT NOT NULL');
        $this->addSql('DROP INDEX idx_1e3e47ccc0081cf5 ON paiement_planning');
        $this->addSql('CREATE INDEX id_adherent ON paiement_planning (id_adherent)');
        $this->addSql('DROP INDEX idx_1e3e47cc84425363 ON paiement_planning');
        $this->addSql('CREATE INDEX id_planning ON paiement_planning (id_planning)');
        $this->addSql('ALTER TABLE paiement_planning ADD CONSTRAINT FK_1E3E47CCC0081CF5 FOREIGN KEY (id_adherent) REFERENCES adherent (id)');
        $this->addSql('ALTER TABLE paiement_planning ADD CONSTRAINT FK_1E3E47CC84425363 FOREIGN KEY (id_planning) REFERENCES planning (id)');
        $this->addSql('ALTER TABLE panier DROP FOREIGN KEY FK_24CC0DF26B3CA4B');
        $this->addSql('ALTER TABLE panier CHANGE id_user id_user INT NOT NULL');
        $this->addSql('DROP INDEX idx_24cc0df26b3ca4b ON panier');
        $this->addSql('CREATE INDEX fk_user ON panier (id_user)');
        $this->addSql('ALTER TABLE panier ADD CONSTRAINT FK_24CC0DF26B3CA4B FOREIGN KEY (id_user) REFERENCES user (id)');
        $this->addSql('ALTER TABLE panierproduit DROP FOREIGN KEY FK_656FE9BA59C47661');
        $this->addSql('ALTER TABLE panierproduit DROP FOREIGN KEY FK_656FE9BA728698BC');
        $this->addSql('ALTER TABLE panierproduit CHANGE montant montant FLOAT NOT NULL, CHANGE etat_paiement etat_paiement ENUM(\'En_Attente\', \'Payé\') NOT NULL');
        $this->addSql('DROP INDEX idx_656fe9ba728698bc ON panierproduit');
        $this->addSql('CREATE INDEX panierId ON panierproduit (panierId)');
        $this->addSql('DROP INDEX idx_656fe9ba59c47661 ON panierproduit');
        $this->addSql('CREATE INDEX produitId ON panierproduit (produitId)');
        $this->addSql('ALTER TABLE panierproduit ADD CONSTRAINT FK_656FE9BA59C47661 FOREIGN KEY (produitId) REFERENCES produit (id)');
        $this->addSql('ALTER TABLE panierproduit ADD CONSTRAINT FK_656FE9BA728698BC FOREIGN KEY (panierId) REFERENCES panier (id)');
        $this->addSql('ALTER TABLE participantevenement DROP FOREIGN KEY FK_2BA7A88364B64DCC');
        $this->addSql('ALTER TABLE participantevenement DROP FOREIGN KEY FK_2BA7A883BD959E61');
        $this->addSql('ALTER TABLE participantevenement CHANGE etat_paiement etat_paiement ENUM(\'EN_ATTENTE\', \'PAYE\', \'ANNULER\') DEFAULT NULL');
        $this->addSql('DROP INDEX idx_2ba7a883bd959e61 ON participantevenement');
        $this->addSql('CREATE INDEX evenementId ON participantevenement (evenementId)');
        $this->addSql('DROP INDEX idx_2ba7a88364b64dcc ON participantevenement');
        $this->addSql('CREATE INDEX userId ON participantevenement (userId)');
        $this->addSql('ALTER TABLE participantevenement ADD CONSTRAINT FK_2BA7A88364B64DCC FOREIGN KEY (userId) REFERENCES user (id)');
        $this->addSql('ALTER TABLE participantevenement ADD CONSTRAINT FK_2BA7A883BD959E61 FOREIGN KEY (evenementId) REFERENCES evenement (id)');
        $this->addSql('ALTER TABLE planning DROP FOREIGN KEY FK_D499BFF6289D2ED1');
        $this->addSql('ALTER TABLE planning DROP FOREIGN KEY FK_D499BFF6289D2ED1');
        $this->addSql('ALTER TABLE planning CHANGE tarif tarif DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE planning ADD CONSTRAINT planning_ibfk_1 FOREIGN KEY (idCoach) REFERENCES coach (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX idx_d499bff6289d2ed1 ON planning');
        $this->addSql('CREATE INDEX idCoach ON planning (idCoach)');
        $this->addSql('ALTER TABLE planning ADD CONSTRAINT FK_D499BFF6289D2ED1 FOREIGN KEY (idCoach) REFERENCES coach (id)');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC27F1585D1');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC27FE278E99');
        $this->addSql('ALTER TABLE produit CHANGE nom nom VARCHAR(100) DEFAULT NULL, CHANGE description description TEXT DEFAULT NULL, CHANGE etat etat ENUM(\'Stock\', \'Rupture\') DEFAULT NULL, CHANGE prix prix FLOAT NOT NULL');
        $this->addSql('DROP INDEX idx_29a5ec27f1585d1 ON produit');
        $this->addSql('CREATE INDEX idInvestisseur ON produit (idInvestisseur)');
        $this->addSql('DROP INDEX idx_29a5ec27fe278e99 ON produit');
        $this->addSql('CREATE INDEX categorieId ON produit (categorieId)');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC27F1585D1 FOREIGN KEY (idInvestisseur) REFERENCES investisseurproduit (id)');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC27FE278E99 FOREIGN KEY (categorieId) REFERENCES categorie (id)');
        $this->addSql('ALTER TABLE reclamation MODIFY id_reclamation INT NOT NULL');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY FK_CE60640428AA4EAA');
        $this->addSql('DROP INDEX `PRIMARY` ON reclamation');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY FK_CE60640428AA4EAA');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY FK_CE60640446949322');
        $this->addSql('ALTER TABLE reclamation ADD typeR ENUM(\'PRODUIT\', \'COACH\', \'ADHERENT\', \'EVENEMENT\') DEFAULT NULL, DROP type_r, CHANGE description description TEXT DEFAULT NULL, CHANGE Id_adherent Id_adherent INT NOT NULL, CHANGE id_reclamation IdReclamation INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT reclamation_ibfk_1 FOREIGN KEY (Id_coach) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reclamation ADD PRIMARY KEY (IdReclamation)');
        $this->addSql('DROP INDEX idx_ce60640428aa4eaa ON reclamation');
        $this->addSql('CREATE INDEX Id_destinataire ON reclamation (Id_coach)');
        $this->addSql('DROP INDEX idx_ce60640446949322 ON reclamation');
        $this->addSql('CREATE INDEX fk_reclamateur ON reclamation (Id_adherent)');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT FK_CE60640428AA4EAA FOREIGN KEY (Id_coach) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT FK_CE60640446949322 FOREIGN KEY (Id_adherent) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY FK_5FB6DEC79D8A5EFC');
        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY FK_5FB6DEC79D8A5EFC');
        $this->addSql('ALTER TABLE reponse CHANGE contenu Contenu TEXT DEFAULT NULL, CHANGE status status VARCHAR(300) NOT NULL');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT reponse_ibfk_1 FOREIGN KEY (Id_Reclamation) REFERENCES reclamation (IdReclamation) ON DELETE CASCADE');
        $this->addSql('DROP INDEX idx_5fb6dec79d8a5efc ON reponse');
        $this->addSql('CREATE INDEX IdReclamation ON reponse (Id_Reclamation)');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC79D8A5EFC FOREIGN KEY (Id_Reclamation) REFERENCES reclamation (IdReclamation)');
        $this->addSql('ALTER TABLE seance DROP FOREIGN KEY FK_DF7DFD0E289D2ED1');
        $this->addSql('ALTER TABLE seance DROP FOREIGN KEY FK_DF7DFD0EC370DA3B');
        $this->addSql('ALTER TABLE seance DROP FOREIGN KEY FK_DF7DFD0EBB1ADCC6');
        $this->addSql('ALTER TABLE seance DROP FOREIGN KEY FK_DF7DFD0E289D2ED1');
        $this->addSql('ALTER TABLE seance DROP FOREIGN KEY FK_DF7DFD0EC370DA3B');
        $this->addSql('ALTER TABLE seance DROP FOREIGN KEY FK_DF7DFD0EBB1ADCC6');
        $this->addSql('ALTER TABLE seance ADD heureDebut TIME DEFAULT NULL, ADD heureFin TIME DEFAULT NULL, DROP heure_debut, DROP heure_fin, CHANGE description Description TEXT DEFAULT NULL, CHANGE type Type ENUM(\'EN_DIRECT\', \'ENREGISTRE\') DEFAULT NULL, CHANGE lien_video LienVideo VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE seance ADD CONSTRAINT seance_ibfk_3 FOREIGN KEY (Planning_id) REFERENCES planning (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE seance ADD CONSTRAINT seance_ibfk_1 FOREIGN KEY (idAdherent) REFERENCES adherent (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE seance ADD CONSTRAINT seance_ibfk_2 FOREIGN KEY (idCoach) REFERENCES coach (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX idx_df7dfd0ec370da3b ON seance');
        $this->addSql('CREATE INDEX idAdherent ON seance (idAdherent)');
        $this->addSql('DROP INDEX idx_df7dfd0e289d2ed1 ON seance');
        $this->addSql('CREATE INDEX idCoach ON seance (idCoach)');
        $this->addSql('DROP INDEX idx_df7dfd0ebb1adcc6 ON seance');
        $this->addSql('CREATE INDEX Planning_id ON seance (Planning_id)');
        $this->addSql('ALTER TABLE seance ADD CONSTRAINT FK_DF7DFD0E289D2ED1 FOREIGN KEY (idCoach) REFERENCES coach (id)');
        $this->addSql('ALTER TABLE seance ADD CONSTRAINT FK_DF7DFD0EC370DA3B FOREIGN KEY (idAdherent) REFERENCES adherent (id)');
        $this->addSql('ALTER TABLE seance ADD CONSTRAINT FK_DF7DFD0EBB1ADCC6 FOREIGN KEY (Planning_id) REFERENCES planning (id)');
    }
}
