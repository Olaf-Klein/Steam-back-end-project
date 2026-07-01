<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    $_SESSION["flash_message"] = "Please log in as an admin first.";
    $_SESSION["flash_type"] = "danger";
    header("Location: Login.php");
    exit;
}

if (empty($_SESSION["is_admin"])) {
    $_SESSION["flash_message"] = "You do not have access to the admin page.";
    $_SESSION["flash_type"] = "danger";
    header("Location: Store.php");
    exit;
}

require_once __DIR__ . "/../Back-End/AdminLogic.php";

function e(?string $value): string
{
    return htmlspecialchars($value ?? "", ENT_QUOTES, "UTF-8");
}

function saleDiscountPercent(array $game): ?int
{
    if ($game["sale_price"] === null || (float) $game["price"] <= 0) {
        return null;
    }

    return (int) round((1 - ((float) $game["sale_price"] / (float) $game["price"])) * 100);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";
    $libraryId = (int) ($_POST["library_id"] ?? 0);

    if ($action === "add_store_game") {
        $result = addStoreGame($pdo, $_POST);
        redirectWithAdminMessage($result["message"], $result["success"] ? "success" : "danger");
    }

    $gameId = (int) ($_POST["game_id"] ?? 0);

    if ($action === "update_store_game" && $gameId > 0) {
        $result = updateStoreGame($pdo, $gameId, $_POST);
        redirectWithAdminMessage($result["message"], $result["success"] ? "success" : "warning");
    }

    if ($action === "disable_game" && $gameId > 0) {
        $updated = setGameActive($pdo, $gameId, false);
        redirectWithAdminMessage($updated ? "Game disabled in the store." : "Game not found.", $updated ? "success" : "warning");
    }

    if ($action === "enable_game" && $gameId > 0) {
        $updated = setGameActive($pdo, $gameId, true);
        redirectWithAdminMessage($updated ? "Game enabled in the store." : "Game not found.", $updated ? "success" : "warning");
    }

    if ($action === "delete_game" && $gameId > 0) {
        $deleted = deleteStoreGame($pdo, $gameId);
        redirectWithAdminMessage($deleted ? "Game removed completely." : "Game not found.", $deleted ? "success" : "warning");
    }

    if ($action === "set_sale" && $gameId > 0) {
        $saleInput = trim($_POST["sale_percent"] ?? "");
        if ($saleInput === "") {
            $result = ["success" => false, "message" => "Enter a discount percentage."];
        } elseif (!is_numeric($saleInput)) {
            $result = ["success" => false, "message" => "Enter a valid discount percentage."];
        } else {
            $result = setGameSalePercentage($pdo, $gameId, (float) $saleInput);
        }
        redirectWithAdminMessage($result["message"], $result["success"] ? "success" : "danger");
    }

    if ($action === "clear_sale" && $gameId > 0) {
        $result = setGameSalePercentage($pdo, $gameId, null);
        redirectWithAdminMessage($result["message"], $result["success"] ? "success" : "danger");
    }

    if ($action === "remove_library_game" && $libraryId > 0) {
        $removed = removeLibraryGame($pdo, $libraryId);
        redirectWithAdminMessage($removed ? "Game removed from library." : "Library item not found.", $removed ? "success" : "warning");
    }

    redirectWithAdminMessage("Invalid admin action.", "danger");
}

$flashMessage = $_SESSION["flash_message"] ?? "";
$flashType = $_SESSION["flash_type"] ?? "info";
unset($_SESSION["flash_message"], $_SESSION["flash_type"]);

$players = getPlayerLibraries($pdo);
$storeGames = getAdminStoreGames($pdo);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Player Libraries</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="styling/Global.css">
    <link rel="stylesheet" href="styling/Admin.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous" defer></script>
</head>

