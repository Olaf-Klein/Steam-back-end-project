<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . "/DB_access.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirectWithMessage("../Front-End/Login.php", "Please submit the form first.", "danger");
}

$action = $_POST["action"] ?? "";

if ($action === "register") {
    registerUser($pdo);
}

if ($action === "login") {
    loginUser($pdo);
}

redirectWithMessage("../Front-End/Login.php", "Unknown authentication action.", "danger");

function registerUser(PDO $pdo): void
{
    $username = trim($_POST["username"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirmPassword"] ?? "";

    if (strlen($username) < 3) {
        redirectWithMessage("../Front-End/Sign-up.php", "Username must be at least 3 characters.", "danger");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirectWithMessage("../Front-End/Sign-up.php", "Please enter a valid email address.", "danger");
    }

    if (strlen($password) < 6) {
        redirectWithMessage("../Front-End/Sign-up.php", "Password must be at least 6 characters.", "danger");
    }

    if ($password !== $confirmPassword) {
        redirectWithMessage("../Front-End/Sign-up.php", "Passwords do not match.", "danger");
    }

    if (userExists($pdo, $username, $email)) {
        redirectWithMessage("../Front-End/Sign-up.php", "Username or email is already in use.", "danger");
    }

    $statement = $pdo->prepare(
        "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)"
    );
    $statement->execute([
        "username" => $username,
        "email" => $email,
        "password" => password_hash($password, PASSWORD_DEFAULT),
    ]);

    redirectWithMessage("../Front-End/Login.php", "Account created. You can sign in now.", "success");
}

function loginUser(PDO $pdo): void
{
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username === "" || $password === "") {
        redirectWithMessage("../Front-End/Login.php", "Enter your username and password.", "danger");
    }

    $statement = $pdo->prepare(
        "SELECT id, username, email, password, IsAdmin
         FROM users
         WHERE username = :login OR email = :login
         LIMIT 1"
    );
    $statement->execute(["login" => $username]);
    $user = $statement->fetch();

    if (!$user || !password_verify($password, $user["password"])) {
        redirectWithMessage("../Front-End/Login.php", "Invalid username/email or password.", "danger");
    }

    session_regenerate_id(true);

    $_SESSION["user_id"] = (int) $user["id"];
    $_SESSION["username"] = $user["username"];
    $_SESSION["email"] = $user["email"];
    $_SESSION["is_admin"] = (bool) $user["IsAdmin"];

    redirectWithMessage("../Front-End/Store.php", "Welcome back, " . $user["username"] . ".", "success");
}

function userExists(PDO $pdo, string $username, string $email): bool
{
    $statement = $pdo->prepare(
        "SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1"
    );
    $statement->execute([
        "username" => $username,
        "email" => $email,
    ]);

    return (bool) $statement->fetch();
}

function redirectWithMessage(string $location, string $message, string $type): never
{
    $_SESSION["flash_message"] = $message;
    $_SESSION["flash_type"] = $type;
    header("Location: " . $location);
    exit;
}
