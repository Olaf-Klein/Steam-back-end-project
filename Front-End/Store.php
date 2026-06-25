<?php
session_start();

require_once __DIR__ . "/../Back-End/StoreLogic.php";

function e(?string $value): string
{
    return htmlspecialchars($value ?? "", ENT_QUOTES, "UTF-8");
}

function formatPrice(float $price): string
{
    return $price <= 0 ? "Free" : "&euro;" . e(number_format($price, 2));
}

function currentGamePrice(array $game): float
{
    return $game["sale_price"] !== null ? (float) $game["sale_price"] : (float) $game["price"];
}

function saleDiscountPercent(array $game): ?int
{
    if ($game["sale_price"] === null || (float) $game["price"] <= 0) {
        return null;
    }

    return (int) round((1 - ((float) $game["sale_price"] / (float) $game["price"])) * 100);
}

$flashMessage = $_SESSION["flash_message"] ?? "";
$flashType = $_SESSION["flash_type"] ?? "info";
unset($_SESSION["flash_message"], $_SESSION["flash_type"]);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="styling/Global.css">
    <link rel="stylesheet" href="styling/Store.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

</head>

<body>
    <?php require __DIR__ . "/Partials/Navbar.php"; ?>
    <main class="container-lg py-4 px-3 px-md-4">
        <div class="store-header d-flex align-items-end justify-content-between gap-3 pb-3 mb-4">
            <div>
                <h2 class="h3 mb-1">Store</h2>
                <p class="text-secondary mb-0">Browse the latest games.</p>
            </div>
        </div>

        <?php if ($flashMessage !== ""): ?>
            <div class="alert alert-<?= e($flashType) ?> py-2" role="alert">
                <?= e($flashMessage) ?>
            </div>
        <?php endif; ?>

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
                                    <strong class="game-price">
                                        <?= formatPrice(currentGamePrice($game)) ?>
                                        <?php $discountPercent = saleDiscountPercent($game); ?>
                                        <?php if ($discountPercent !== null): ?>
                                            <em><?= $discountPercent ?>% off</em>
                                        <?php endif; ?>
                                    </strong>
                                </div>
                                <form action="../Back-End/Checkout.php" method="post" class="mt-3">
                                    <input type="hidden" name="action" value="add_to_cart">
                                    <input type="hidden" name="game_id" value="<?= (int) $game["id"] ?>">
                                    <button type="submit" class="btn app-secondary-button add-cart-button w-100">
                                        <i class="bi bi-cart-plus me-1"></i>Add to cart
                                    </button>
                                </form>
                            </div>
                        </article>
                    </div>

                <?php endforeach; ?>

            </div>
        <?php endif; ?>
    </main>

    <aside class="offcanvas offcanvas-end cart-offcanvas" tabindex="-1" id="cartSidebar" aria-labelledby="cartSidebarLabel">
        <div class="offcanvas-header">
            <div>
                <p class="app-kicker mb-1">Cart</p>
                <h2 class="offcanvas-title h5" id="cartSidebarLabel"><?= count($cartGames) ?> game<?= count($cartGames) === 1 ? "" : "s" ?></h2>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <?php if (empty($cartGames)): ?>
                <div class="cart-empty app-panel">
                    <i class="bi bi-cart3"></i>
                    <p class="mb-0">Your cart is empty.</p>
                </div>
            <?php else: ?>
                <div class="cart-items">
                    <?php foreach ($cartGames as $cartGame): ?>
                        <?php $cartCover = $cartGame["image"] ?: "https://placehold.co/160x240/1b2233/ffffff?text=Game"; ?>
                        <div class="cart-item">
                            <img
                                src="<?= e($cartCover) ?>"
                                class="cart-cover"
                                alt="<?= e($cartGame["title"]) ?> cover"
                                width="36"
                                height="54"
                                loading="lazy">
                            <div>
                                <strong><?= e($cartGame["title"]) ?></strong>
                                <span>
                                    <?= formatPrice(currentGamePrice($cartGame)) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="cart-footer">
            <div class="cart-total">
                <span>Total</span>
                <strong><?= formatPrice($cartTotal) ?></strong>
            </div>
            <form action="Checkout.php" method="post">
                <button type="submit" class="btn app-gradient-button checkout-button w-100" <?= empty($cartGames) ? "disabled" : "" ?>>
                    <i class="bi bi-credit-card me-1"></i>Checkout
                </button>
            </form>
        </div>
    </aside>

</body>

</html>
