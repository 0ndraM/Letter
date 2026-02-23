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
    <title>Upravit dopis | Chronos</title>
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        /* Použij stejné CSS jako u write.php */
        :root { --bg-color: #f3f4f6; --paper-color: #fdfaf0; --text-color: #1f2937; --accent: #4f46e5; }
        @media (prefers-color-scheme: dark) { :root { --bg-color: #000000; } }
        body { font-family: 'Menlo', monospace; background: var(--bg-color); margin: 0; display: flex; flex-direction: column; min-height: 100vh; }
        .letter-container { max-width: 800px; width: calc(100% - 16px); margin: 8px auto; background: var(--paper-color); padding: 50px 40px; border-radius: 4px; box-sizing: border-box; }
        #editor { height: 400px; background: white; }
        .btn { padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-family: inherit; font-size: 14px; text-decoration: none; display: inline-block; }
        .btn-save { background: #10b981; color: white; }
        .btn-cancel { background: rgba(0,0,0,0.05); color: #6b7280; border: 1px solid #d1d5db; }
        .actions { display: flex; gap: 10px; margin-top: 20px; }
    </style>
</head>
<body>
    <main class="letter-container">
        <div style="font-size: 0.75rem; color: #9ca3af; margin-bottom: 20px;">REŽIM ÚPRAV</div>
        <form method="POST" id="editForm">
            <input type="hidden" name="letter_content" id="letter_content">
            <div id="editor"><?php echo $letter['letter_text']; ?></div>
            <div class="actions">
                <button type="submit" class="btn btn-save">Uložit změny</button>
                <a href="view.php?id=<?php echo $id; ?>" class="btn btn-cancel">Zrušit</a>
            </div>
        </form>
    </main>

    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        var quill = new Quill('#editor', { theme: 'snow', modules: { toolbar: [['bold', 'italic', 'underline'], [{ 'align': [] }], [{ 'list': 'ordered'}, { 'list': 'bullet' }]] } });
        document.getElementById('editForm').onsubmit = function() {
            document.getElementById('letter_content').value = quill.root.innerHTML;
        };
    </script>
</body>
</html>