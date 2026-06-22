<?php

function checkoutCart(PDO $pdo, int $userId): void
{
    ensurePlayerLibrariesTable($pdo);

    try {
        $pdo->beginTransaction();

        $cartStatement = $pdo->prepare(
            "SELECT DISTINCT g.id, g.price
             FROM cart c
             INNER JOIN games g ON g.id = c.game_id
             WHERE c.user_id = :user_id
             ORDER BY g.id ASC"
        );
        $cartStatement->execute(["user_id" => $userId]);
        $cartGames = $cartStatement->fetchAll();

        if (empty($cartGames)) {
            $pdo->rollBack();
            redirectWithMessage("Store.php", "Je winkelwagen is leeg.", "info");
        }

        $orderStatement = $pdo->prepare("INSERT INTO orders (user_id) VALUES (:user_id)");
        $orderStatement->execute(["user_id" => $userId]);
        $orderId = (int) $pdo->lastInsertId();

        $itemStatement = $pdo->prepare(
            "INSERT INTO order_items (order_id, game_id, price_at_purchase)
             VALUES (:order_id, :game_id, :price_at_purchase)"
        );

        $libraryStatement = $pdo->prepare(
            "INSERT IGNORE INTO player_libraries (user_id, game_id)
             VALUES (:user_id, :game_id)"
        );

        foreach ($cartGames as $game) {
            $itemStatement->execute([
                "order_id" => $orderId,
                "game_id" => (int) $game["id"],
                "price_at_purchase" => (float) $game["price"],
            ]);

            $libraryStatement->execute([
                "user_id" => $userId,
                "game_id" => (int) $game["id"],
            ]);
        }

        $deleteStatement = $pdo->prepare("DELETE FROM cart WHERE user_id = :user_id");
        $deleteStatement->execute(["user_id" => $userId]);

        $pdo->commit();
        redirectWithMessage("Library.php", "Afrekenen gelukt. Je games staan nu in je bibliotheek.", "success");
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        redirectWithMessage("Store.php", "Afrekenen is mislukt. Probeer het opnieuw.", "danger");
    }
}

function ensurePlayerLibrariesTable(PDO $pdo): void
{
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
}

function redirectWithMessage(string $location, string $message, string $type): never
{
    $_SESSION["flash_message"] = $message;
    $_SESSION["flash_type"] = $type;
    header("Location: " . $location);
    exit;
}
