<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . "/DB_access.php";
require_once __DIR__ . "/LibraryLogic.php";
require_once __DIR__ . "/StoreLogic.php";

ensurePlayerLibrariesTable($pdo);
ensureStoreGameColumns($pdo);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirectWithMessage("../Front-End/Store.php", "Invalid checkout action.", "danger");
}

if (!isset($_SESSION["user_id"])) {
    redirectWithMessage("../Front-End/Login.php", "Please log in before buying games.", "danger");
}

$userId = (int) $_SESSION["user_id"];
$action = $_POST["action"] ?? "";

if ($action === "add_to_cart") {
    addToCart($pdo, $userId);
}

if ($action === "checkout") {
    $_SESSION["flash_message"] = "Open your cart to checkout.";
    $_SESSION["flash_type"] = "info";
    header("Location: ../Front-End/Checkout.php");
    exit;
}

redirectWithMessage("../Front-End/Store.php", "Unknown checkout action.", "danger");

function addToCart(PDO $pdo, int $userId): void
{
    $gameId = (int) ($_POST["game_id"] ?? 0);

    if ($gameId <= 0 || !gameExists($pdo, $gameId)) {
        redirectWithMessage("../Front-End/Store.php", "Game not found.", "danger");
    }

    if (userOwnsGame($pdo, $userId, $gameId)) {
        redirectWithMessage("../Front-End/Store.php", "This game is already in your library.", "info");
    }

    if (gameIsInCart($pdo, $userId, $gameId)) {
        redirectWithMessage("../Front-End/Store.php", "This game is already in your cart.", "info");
    }

    $statement = $pdo->prepare(
        "INSERT INTO cart (user_id, game_id) VALUES (:user_id, :game_id)"
    );
    $statement->execute([
        "user_id" => $userId,
        "game_id" => $gameId,
    ]);

    redirectWithMessage("../Front-End/Store.php", "Game added to your cart.", "success");
}

function gameExists(PDO $pdo, int $gameId): bool
{
    $statement = $pdo->prepare("SELECT id FROM games WHERE id = :game_id AND is_active = TRUE LIMIT 1");
    $statement->execute(["game_id" => $gameId]);

    return (bool) $statement->fetch();
}

function gameIsInCart(PDO $pdo, int $userId, int $gameId): bool
{
    $statement = $pdo->prepare(
        "SELECT id FROM cart WHERE user_id = :user_id AND game_id = :game_id LIMIT 1"
    );
    $statement->execute([
        "user_id" => $userId,
        "game_id" => $gameId,
    ]);

    return (bool) $statement->fetch();
}

function userOwnsGame(PDO $pdo, int $userId, int $gameId): bool
{
    $statement = $pdo->prepare(
        "SELECT id
         FROM player_libraries
         WHERE user_id = :user_id AND game_id = :game_id
         LIMIT 1"
    );
    $statement->execute([
        "user_id" => $userId,
        "game_id" => $gameId,
    ]);

    return (bool) $statement->fetch();
}

function redirectWithMessage(string $location, string $message, string $type): never
{
    $_SESSION["flash_message"] = $message;
    $_SESSION["flash_type"] = $type;
    header("Location: " . $location);
    exit;
}
