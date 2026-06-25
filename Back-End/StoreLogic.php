<?php

require_once __DIR__ . "/DB_access.php";

function ensureStoreGameColumns(PDO $pdo): void
{
    $columns = getTableColumns($pdo, "games");

    if (!in_array("sale_price", $columns, true)) {
        $pdo->exec("ALTER TABLE games ADD sale_price DECIMAL(6, 2) NULL AFTER price");
    }

    if (!in_array("is_active", $columns, true)) {
        $pdo->exec("ALTER TABLE games ADD is_active BOOLEAN NOT NULL DEFAULT TRUE AFTER sale_price");
    }
}

function getStoreGames(PDO $pdo): array
{
    ensureStoreGameColumns($pdo);
    $columns = getTableColumns($pdo, "games");

    $imageColumn = in_array("image", $columns, true) ? "image" : null;
    if ($imageColumn === null && in_array("cover_image", $columns, true)) {
        $imageColumn = "cover_image";
    }

    $selectFields = [
        "id",
        "title",
        in_array("description", $columns, true) ? "description" : "'' AS description",
        in_array("price", $columns, true) ? "price" : "0.00 AS price",
        in_array("sale_price", $columns, true) ? "sale_price" : "NULL AS sale_price",
        $imageColumn !== null ? "$imageColumn AS image" : "'' AS image",
        in_array("genre", $columns, true) ? "genre" : "'' AS genre",
    ];

    $orderBy = in_array("title", $columns, true) ? "title ASC" : "id ASC";

    $statement = $pdo->prepare(
        "SELECT " . implode(", ", $selectFields) . "
         FROM games
         WHERE " . (in_array("is_active", $columns, true) ? "is_active = TRUE" : "1 = 1") . "
         ORDER BY $orderBy"
    );

    $statement->execute();

    return $statement->fetchAll();
}

function getCartGames(PDO $pdo, ?int $userId): array
{
    ensureStoreGameColumns($pdo);

    if ($userId === null) {
        return [];
    }

    $statement = $pdo->prepare(
        "SELECT g.id, g.title, g.price, g.sale_price, g.image
         FROM cart c
         INNER JOIN games g ON g.id = c.game_id
         WHERE c.user_id = :user_id
         ORDER BY g.title ASC"
    );
    $statement->execute(["user_id" => $userId]);

    return $statement->fetchAll();
}

function getCartTotal(array $cartGames): float
{
    $total = 0.0;

    foreach ($cartGames as $game) {
        $salePrice = $game["sale_price"] ?? null;
        $total += $salePrice !== null ? (float) $salePrice : (float) $game["price"];
    }

    return $total;
}

function getTableColumns(PDO $pdo, string $tableName): array
{
    if (!preg_match("/^[a-zA-Z0-9_]+$/", $tableName)) {
        return [];
    }

    $statement = $pdo->prepare("SHOW COLUMNS FROM $tableName");
    $statement->execute();

    return array_column($statement->fetchAll(), "Field");
}

$currentUserId = $_SESSION["user_id"] ?? null;
$games = getStoreGames($pdo);
$cartGames = getCartGames($pdo, $currentUserId !== null ? (int) $currentUserId : null);
$cartTotal = getCartTotal($cartGames);
