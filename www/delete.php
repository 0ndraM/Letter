<?php
require 'db.php';

// 1. Kontrola přihlášení a ID
$id = $_GET['id'] ?? '';
if (!isset($_SESSION['user_id']) || empty($id)) {
    header("Location: index.php");
    exit;
}

// 2. Kontrola, zda dopis existuje a patří přihlášenému uživateli
$stmt = $pdo->prepare("SELECT * FROM content_table WHERE id = ?");
$stmt->execute([$id]);
$letter = $stmt->fetch();

if (!$letter || $letter['user_id'] != $_SESSION['user_id']) {
    die("Nemáte oprávnění smazat tento dopis nebo dopis neexistuje.");
}

// 3. Smazání dopisu z databáze
$delete = $pdo->prepare("DELETE FROM content_table WHERE id = ?");
if ($delete->execute([$id])) {
    // Přesměrování zpět na hlavní stranu s oznámením
    header("Location: index.php?deleted=1");
    exit;
} else {
    die("Chyba při mazání dopisu z databáze.");
}
?>
