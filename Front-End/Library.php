<?php
session_start();
require_once __DIR__ . "/../Back-End/LibraryLogic.php";

function e(?string $value): string
{
    return htmlspecialchars($value ?? "", ENT_QUOTES, "UTF-8");
}

$userId = isset($_SESSION["user_id"]) ? (int) $_SESSION["user_id"] : null;
$flashMessage = $_SESSION["flash_message"] ?? "";
$flashType = $_SESSION["flash_type"] ?? "info";
unset($_SESSION["flash_message"], $_SESSION["flash_type"]);
$libraryGames = getLibraryGames($pdo, $userId);
$selectedId = isset($_GET["game_id"]) ? (int) $_GET["game_id"] : null;
$selectedGame = getSelectedLibraryGame($libraryGames, $selectedId);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="styling/Global.css">
    <link rel="stylesheet" href="styling/Library.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</head>

<body class="library-page">
    <?php require __DIR__ . "/Partials/Navbar.php"; ?>
    <div class="library-shell">
        <aside class="library-sidebar">
            <div class="sidebar-games">
                <p class="sidebar-label app-kicker">Games</p>

                <?php if (empty($libraryGames)): ?>
                    <p class="sidebar-empty">No games found.</p>
                <?php else: ?>
                    <?php foreach ($libraryGames as $game): ?>
                        <?php
                        $isActive = $selectedGame !== null && (int) $game["id"] === (int) $selectedGame["id"];
                        $coverImage = $game["image"] ?: "https://placehold.co/160x240/1b2233/ffffff?text=Game";
                        ?>
                        <a href="Library.php?game_id=<?= (int) $game["id"] ?>" class="sidebar-game <?= $isActive ? "active" : "" ?>" <?= $isActive ? 'aria-current="page"' : "" ?>>
                            <img src="<?= e($coverImage) ?>" alt="<?= e($game["title"]) ?> cover" loading="lazy">
                            <span>
                                <strong><?= e($game["title"]) ?></strong>
                                <small><?= e($game["genre"]) ?></small>
                            </span>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </aside>

        <main class="library-content">
            <header class="library-header">
                <div>
                    <p class="library-kicker app-kicker mb-1">Game library</p>
                    <h1 class="h3 mb-0">Your games</h1>
                </div>
            </header>

            <?php if ($flashMessage !== ""): ?>
                <div class="alert alert-<?= e($flashType) ?> py-2" role="alert">
                    <?= e($flashMessage) ?>
                </div>
            <?php endif; ?>

            <?php if ($selectedGame === null): ?>
                <section class="library-empty app-panel">
                    <i class="bi bi-collection-play"></i>
                    <h2 class="h5 mb-2">No games in your library yet</h2>
                    <p class="mb-3">When you buy or claim games, they will show up here.</p>
                    <a class="btn library-button app-gradient-button" href="Store.php">
                        <i class="bi bi-grid me-1"></i>Browse store
                    </a>
                </section>
            <?php else: ?>
                <?php
                $selectedCover = $selectedGame["image"] ?: "https://placehold.co/600x900/1b2233/ffffff?text=Game";
                $price = (float) $selectedGame["price"];
                ?>
                <section class="library-detail">
                    <div class="library-detail-media">
                        <img src="<?= e($selectedCover) ?>" alt="<?= e($selectedGame["title"]) ?> cover">
                    </div>

                    <div class="library-detail-info app-panel">
                        <span class="library-genre"><?= e($selectedGame["genre"]) ?></span>
                        <h2><?= e($selectedGame["title"]) ?></h2>
                        <p><?= e($selectedGame["description"]) ?></p>

                        <div class="library-actions">
                            <a class="btn library-button app-gradient-button" href="#">
                                <i class="bi bi-play-fill me-1"></i>Play
                            </a>
                            <a class="btn library-secondary-button app-secondary-button" href="Store.php">
                                <i class="bi bi-shop me-1"></i>Store page
                            </a>
                        </div>

                        <dl class="library-meta">
                            <div>
                                <dt>Status</dt>
                                <dd>Ready to play</dd>
                            </div>
                            <div>
                                <dt>Genre</dt>
                                <dd><?= e($selectedGame["genre"]) ?></dd>
                            </div>
                            <div>
                                <dt>Price</dt>
                                <dd><?= $price <= 0 ? "Free" : "&euro;" . e(number_format($price, 2)) ?></dd>
                            </div>
                        </dl>
                    </div>
                </section>
            <?php endif; ?>
        </main>
    </div>

</body>

</html>
