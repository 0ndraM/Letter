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
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="cs">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="theme-color" content="#0f172a">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <title>Dopis nenalezen | LETTER</title>
        <link rel="manifest" href="manifest.webmanifest">
        <link rel="apple-touch-icon" href="icons/icon-192.png">
        <link rel="stylesheet" href="style.css">
        <style>
            body {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 16px;
                box-sizing: border-box;
            }

            .error-card {
                max-width: 760px;
                width: 100%;
                background: var(--paper-color);
                border-radius: 4px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.18);
                padding: 40px 28px;
                color: var(--text-color);
                text-align: center;
                line-height: 1.8;
            }

            .error-code {
                color: var(--danger);
                font-size: 0.8rem;
                letter-spacing: 1px;
                margin-bottom: 18px;
            }

            .error-title {
                font-size: 1rem;
                margin: 0 0 20px;
            }

            .error-text {
                font-size: 0.72rem;
                margin: 0;
            }

            .back-link {
                display: inline-block;
                margin-top: 26px;
                text-decoration: none;
                color: #6b7280;
                font-size: 0.68rem;
            }
        </style>
    </head>
    <body>
        <main class="error-card">
            <div class="error-code">CHYBA 404</div>
            <h1 class="error-title">Dopis nenalezen</h1>
            <p class="error-text">Dopis s tímto kódem neexistuje. Možná byl smazán nebo máte špatný odkaz.</p>
            <a class="back-link" href="index.php">← Zpět na hlavní stranu</a>
        </main>
        <script src="pwa-register.js"></script>
    </body>
    </html>
    <?php
    exit;
}

// 3. Kontrola, zda je aktuální návštěvník autorem dopisu
$isOwner = (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $letter['user_id']);
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0f172a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Dopis od <?php echo htmlspecialchars($letter['username']); ?> | LETTER</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>✉️</text></svg>">
    <link rel="manifest" href="manifest.webmanifest">
    <link rel="apple-touch-icon" href="icons/icon-192.png">
    <link rel="stylesheet" href="style.css">
    <style>
        .letter-container { 
            min-height: 500px; 
            line-height: 1.6;
            padding: 50px 40px;
        }

        .author-header { 
            margin-bottom: 30px;
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

        .btn-edit { background: var(--accent); color: white; }

        /* Styly pro formátovaný obsah z editoru */
        .letter-content { font-size: 16px; overflow-wrap: break-word; }
        .letter-content p { margin-bottom: 1em; }
        .ql-align-center { text-align: center; }
        .ql-align-right { text-align: right; }
        .ql-align-justify { text-align: justify; }

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
        <?php if ($isOwner): ?>
        <button class="btn btn-outline" onclick="copyLink()">📋 Kopírovat odkaz pro sdílení</button>
        
        <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-edit">✏️ Upravit můj dopis</a>
        <button class="btn btn-outline btn-danger" onclick="if(confirm('Opravdu chcete smazat tento dopis? Tuto akci nelze vrátit.')) window.location.href='delete.php?id=<?php echo $id; ?>'">🗑️ Smazat dopis</button>
        <?php endif; ?>
        
        <div class="mt-20">
            <a href="index.php" class="text-muted no-decoration fs-12">← Zpět na hlavní stranu</a>
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
    <script src="pwa-register.js"></script>
</body>
</html>