<body class="admin-page">
    <?php require __DIR__ . "/Partials/Navbar.php"; ?>
    <main class="container-lg py-4 px-3 px-md-4">
        <header class="admin-header">
            <div>
                <p class="app-kicker mb-1">Admin</p>
                <h1 class="h3 mb-0">Admin dashboard</h1>
            </div>
        </header>

        <?php if ($flashMessage !== ""): ?>
            <div class="alert alert-<?= e($flashType) ?> py-2" role="alert">
                <?= e($flashMessage) ?>
            </div>
        <?php endif; ?>

        <ul class="nav nav-tabs admin-tabs" id="adminTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="add-game-tab" data-bs-toggle="tab" data-bs-target="#add-game-pane" type="button" role="tab" aria-controls="add-game-pane" aria-selected="true">
                    <i class="bi bi-plus-circle me-1"></i>Add store games
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="libraries-tab" data-bs-toggle="tab" data-bs-target="#libraries-pane" type="button" role="tab" aria-controls="libraries-pane" aria-selected="false">
                    <i class="bi bi-collection me-1"></i>Player libraries
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="store-games-tab" data-bs-toggle="tab" data-bs-target="#store-games-pane" type="button" role="tab" aria-controls="store-games-pane" aria-selected="false">
                    <i class="bi bi-tags me-1"></i>Edit store games
                </button>
            </li>
        </ul>

        <div class="tab-content admin-tab-content" id="adminTabsContent">
            <section class="tab-pane fade show active admin-add-game app-panel" id="add-game-pane" role="tabpanel" aria-labelledby="add-game-tab" tabindex="0">
                <div class="admin-section-heading">
                    <p class="app-kicker mb-1">Store</p>
                    <h2 class="h5 mb-0">Add game</h2>
                </div>
                <form method="post" class="admin-game-form">
                    <input type="hidden" name="action" value="add_store_game">
                    <div>
                        <label class="form-label" for="title">Title</label>
                        <input type="text" class="form-control admin-control" id="title" name="title" maxlength="120" required>
                    </div>
                    <div>
                        <label class="form-label" for="genre">Genre</label>
                        <input type="text" class="form-control admin-control" id="genre" name="genre" maxlength="80" required>
                    </div>
                    <div>
                        <label class="form-label" for="price">Price</label>
                        <input type="number" class="form-control admin-control" id="price" name="price" min="0" step="0.01" value="0.00" required>
                    </div>
                    <div class="admin-form-wide">
                        <label class="form-label" for="image">Image URL</label>
                        <input type="url" class="form-control admin-control" id="image" name="image" maxlength="500" required>
                    </div>
                    <div class="admin-form-wide">
                        <label class="form-label" for="description">Description</label>
                        <textarea class="form-control admin-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="admin-form-actions">
                        <button type="submit" class="btn app-gradient-button">
                            <i class="bi bi-plus-circle me-1"></i>Add game
                        </button>
                    </div>
                </form>
            </section>

            <section class="tab-pane fade" id="store-games-pane" role="tabpanel" aria-labelledby="store-games-tab" tabindex="0">
                <div class="admin-grid">
                    <?php foreach ($storeGames as $game): ?>
                        <article class="admin-store-game app-panel">
                            <img src="<?= e($game["image"]) ?>" alt="<?= e($game["title"]) ?> cover" loading="lazy">
                            <div class="admin-store-game-info">
                                <div>
                                    <strong><?= e($game["title"]) ?></strong>
                                    <span><?= e($game["genre"]) ?></span>
                                </div>
                                <div class="admin-game-prices">
                                    <span class="<?= $game["sale_price"] !== null ? "has-sale" : "" ?>">&euro;<?= e(number_format((float) $game["price"], 2)) ?></span>
                                    <?php if ($game["sale_price"] !== null): ?>
                                        <strong>&euro;<?= e(number_format((float) $game["sale_price"], 2)) ?></strong>
                                        <?php $discountPercent = saleDiscountPercent($game); ?>
                                        <?php if ($discountPercent !== null): ?>
                                            <em><?= $discountPercent ?>% off</em>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                <span class="admin-status <?= (bool) $game["is_active"] ? "active" : "disabled" ?>">
                                    <?= (bool) $game["is_active"] ? "Active" : "Disabled" ?>
                                </span>
                            </div>

                            <div class="admin-store-actions">
                                <form method="post">
                                    <input type="hidden" name="game_id" value="<?= (int) $game["id"] ?>">
                                    <input type="hidden" name="action" value="<?= (bool) $game["is_active"] ? "disable_game" : "enable_game" ?>">
                                    <button type="submit" class="btn btn-sm app-secondary-button">
                                        <?= (bool) $game["is_active"] ? "Disable" : "Enable" ?>
                                    </button>
                                </form>
                                <button class="btn btn-sm app-secondary-button" type="button" data-bs-toggle="collapse" data-bs-target="#editGame<?= (int) $game["id"] ?>" aria-expanded="false" aria-controls="editGame<?= (int) $game["id"] ?>">
                                    Edit
                                </button>
                                <form method="post" class="admin-sale-form">
                                    <input type="hidden" name="action" value="set_sale">
                                    <input type="hidden" name="game_id" value="<?= (int) $game["id"] ?>">
                                    <input type="number" class="form-control admin-control" name="sale_percent" min="1" max="100" step="1" placeholder="% off">
                                    <button type="submit" class="btn btn-sm app-secondary-button">Sale</button>
                                </form>
                                <?php if ($game["sale_price"] !== null): ?>
                                    <form method="post">
                                        <input type="hidden" name="action" value="clear_sale">
                                        <input type="hidden" name="game_id" value="<?= (int) $game["id"] ?>">
                                        <button type="submit" class="btn btn-sm app-secondary-button">Clear sale</button>
                                    </form>
                                <?php endif; ?>
                                <form method="post">
                                    <input type="hidden" name="action" value="delete_game">
                                    <input type="hidden" name="game_id" value="<?= (int) $game["id"] ?>">
                                    <button type="submit" class="btn btn-sm admin-remove-button">
                                        <i class="bi bi-trash3 me-1"></i>Delete
                                    </button>
                                </form>
                            </div>
                            <div class="collapse admin-edit-collapse" id="editGame<?= (int) $game["id"] ?>">
                                <form method="post" class="admin-edit-game-form">
                                    <input type="hidden" name="action" value="update_store_game">
                                    <input type="hidden" name="game_id" value="<?= (int) $game["id"] ?>">
                                    <div>
                                        <label class="form-label" for="edit-title-<?= (int) $game["id"] ?>">Title</label>
                                        <input type="text" class="form-control admin-control" id="edit-title-<?= (int) $game["id"] ?>" name="title" value="<?= e($game["title"]) ?>" maxlength="120" required>
                                    </div>
                                    <div>
                                        <label class="form-label" for="edit-genre-<?= (int) $game["id"] ?>">Genre</label>
                                        <input type="text" class="form-control admin-control" id="edit-genre-<?= (int) $game["id"] ?>" name="genre" value="<?= e($game["genre"]) ?>" maxlength="80" required>
                                    </div>
                                    <div>
                                        <label class="form-label" for="edit-price-<?= (int) $game["id"] ?>">Price</label>
                                        <input type="number" class="form-control admin-control" id="edit-price-<?= (int) $game["id"] ?>" name="price" min="0" step="0.01" value="<?= e(number_format((float) $game["price"], 2, ".", "")) ?>" required>
                                    </div>
                                    <div class="admin-form-wide">
                                        <label class="form-label" for="edit-image-<?= (int) $game["id"] ?>">Thumbnail URL</label>
                                        <input type="url" class="form-control admin-control" id="edit-image-<?= (int) $game["id"] ?>" name="image" value="<?= e($game["image"]) ?>" maxlength="500" required>
                                    </div>
                                    <div class="admin-form-wide">
                                        <label class="form-label" for="edit-description-<?= (int) $game["id"] ?>">Description</label>
                                        <textarea class="form-control admin-control" id="edit-description-<?= (int) $game["id"] ?>" name="description" rows="3" required><?= e($game["description"]) ?></textarea>
                                    </div>
                                    <div class="admin-form-actions">
                                        <button type="submit" class="btn app-gradient-button">
                                            <i class="bi bi-save me-1"></i>Save changes
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="tab-pane fade" id="libraries-pane" role="tabpanel" aria-labelledby="libraries-tab" tabindex="0">
                <div class="admin-grid">
                    <?php foreach ($players as $player): ?>
                        <section class="admin-player app-panel">
                            <div class="admin-player-header">
                                <div>
                                    <h2 class="h5 mb-1"><?= e($player["username"]) ?></h2>
                                    <p class="mb-0"><?= e($player["email"]) ?></p>
                                </div>
                                <span><?= count($player["games"]) ?> game<?= count($player["games"]) === 1 ? "" : "s" ?></span>
                            </div>

                            <?php if (empty($player["games"])): ?>
                                <p class="admin-empty mb-0">This player does not own any games yet.</p>
                            <?php else: ?>
                                <div class="admin-library-list">
                                    <?php foreach ($player["games"] as $game): ?>
                                        <?php $cover = $game["image"] ?: "https://placehold.co/160x240/1b2233/ffffff?text=Game"; ?>
                                        <article class="admin-library-item">
                                            <img src="<?= e($cover) ?>" alt="<?= e($game["title"]) ?> cover" loading="lazy">
                                            <div>
                                                <strong><?= e($game["title"]) ?></strong>
                                                <span><?= e($game["genre"]) ?></span>
                                            </div>
                                            <form method="post">
                                                <input type="hidden" name="action" value="remove_library_game">
                                                <input type="hidden" name="library_id" value="<?= (int) $game["library_id"] ?>">
                                                <button type="submit" class="btn btn-sm admin-remove-button">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </form>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </section>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </main>
</body>

</html>