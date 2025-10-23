<?php

class User {
    private ?int $id = null;
    private string $pseudo;
    private string $email;

    public function __construct(?int $id, string $pseudo, string $email) {
        $this->id = $id;
        $this->pseudo = $pseudo;
        $this->email = $email;
    }

    public static function login(string $email, string $password): ?User {
        $pdo = Database::getConnexion();

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $ligne = $stmt->fetch();

        if ($ligne && password_verify($password, $ligne['password_hash'])) {
            return new User(
                $ligne['id'],
                $ligne['pseudo'],
                $ligne['email']
            );
        }
        return null;
    }

    public static function register(string $pseudo, string $email, string $password): ?User {
        $pdo = Database::getConnexion();

        try {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (pseudo, email, password_hash) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$pseudo, $email, $passwordHash]);

            $id = (int)$pdo->lastInsertId();

            return new User($id, $pseudo, $email);

        } catch (PDOException $e) {
            return null;
        }
    }

    public function getHistorique(): array {
        $pdo = Database::getConnexion();

        $sql = "
            SELECT
                s.*,                          -- Toutes les colonnes de scores
                q.titre as theme_titre,       -- Titre du questionnaire
                q.emoji                       -- Emoji du questionnaire
            FROM scores s
            INNER JOIN questionnaires q ON s.questionnaire_id = q.id
            WHERE s.user_id = ?
            ORDER BY s.date_jeu DESC          -- Plus récent en premier
            LIMIT 20                          -- Seulement les 20 derniers
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$this->id]);

        return $stmt->fetchAll();
    }


    public function getDateInscription(): string {
        $pdo = Database::getConnexion();

        $stmt = $pdo->prepare("SELECT created_at FROM users WHERE id = ?");
        $stmt->execute([$this->id]);
        $result = $stmt->fetch();

        return $result['created_at'] ?? '';
    }

    public function getNombreParties(): int {
        $pdo = Database::getConnexion();

        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM scores WHERE user_id = ?");
        $stmt->execute([$this->id]);
        $result = $stmt->fetch();

        return (int)($result['total'] ?? 0);
    }

    public function getThemePreference(): ?array {
        $pdo = Database::getConnexion();

        $sql = "
            SELECT
                q.titre,
                q.emoji,
                COUNT(*) as nb_parties
            FROM scores s
            INNER JOIN questionnaires q ON s.questionnaire_id = q.id
            WHERE s.user_id = ?
            GROUP BY s.questionnaire_id, q.titre, q.emoji
            ORDER BY nb_parties DESC
            LIMIT 1
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$this->id]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function updatePseudo(string $nouveauPseudo): bool {
        $pdo = Database::getConnexion();

        try {

            $stmt = $pdo->prepare("UPDATE users SET pseudo = ? WHERE id = ?");
            $stmt->execute([$nouveauPseudo, $this->id]);


            $this->pseudo = $nouveauPseudo;

            return true;

        } catch (PDOException $e) {
            return false;
        }
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getPseudo(): string {
        return $this->pseudo;
    }

    public function getEmail(): string {
        return $this->email;
    }
}
