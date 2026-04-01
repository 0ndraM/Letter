<?php
require 'db.php';

// 1. Kontrola přihlášení a ID
$id = $_GET['id'] ?? '';
if (!isset($_SESSION['user_id']) || empty($id)) {
    header("Location: index.php");
    exit;
}

// 2. Načtení dopisu
$stmt = $pdo->prepare("SELECT * FROM content_table WHERE id = ?");
$stmt->execute([$id]);
$letter = $stmt->fetch();

// 3. Kontrola, zda dopis existuje a patří přihlášenému uživateli
if (!$letter || $letter['user_id'] != $_SESSION['user_id']) {
    die("Nemáte oprávnění upravovat tento dopis nebo dopis neexistuje.");
}

// 4. Zpracování uložení změn
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['letter_content'])) {
    $newContent = $_POST['letter_content'];
    $update = $pdo->prepare("UPDATE content_table SET letter_text = ? WHERE id = ?");
    if ($update->execute([$newContent, $id])) {
        header("Location: view.php?id=" . $id);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upravit dopis | LETTER</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        .letter-container { margin-bottom: 8px; }
        #editor { height: 400px; background: white; }
        .btn-save { background: #10b981; color: white; }
        .btn-cancel { }
        .actions { display: flex; gap: 10px; margin-top: 20px; text-align: left; margin-bottom: 0; }
    </style>
</head>
<body>
    <main class="letter-container">
        <div class="fs-12 text-muted mb-20">REŽIM ÚPRAV</div>
        <form method="POST" id="editForm">
            <input type="hidden" name="letter_content" id="letter_content">
            <div id="editor"><?php echo $letter['letter_text']; ?></div>
            <div class="actions">
                <button type="submit" class="btn btn-save">Uložit změny</button>
                <a href="view.php?id=<?php echo $id; ?>" class="btn btn-cancel">Zrušit</a>
                <button type="button" class="btn btn-cancel btn-danger ml-10" onclick="if(confirm('Opravdu chcete smazat tento dopis? Tuto akci nelze vrátit.')) window.location.href='delete.php?id=<?php echo $id; ?>'">🗑️ Smazat</button>
            </div>
        </form>
    </main>

    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        var quill = new Quill('#editor', { theme: 'snow', } );
        document.getElementById('editForm').onsubmit = function() {
            document.getElementById('letter_content').value = quill.root.innerHTML;
        };
    </script>
</body>
</html>