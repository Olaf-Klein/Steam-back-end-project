<?php

require_once __DIR__ . "/DB_access.php";

function ensurePlayerLibrariesTable(PDO $pdo): void
{
    $tableAlreadyExists = tableExists($pdo, "player_libraries");

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS player_libraries (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            game_id INT NOT NULL,
            added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_user_game (user_id, game_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
        )"
    );

    if (!$tableAlreadyExists) {
        $pdo->exec(
            "INSERT IGNORE INTO player_libraries (user_id, game_id)
             SELECT DISTINCT o.user_id, oi.game_id
             FROM order_items oi
             INNER JOIN orders o ON o.id = oi.order_id"
        );
    }
}

function tableExists(PDO $pdo, string $tableName): bool
{
    if (!preg_match("/^[a-zA-Z0-9_]+$/", $tableName)) {
        return false;
    }

    $statement = $pdo->prepare("SHOW TABLES LIKE :table_name");
    $statement->execute(["table_name" => $tableName]);

    return (bool) $statement->fetchColumn();
}

function getLibraryGames(PDO $pdo, ?int $userId = null): array
{
    if ($userId !== null) {
        ensurePlayerLibrariesTable($pdo);

        $statement = $pdo->prepare(
            "SELECT g.id, g.title, g.description, g.price, g.image, g.genre
             FROM player_libraries pl
             INNER JOIN games g ON g.id = pl.game_id
             WHERE pl.user_id = :user_id
             ORDER BY g.title ASC"
        );
        $statement->execute(["user_id" => $userId]);
        return $statement->fetchAll();
    }

    $statement = $pdo->prepare(
        "SELECT id, title, description, price, image, genre
         FROM games
         ORDER BY title ASC"
    );
    $statement->execute();

    return $statement->fetchAll();
}

function getSelectedLibraryGame(array $games, ?int $selectedId): ?array
{
    if (empty($games)) {
        return null;
    }

    foreach ($games as $game) {
        if ((int) $game["id"] === $selectedId) {
            return $game;
        }
    }

    return $games[0];
}
