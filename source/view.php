<?php
require 'db.php';

// 1. Získání ID z URL (očekáváme ten náhodný token, např. view.php?id=4f2a9b1c5e)
$id = $_GET['id'] ?? '';

if (empty($id)) {
    header("Location: index.php");
    exit;
}

// 2. Načtení dopisu a autora z databáze
$stmt = $pdo->prepare("SELECT c.*, u.username FROM content_table c JOIN users u ON c.user_id = u.id WHERE c.id = ?");
$stmt->execute([$id]);
$letter = $stmt->fetch();

if (!$letter) {
    die("Dopis s tímto kódem neexistuje. Možná byl smazán nebo máte špatný odkaz.");
}

// 3. Kontrola, zda je aktuální návštěvník autorem dopisu
$isOwner = (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $letter['user_id']);
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dopis od <?php echo htmlspecialchars($letter['username']); ?> | Chronos</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>✉️</text></svg>">
    <style>
        :root { 
            --bg-color: #f3f4f6; 
            --paper-color: #fdfaf0;
            --text-color: #1f2937;
            --accent: #4f46e5;
        }
        
        @media (prefers-color-scheme: dark) { :root { --bg-color: #000000; } }

        body { 
            font-family: 'Menlo', 'Monaco', 'Courier New', monospace; 
            background: var(--bg-color); 
            margin: 0; padding: 0; 
            display: flex; flex-direction: column; min-height: 100vh;
        }

        .letter-container { 
            max-width: 800px; 
            width: calc(100% - 16px); 
            margin: 8px auto 20px auto; 
            background: var(--paper-color); 
            padding: 50px 40px; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.15); 
            min-height: 500px; 
            color: var(--text-color); 
            line-height: 1.6;
            box-sizing: border-box;
            border-radius: 4px;
        }

        @media (max-width: 600px) {
            .letter-container { padding: 30px 20px; }
        }

        .author-header { 
            font-size: 0.75rem; 
            color: #9ca3af; 
            border-bottom: 1px solid rgba(0,0,0,0.05); 
            padding-bottom: 10px; 
            margin-bottom: 30px; 
            letter-spacing: 2px;
        }

        .timestamp-footer { 
            text-align: right; 
            color: #9ca3af; 
            font-size: 0.7rem; 
            margin-top: 40px; 
            border-top: 1px solid rgba(0,0,0,0.05); 
            padding-top: 10px; 
            text-transform: uppercase;
        }

        .btn { padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-family: inherit; font-size: 13px; font-weight: 500; transition: 0.2s; text-decoration: none; display: inline-block; }
        .btn-outline { background: rgba(0,0,0,0.05); color: #6b7280; border: 1px solid #d1d5db; }
        .btn-edit { background: var(--accent); color: white; }

        .actions { text-align: center; margin-top: 30px; margin-bottom: 40px; }

        /* Styly pro formátovaný obsah z editoru */
        .letter-content { font-size: 16px; overflow-wrap: break-word; }
        .letter-content p { margin-bottom: 1em; }
        .ql-align-center { text-align: center; }
        .ql-align-right { text-align: right; }
        .ql-align-justify { text-align: justify; }

        footer { margin-top: auto; padding: 30px 20px; text-align: center; color: #9ca3af; font-size: 11px; }
    </style>
</head>
<body>

    <main class="letter-container">
        <div class="author-header">AUTOR: <?php echo htmlspecialchars(strtoupper($letter['username'])); ?></div>

        <div class="letter-content">
            <?php echo $letter['letter_text']; // Zde vypisujeme HTML obsah z Quillu ?>
        </div>

        <div class="timestamp-footer">
            Poslední záznam: <?php echo date("d. m. Y H:i", strtotime($letter['created_at'])); ?>
        </div>
    </main>

    <div class="actions">
        <button class="btn btn-outline" onclick="copyLink()">📋 Kopírovat odkaz pro sdílení</button>
        
        <?php if ($isOwner): ?>
            <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-edit">✏️ Upravit můj dopis</a>
        <?php endif; ?>
        
        <div style="margin-top: 20px;">
            <a href="index.php" style="color: #9ca3af; text-decoration: none; font-size: 12px;">← Zpět na hlavní stranu</a>
        </div>
    </div>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> <a href="https://0ndra.maweb.eu" target="_blank">0ndra_M_</a></p>
    </footer>

    <script>
function copyLink() {
    const url = window.location.href;

    // Metoda 1: Moderní Clipboard API
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(url).then(() => {
            alert("Odkaz zkopírován!");
        }).catch(err => {
            fallbackCopy(url);
        });
    } else {
        // Metoda 2: Starší přístup (pro HTTP nebo starší prohlížeče)
        fallbackCopy(url);
    }
}

function fallbackCopy(text) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    
    // Zajistíme, aby nebyl vidět a nepřekážel
    textArea.style.position = "fixed";
    textArea.style.left = "-9999px";
    textArea.style.top = "0";
    document.body.appendChild(textArea);
    
    textArea.focus();
    textArea.select();
 
    try {
        const successful = document.execCommand('copy');
        if (successful) {
            alert("Odkaz zkopírován!");
        } else {
           alert("Odkaz nešlo zkopírovat automaticky. Zkopírujte ho prosím z adresního řádku.");
        }
    } catch (err) {
        alert("Chyba při kopírování.");
    }

    
    document.body.removeChild(textArea);
}
    </script>
</body>
</html>