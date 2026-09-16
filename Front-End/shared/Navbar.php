<?php

if (!function_exists("nav_e")) {
    function nav_e(?string $value): string
    {
        return htmlspecialchars($value ?? "", ENT_QUOTES, "UTF-8");
    }
}

$cartCount = isset($cartGames) && is_array($cartGames) ? count($cartGames) : 0;
$isLoggedIn = isset($_SESSION["user_id"]);
$isAdmin = !empty($_SESSION["is_admin"]);
$username = $_SESSION["username"] ?? "Profile";
$email = $_SESSION["email"] ?? "";
$role = $isAdmin ? "Administrator" : "Player";
?>
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
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="Store.php"><i class="bi bi-grid me-1"></i>Store</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="Library.php"><i class="bi bi-collection me-1"></i>Library</a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <?php if ($isLoggedIn): ?>
                    <li class="nav-item dropdown">
                        <button class="nav-link dropdown-toggle profile-nav-button" type="button" title="<?= nav_e($username) ?>" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle fs-5"></i>
                            <span class="ms-1 d-lg-none"><?= nav_e($username) ?></span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end profile-dropdown">
                            <div class="profile-dropdown-header">
                                <strong><?= nav_e($username) ?></strong>
                                <span><?= nav_e($email) ?></span>
                            </div>
                            <button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#profileModal">
                                <i class="bi bi-person me-2"></i>Show profile
                            </button>
                            <?php if ($isAdmin): ?>
                                <a class="dropdown-item" href="Admin.php">
                                    <i class="bi bi-shield-lock me-2"></i>Admin dashboard
                                </a>
                            <?php endif; ?>
                            <hr class="dropdown-divider">
                            <a class="dropdown-item text-danger" href="Logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </a>
                        </div>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="Login.php"><i class="bi bi-box-arrow-in-right me-1"></i>Login</a>
                    </li>
                <?php endif; ?>
            </ul>
            <?php if (basename($_SERVER["SCRIPT_NAME"]) === "Store.php"): ?>
                <button class="btn app-secondary-button ms-lg-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#cartSidebar" aria-controls="cartSidebar">
                    <i class="bi bi-cart3 me-1"></i>Cart
                    <span class="badge text-bg-info ms-1"><?= $cartCount ?></span>
                </button>
            <?php endif; ?>
        </div>
    </div>
</nav>

<?php if ($isLoggedIn): ?>
    <div class="modal fade profile-modal" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content app-glass">
                <div class="modal-header">
                    <div>
                        <p class="app-kicker mb-1">Profile</p>
                        <h2 class="modal-title h5" id="profileModalLabel"><?= nav_e($username) ?></h2>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="profile-modal-avatar" aria-hidden="true">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <dl class="profile-modal-details">
                        <div>
                            <dt>Username</dt>
                            <dd><?= nav_e($username) ?></dd>
                        </div>
                        <div>
                            <dt>Email</dt>
                            <dd><?= nav_e($email) ?></dd>
                        </div>
                        <div>
                            <dt>Role</dt>
                            <dd><?= nav_e($role) ?></dd>
                        </div>
                    </dl>
                </div>
                <div class="modal-footer">
                    <a class="btn app-secondary-button" href="Library.php">
                        <i class="bi bi-collection me-1"></i>Library
                    </a>
                    <?php if ($isAdmin): ?>
                        <a class="btn app-secondary-button" href="Admin.php">
                            <i class="bi bi-shield-lock me-1"></i>Admin
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>