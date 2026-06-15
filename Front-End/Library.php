<?php
session_start();
require_once __DIR__ . "/../Back-End/LibraryLogic.php";

function e(?string $value): string
{
    return htmlspecialchars($value ?? "", ENT_QUOTES, "UTF-8");
}

$userId = isset($_SESSION["user_id"]) ? (int) $_SESSION["user_id"] : null;
$username = $_SESSION["username"] ?? "Player";
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
    <nav class="navbar navbar-expand-lg navbar-dark signup-nav">
        <div class="container-lg">
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
                aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="Store.php"><i class="bi bi-grid me-1"></i>Store</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="Library.php"><i class="bi bi-collection me-1"></i>Library</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php"><i class="bi bi-box-arrow-in-right me-1"></i>Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="library-shell">
        <aside class="library-sidebar">
            <a class="sidebar-brand" href="Store.php">
                <i class="bi bi-controller"></i>
                <span>A Store</span>
            </a>
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

            <div class="sidebar-user">
                <div class="sidebar-avatar">
                    <i class="bi bi-person-fill"></i>
                </div>
                <div>
                    <strong><?= e($username) ?></strong>
                    <span>Online</span>
                </div>
            </div>
        </aside>

        <main class="library-content">
            <header class="library-header">
                <div>
                    <p class="library-kicker app-kicker mb-1">Game library</p>
                    <h1 class="h3 mb-0">Your games</h1>
                </div>
            </header>

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