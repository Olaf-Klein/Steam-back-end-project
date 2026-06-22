<?php

require_once __DIR__ . "/DB_access.php";
require_once __DIR__ . "/LibraryLogic.php";

ensurePlayerLibrariesTable($pdo);

function getPlayerLibraries(PDO $pdo): array
{
    $statement = $pdo->prepare(
        "SELECT
            u.id AS user_id,
            u.username,
            u.email,
            pl.id AS library_id,
            pl.added_at,
            g.id AS game_id,
            g.title,
            g.genre,
            g.image
         FROM users u
         LEFT JOIN player_libraries pl ON pl.user_id = u.id
         LEFT JOIN games g ON g.id = pl.game_id
         ORDER BY u.username ASC, g.title ASC"
    );
    $statement->execute();

    $players = [];

    foreach ($statement->fetchAll() as $row) {
        $userId = (int) $row["user_id"];

        if (!isset($players[$userId])) {
            $players[$userId] = [
                "id" => $userId,
                "username" => $row["username"],
                "email" => $row["email"],
                "games" => [],
            ];
        }

        if ($row["library_id"] !== null) {
            $players[$userId]["games"][] = [
                "library_id" => (int) $row["library_id"],
                "game_id" => (int) $row["game_id"],
                "title" => $row["title"],
                "genre" => $row["genre"],
                "image" => $row["image"],
                "added_at" => $row["added_at"],
            ];
        }
    }

    return array_values($players);
}

function removeLibraryGame(PDO $pdo, int $libraryId): bool
{
    $statement = $pdo->prepare("DELETE FROM player_libraries WHERE id = :library_id");
    $statement->execute(["library_id" => $libraryId]);

    return $statement->rowCount() > 0;
}

function redirectWithAdminMessage(string $message, string $type): never
{
    $_SESSION["flash_message"] = $message;
    $_SESSION["flash_type"] = $type;
    header("Location: Admin.php");
    exit;
}
