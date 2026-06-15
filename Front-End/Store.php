<?php
require_once __DIR__ . "/../Back-End/StoreLogic.php";

function e(?string $value): string
{
    return htmlspecialchars($value ?? "", ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="styling/Global.css">
    <link rel="stylesheet" href="styling/Store.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark signup-nav">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="Store.php">
                <i class="bi bi-controller me-2 text-info"></i>A Store
            </a>
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
    <main class="container-lg py-4 px-3 px-md-4">
        <div class="store-header d-flex align-items-end justify-content-between gap-3 pb-3 mb-4">
            <div>
                <h2 class="h3 mb-1">Store</h2>
                <p class="text-secondary mb-0">Browse the latest games.</p>
            </div>
        </div>

        <?php if (empty($games)): ?>
            <div class="alert alert-dark border-secondary" role="alert">
                No games found in the store yet.
            </div>
        <?php else: ?>
            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-6 g-4">

                <?php foreach ($games as $game): ?>
                    <?php
                    $coverImage = $game["image"] ?: "https://placehold.co/600x900/1b2233/ffffff?text=No+Cover";
                    ?>

                    <div class="col">
                        <article class="game-card">
                            <img
                                src="<?= e($coverImage) ?>"
                                class="game-cover"
                                alt="<?= e($game["title"]) ?> cover"
                                loading="lazy">

                            <div class="release-badge">
                                <?= e($game["genre"]) ?>
                            </div>

                            <div class="game-info">
                                <h2 class="game-title"><?= e($game["title"]) ?></h2>
                                <p class="game-description text-secondary mt-2 mb-0"><?= e($game["description"]) ?></p>
                                <div class="d-flex align-items-center justify-content-between gap-2 mt-3">
                                    <span class="text-secondary small"><?= e($game["genre"]) ?></span>
                                    <strong class="game-price"><?= ((float) $game["price"] <= 0) ? "Free" : "&euro;" . e(number_format((float) $game["price"], 2)) ?></strong>
                                </div>
                            </div>
                        </article>
                    </div>

                <?php endforeach; ?>

            </div>
        <?php endif; ?>
    </main>

</body>

</html>