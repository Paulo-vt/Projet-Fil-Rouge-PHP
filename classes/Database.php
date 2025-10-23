<?php

class Database {

    private static ?PDO $connexion = null;

    public static function getConnexion(): PDO {
        if (self::$connexion === null) {
            try {
                $dsn = "mysql:host=localhost;dbname=quizmusic;charset=utf8mb4";
                $username = "root";
                $password = "";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

                    PDO::ATTR_EMULATE_PREPARES => false,

                    PDO::ATTR_STRINGIFY_FETCHES => false
                ];

                self::$connexion = new PDO($dsn, $username, $password, $options);

            } catch (PDOException $e) {

                die("❌ Erreur de connexion à la base de données : " . $e->getMessage());
            }
        }

        return self::$connexion;
    }
    
    public static function fermerConnexion(): void {
        self::$connexion = null;
    }
}
