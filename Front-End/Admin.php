<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    $_SESSION["flash_message"] = "Log eerst in als admin.";
    $_SESSION["flash_type"] = "danger";
    header("Location: Login.php");
    exit;
}

if (empty($_SESSION["is_admin"])) {
    $_SESSION["flash_message"] = "Je hebt geen toegang tot de admin pagina.";
    $_SESSION["flash_type"] = "danger";
    header("Location: Store.php");
    exit;
}

require_once __DIR__ . "/../Back-End/AdminLogic.php";

function e(?string $value): string
{
    return htmlspecialchars($value ?? "", ENT_QUOTES, "UTF-8");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";
    $libraryId = (int) ($_POST["library_id"] ?? 0);

    if ($action === "remove_library_game" && $libraryId > 0) {
        $removed = removeLibraryGame($pdo, $libraryId);
        redirectWithAdminMessage($removed ? "Game verwijderd uit bibliotheek." : "Bibliotheek item niet gevonden.", $removed ? "success" : "warning");
    }

    redirectWithAdminMessage("Ongeldige admin actie.", "danger");
}

$flashMessage = $_SESSION["flash_message"] ?? "";
$flashType = $_SESSION["flash_type"] ?? "info";
unset($_SESSION["flash_message"], $_SESSION["flash_type"]);

$players = getPlayerLibraries($pdo);
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
</head>

<body class="admin-page">
    <main class="container-lg py-4 px-3 px-md-4">
        <header class="admin-header">
            <div>
                <p class="app-kicker mb-1">Admin</p>
                <h1 class="h3 mb-0">Player libraries</h1>
            </div>
            <a class="btn app-secondary-button" href="Store.php">
                <i class="bi bi-grid me-1"></i>Store
            </a>
        </header>

        <?php if ($flashMessage !== ""): ?>
            <div class="alert alert-<?= e($flashType) ?> py-2" role="alert">
                <?= e($flashMessage) ?>
            </div>
        <?php endif; ?>

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
                        <p class="admin-empty mb-0">Deze speler heeft nog geen games.</p>
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
    </main>
</body>

</html>
