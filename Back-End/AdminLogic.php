<?php

require_once __DIR__ . "/DB_access.php";
require_once __DIR__ . "/LibraryLogic.php";
require_once __DIR__ . "/StoreLogic.php";

ensurePlayerLibrariesTable($pdo);
ensureStoreGameColumns($pdo);

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

function getAdminStoreGames(PDO $pdo): array
{
    ensureStoreGameColumns($pdo);

    $statement = $pdo->prepare(
        "SELECT id, title, description, price, sale_price, is_active, image, genre
         FROM games
         ORDER BY title ASC"
    );
    $statement->execute();

    return $statement->fetchAll();
}

function addStoreGame(PDO $pdo, array $data): array
{
    $validated = validateStoreGameData($data);

    if (!$validated["success"]) {
        return $validated;
    }

    ensureStoreGameColumns($pdo);

    $statement = $pdo->prepare(
        "INSERT INTO games (title, description, price, sale_price, is_active, image, genre)
         VALUES (:title, :description, :price, NULL, TRUE, :image, :genre)"
    );
    $statement->execute([
        "title" => $validated["game"]["title"],
        "description" => $validated["game"]["description"],
        "price" => $validated["game"]["price"],
        "image" => $validated["game"]["image"],
        "genre" => $validated["game"]["genre"],
    ]);

    return ["success" => true, "message" => "Game added to the store."];
}

function updateStoreGame(PDO $pdo, int $gameId, array $data): array
{
    $validated = validateStoreGameData($data);

    if (!$validated["success"]) {
        return $validated;
    }

    ensureStoreGameColumns($pdo);

    $statement = $pdo->prepare(
        "UPDATE games
         SET title = :title,
             description = :description,
             price = :price,
             image = :image,
             genre = :genre,
             sale_price = NULL
         WHERE id = :game_id"
    );
    $statement->execute([
        "title" => $validated["game"]["title"],
        "description" => $validated["game"]["description"],
        "price" => $validated["game"]["price"],
        "image" => $validated["game"]["image"],
        "genre" => $validated["game"]["genre"],
        "game_id" => $gameId,
    ]);

    return ["success" => $statement->rowCount() > 0, "message" => $statement->rowCount() > 0 ? "Game information updated." : "Game not found or unchanged."];
}

function validateStoreGameData(array $data): array
{
    $title = trim($data["title"] ?? "");
    $description = trim($data["description"] ?? "");
    $price = trim($data["price"] ?? "");
    $image = trim($data["image"] ?? "");
    $genre = trim($data["genre"] ?? "");

    if ($title === "" || strlen($title) > 120) {
        return ["success" => false, "message" => "Enter a title between 1 and 120 characters."];
    }

    if ($description === "") {
        return ["success" => false, "message" => "Enter a game description."];
    }

    if ($price === "" || !is_numeric($price) || (float) $price < 0) {
        return ["success" => false, "message" => "Enter a valid price of 0 or higher."];
    }

    if ($image === "" || strlen($image) > 500 || !filter_var($image, FILTER_VALIDATE_URL)) {
        return ["success" => false, "message" => "Enter a valid image URL."];
    }

    if ($genre === "" || strlen($genre) > 80) {
        return ["success" => false, "message" => "Enter a genre between 1 and 80 characters."];
    }

    return [
        "success" => true,
        "game" => [
            "title" => $title,
            "description" => $description,
            "price" => (float) $price,
            "image" => $image,
            "genre" => $genre,
        ],
    ];
}

function setGameActive(PDO $pdo, int $gameId, bool $isActive): bool
{
    ensureStoreGameColumns($pdo);

    $statement = $pdo->prepare("UPDATE games SET is_active = :is_active WHERE id = :game_id");
    $statement->execute([
        "is_active" => $isActive ? 1 : 0,
        "game_id" => $gameId,
    ]);

    return $statement->rowCount() > 0;
}

function deleteStoreGame(PDO $pdo, int $gameId): bool
{
    $statement = $pdo->prepare("DELETE FROM games WHERE id = :game_id");
    $statement->execute(["game_id" => $gameId]);

    return $statement->rowCount() > 0;
}

function setGameSalePercentage(PDO $pdo, int $gameId, ?float $discountPercent): array
{
    ensureStoreGameColumns($pdo);

    if ($discountPercent === null) {
        $statement = $pdo->prepare("UPDATE games SET sale_price = NULL WHERE id = :game_id");
        $statement->execute(["game_id" => $gameId]);

        return ["success" => true, "message" => "Sale removed."];
    }

    if ($discountPercent <= 0 || $discountPercent > 100) {
        return ["success" => false, "message" => "Sale discount must be between 1 and 100 percent."];
    }

    $gameStatement = $pdo->prepare("SELECT price FROM games WHERE id = :game_id LIMIT 1");
    $gameStatement->execute(["game_id" => $gameId]);
    $game = $gameStatement->fetch();

    if (!$game) {
        return ["success" => false, "message" => "Game not found."];
    }

    $salePrice = round((float) $game["price"] * (1 - ($discountPercent / 100)), 2);

    $statement = $pdo->prepare("UPDATE games SET sale_price = :sale_price WHERE id = :game_id");
    $statement->execute([
        "sale_price" => $salePrice,
        "game_id" => $gameId,
    ]);

    return ["success" => true, "message" => "Sale started with " . rtrim(rtrim(number_format($discountPercent, 2), "0"), ".") . "% off."];
}

function redirectWithAdminMessage(string $message, string $type): never
{
    $_SESSION["flash_message"] = $message;
    $_SESSION["flash_type"] = $type;
    header("Location: Admin.php");
    exit;
}
