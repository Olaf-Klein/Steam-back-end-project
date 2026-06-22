<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . "/../Back-End/DB_access.php";
require_once __DIR__ . "/../Back-End/CheckoutLogic.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirectWithMessage("Store.php", "Open je winkelwagen om af te rekenen.", "info");
}

if (!isset($_SESSION["user_id"])) {
    redirectWithMessage("Login.php", "Log eerst in om af te rekenen.", "danger");
}

checkoutCart($pdo, (int) $_SESSION["user_id"]);
