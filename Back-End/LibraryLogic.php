<?php

require_once __DIR__ . "/DB_access.php";

function getLibraryGames(PDO $pdo, ?int $userId = null): array
{
    if ($userId !== null) {
        $statement = $pdo->prepare(
            "SELECT DISTINCT g.id, g.title, g.description, g.price, g.image, g.genre
             FROM order_items oi
             INNER JOIN orders o ON o.id = oi.order_id
             INNER JOIN games g ON g.id = oi.game_id
             WHERE o.user_id = :user_id
             ORDER BY g.title ASC"
        );
        $statement->execute(["user_id" => $userId]);
        $games = $statement->fetchAll();

        if (!empty($games)) {
            return $games;
        }
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
