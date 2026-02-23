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
    <title>Nový dopis | Chronos</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>✉️</text></svg>">
    
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    
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
            min-height: 600px;
            color: var(--text-color); 
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

        .btn { padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-family: inherit; font-size: 14px; font-weight: 500; transition: 0.2s; text-decoration: none; display: inline-block; }
        .btn-save { background: #10b981; color: white; flex: 1; }
        .btn-save:hover { background: #059669; }
        .btn-cancel { background: rgba(0,0,0,0.05); color: #6b7280; border: 1px solid #d1d5db; }

        .actions { display: flex; gap: 10px; margin-top: 30px; }

        footer { margin-top: auto; padding: 30px 20px; text-align: center; color: #9ca3af; font-size: 11px; }
    </style>
</head>
<body>

    <main class="letter-container">
        <div class="author-header">NOVÝ ZÁZNAM | AUTOR: <?php echo htmlspecialchars(strtoupper($username)); ?></div>

        <form action="save.php" method="POST" id="letterForm">
            <input type="hidden" name="letter_content" id="letter_content">
            
            <div id="editor">
                <p>Zde začíná váš příběh...</p>
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
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline'],
                    [{ 'align': [] }],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['clean']
                ]
            }
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