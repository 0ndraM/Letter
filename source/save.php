<?php
require 'db.php';

// 1. Kontrola, zda je uživatel přihlášen
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// 2. Kontrola, zda přišla data z formuláře
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['letter_content'])) {
    $content = $_POST['letter_content'];
    $userId = $_SESSION['user_id'];

    // 3. Generování náhodného ID (tokenu)
    // bin2hex(random_bytes(5)) vytvoří 10místný náhodný řetězec (např. 4f2a9b1c5e)
    try {
        $randomId = bin2hex(random_bytes(5));
    } catch (Exception $e) {
        // Záložní metoda, kdyby random_bytes selhalo (velmi nepravděpodobné)
        $randomId = substr(md5(uniqid(mt_rand(), true)), 0, 10);
    }

    // 4. Uložení do databáze
    $stmt = $pdo->prepare("INSERT INTO content_table (id, user_id, letter_text) VALUES (?, ?, ?)");
    
    if ($stmt->execute([$randomId, $userId, $content])) {
        // 5. Přesměrování na nově vytvořený dopis
        header("Location: view.php?id=" . $randomId);
        exit;
    } else {
        die("Chyba při ukládání dopisu do databáze.");
    }
} else {
    // Pokud se někdo pokusí přistoupit přímo k save.php bez dat
    header("Location: index.php");
    exit;
}