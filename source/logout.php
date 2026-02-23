<?php
require 'db.php';

// 1. Vymažeme všechna data ze session
$_SESSION = array();

// 2. Pokud používáte cookies pro session, smažeme i ty
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Zničíme session úplně
session_destroy();

// 4. Přesměrujeme uživatele zpět na úvodní stránku
header("Location: index.php");
exit;