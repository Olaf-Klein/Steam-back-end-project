<?php
session_start();

$flashMessage = $_SESSION["flash_message"] ?? "";
$flashType = $_SESSION["flash_type"] ?? "info";
unset($_SESSION["flash_message"], $_SESSION["flash_type"]);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create account - A Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="styling/Global.css">
    <link rel="stylesheet" href="styling/Sign-up.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous" defer></script>
</head>

<body class="signup-page">
    <?php require __DIR__ . "/Partials/Navbar.php"; ?>

    <main class="signup-stage">
        <section class="container-lg">
            <div class="row align-items-center justify-content-between g-4 g-lg-5">
                <div class="col-lg-6">
                    <div class="signup-copy">
                        <span class="signup-kicker app-kicker">Join in on the fun.</span>
                        <h1>Jump back into your library</h1>
                        <p>
                            Build your library, browse new releases, and keep your games ready in one place.
                        </p>
                        <div class="signup-highlights" aria-label="Account benefits">
                            <span><i class="bi bi-lightning-charge-fill"></i> Fast checkout</span>
                            <span><i class="bi bi-cloud-arrow-down-fill"></i> Library access</span>
                            <span><i class="bi bi-stars"></i> New releases</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-9 col-lg-5 mx-auto mx-lg-0">
                    <div class="signup-glass-panel app-glass">
                        <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
                            <div>
                                <p class="signup-panel-label app-kicker mb-1">Existing account</p>
                                <h2 class="h4 mb-0">Sign in</h2>
                            </div>
                            <div class="signup-emblem" aria-hidden="true">
                                <i class="bi bi-person-plus-fill"></i>
                            </div>
                        </div>

                        <?php if ($flashMessage !== ""): ?>
                            <div class="alert alert-<?= htmlspecialchars($flashType, ENT_QUOTES, "UTF-8") ?> py-2" role="alert">
                                <?= htmlspecialchars($flashMessage, ENT_QUOTES, "UTF-8") ?>
                            </div>
                        <?php endif; ?>

                        <form id="loginForm" action="../Back-End/Auth.php" method="post" autocomplete="on">
                            <input type="hidden" name="action" value="login">
                            <div class="mb-3">
                                <label class="form-label" for="username">Username or email</label>
                                <input type="text" class="form-control signup-control" id="username" name="username"
                                    placeholder="Username or email" autocomplete="username" minlength="3" required>
                            </div>
                            <div class="mb-1 position-relative">
                                <label class="form-label" for="password">Password</label>
                                <input type="password" class="form-control signup-control pe-5" id="password" name="password"
                                    placeholder="Password" autocomplete="new-password" minlength="6" required>
                                <button type="button" class="btn btn-link position-absolute end-0 signup-password-toggle"
                                    id="togglePassword" tabindex="-1" aria-label="Show password">
                                    <i class="bi bi-eye" id="togglePasswordIcon"></i>
                                </button>
                            </div>
                            <div id="registerMsg" class="mb-3 text-center small signup-message" aria-live="polite"></div>
                            <button type="submit" class="btn signup-submit app-gradient-button w-100">
                                <i class="bi bi-person me-1"></i>Sign in
                            </button>
                        </form>

                        <div class="mt-3 text-center signup-alt">
                            <span>Don't have an account yet? <a href="Sign-up.php">Sign up</a></span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>

</html>
