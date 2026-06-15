<?php

require_once __DIR__ . "/DB_access.php";

function getStoreGames(PDO $pdo): array
{
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
        $imageColumn !== null ? "$imageColumn AS image" : "'' AS image",
        in_array("genre", $columns, true) ? "genre" : "'' AS genre",
    ];

    $orderBy = in_array("title", $columns, true) ? "title ASC" : "id ASC";

    $statement = $pdo->prepare(
        "SELECT " . implode(", ", $selectFields) . "
         FROM games
         ORDER BY $orderBy"
    );

    $statement->execute();

    return $statement->fetchAll();
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

$games = getStoreGames($pdo);
