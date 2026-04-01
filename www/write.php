<?php
require 'db.php';

// Ochrana: Pokud není uživatel přihlášen, pošleme ho na hlavní stranu
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$username = $_SESSION['username'] ?? 'Poutník';
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nový dopis | LETTER</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>✉️</text></svg>">
    <link rel="stylesheet" href="style.css">
    
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    
    <style>
        .letter-container { min-height: 600px; }

        /* Upravený Quill editor aby ladil k papíru */
        #editor { 
            height: 400px; 
            background: white; 
            font-family: 'Menlo', monospace; 
            font-size: 16px;
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
        }
        
        .ql-toolbar.ql-snow { 
            background: #f9fafb; 
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            font-family: sans-serif;
        }

        .btn-save { background: #10b981; color: white; flex: 1; }
        .btn-save:hover { background: #059669; }
        .btn-cancel { flex: 0 0 auto; }

        .actions { display: flex; gap: 10px; }
    </style>
</head>
<body>

    <main class="letter-container">
        <div class="author-header">NOVÝ ZÁZNAM | AUTOR: <?php echo htmlspecialchars(strtoupper($username)); ?></div>

        <form action="save.php" method="POST" id="letterForm">
            <input type="hidden" name="letter_content" id="letter_content">
            
            <div id="editor">
                
            </div>

            <div class="actions">
                <button type="submit" class="btn btn-save">Publikovat a vygenerovat odkaz</button>
                <a href="index.php" class="btn btn-cancel">Zrušit</a>
            </div>
        </form>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> <a href="https://0ndra.maweb.eu" target="_blank">0ndra_M_</a></p>
    </footer>

    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        // Inicializace editoru
        var quill = new Quill('#editor', {
            theme: 'snow',
            placeholder: 'Napište svůj dopis zde...',
          
        });

        // Před odesláním formuláře přepíšeme obsah z Quillu do skrytého inputu
        var form = document.getElementById('letterForm');
        form.onsubmit = function() {
            var content = document.querySelector('input[name=letter_content]');
            content.value = quill.root.innerHTML;
            
            // Kontrola, zda není dopis prázdný
            if(quill.getText().trim().length === 0) {
                alert("Nelze publikovat prázdný dopis.");
                return false;
            }
        };
    </script>
</body>
</html>