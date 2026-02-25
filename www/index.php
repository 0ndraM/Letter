<?php
require 'db.php';

$error = "";
// Logika přihlášení
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_action'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        session_write_close();
        header("Location: index.php"); 
        exit;
    } else { $error = "Chybné jméno nebo heslo."; }
}

$isLoggedIn = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chronos | Zanechte stopu</title>
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

        /* Tlačítka */
        .btn { padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-family: inherit; font-size: 14px; font-weight: 500; transition: 0.2s; text-decoration: none; display: inline-block; }
        .btn-primary { background: var(--accent); color: white; }
        .btn-primary:hover { background: #4338ca; transform: translateY(-1px); }
        .btn-outline { background: rgba(0,0,0,0.05); color: #6b7280; border: 1px solid #d1d5db; }
        
        /* Modal */
        .modal { display: <?php echo ($error ? 'flex' : 'none'); ?>; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); align-items: center; justify-content: center; backdrop-filter: blur(4px); }
        .modal-content { background: white; padding: 32px; border-radius: 12px; width: 85%; max-width: 320px; position: relative; color: #111; }
        .close { position: absolute; right: 15px; top: 10px; cursor: pointer; font-size: 24px; color: #9ca3af; }
        .modal-content input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #d1d5db; border-radius: 6px; font-family: inherit; box-sizing: border-box; }

        /* Kontejner papíru */
        .letter-container { 
            max-width: 800px; 
            width: calc(100% - 16px); 
            margin: 8px auto 20px auto; 
            background: var(--paper-color); 
            padding: 60px 40px; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.15); 
            color: var(--text-color); 
            text-align: center;
            box-sizing: border-box;
            border-radius: 4px;
        }

        .author-header { font-size: 0.75rem; color: #9ca3af; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 10px; margin-bottom: 40px; letter-spacing: 2px; }
        h1 { font-size: 1.8rem; margin-bottom: 20px; }
        .instruction { text-align: left; max-width: 400px; margin: 40px auto; color: #4b5563; line-height: 1.8; }

        footer { margin-top: auto; padding: 30px 20px; text-align: center; color: #9ca3af; font-size: 11px; }
        footer a { color: #6b7280; text-decoration: none; }
    </style>
</head>
<body>

    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="toggleModal(false)">&times;</span>
            <form method="POST">
                <h3 style="margin-top:0;">Přihlášení</h3>
                <?php if ($error): ?><p style="color:#ef4444; font-size:12px;"><?php echo $error; ?></p><?php endif; ?>
                <input type="text" name="username" placeholder="Uživatel" required>
                <input type="password" name="password" placeholder="Heslo" required>
                <button type="submit" name="login_action" class="btn btn-primary" style="width:100%;">Vstoupit</button>
                <p style="font-size: 11px; text-align: center; margin-top: 15px;">
                    Nemáte účet? <a href="register.php" style="color: var(--accent)">Zaregistrujte se</a>
                </p>
            </form>
        </div>
    </div>

    <main class="letter-container">
        <div class="author-header">PROJEKT_CHRONOS</div>
        
        <?php if ($isLoggedIn): ?>
    <h1>Vítejte zpět, <?php echo htmlspecialchars($_SESSION['username'] ?? 'poutníku'); ?>.</h1>
    <p>Vaše slova čekají na zapsání nebo pokračování.</p>
    
    <div style="margin-top: 30px;">
        <a href="write.php" class="btn btn-primary">✍️ Napsat nový dopis</a>
    </div>

    <div style="margin-top: 50px; text-align: left; max-width: 500px; margin-left: auto; margin-right: auto;">
        <h3 style="font-size: 0.9rem; color: #9ca3af; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 10px; letter-spacing: 1px;">MOJE ARCHIVY</h3>
        
        <?php
        // Načteme dopisy přihlášeného uživatele
        $stmt = $pdo->prepare("SELECT id, letter_text, created_at FROM content_table WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$_SESSION['user_id']]);
        $myLetters = $stmt->fetchAll();

        if ($myLetters): ?>
            <ul style="list-style: none; padding: 0; margin-top: 15px;">
                <?php foreach ($myLetters as $myLetter): 
                    // Vytáhneme kousek textu jako náhled (odstraníme HTML značky)
                    $preview = strip_tags($myLetter['letter_text']);
                    $preview = mb_substr($preview, 0, 40) . (mb_strlen($preview) > 40 ? "..." : "");
                    if (empty($preview)) $preview = "<i>Dopis bez textu</i>";
                ?>
                    <li style="margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.02); padding: 10px; border-radius: 6px;">
                        <div style="overflow: hidden; white-space: nowrap; text-overflow: ellipsis; margin-right: 10px;">
                            <a href="view.php?id=<?php echo $myLetter['id']; ?>" style="color: var(--text-color); text-decoration: none; font-weight: bold; font-size: 14px;">
                                <?php echo $preview; ?>
                            </a>
                            <div style="font-size: 10px; color: #9ca3af;">
                                <?php echo date("d. m. Y H:i", strtotime($myLetter['created_at'])); ?>
                            </div>
                        </div>
                        <a href="view.php?id=<?php echo $myLetter['id']; ?>" style="font-size: 12px; color: var(--accent); text-decoration: none;">Otevřít →</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p style="font-size: 13px; color: #9ca3af; font-style: italic; text-align: center; margin-top: 20px;">Zatím jste nenapsal žádný dopis.</p>
        <?php endif; ?>
    </div>

    <div style="margin-top: 40px;">
        <a href="logout.php" style="color: #9ca3af; font-size: 12px; text-decoration: none;">Odhlásit se</a>
    </div>

<?php else: ?>
            <h1>Pošlete dopis.</h1>
            <p>Aplikace pro posílání dlouhých zpráv.</p>
            
            <div class="instruction">
                1. <b style="color: #111;">IDENTITA:</b> Vytvořte si anonymní profil.<br>
                2. <b style="color: #111;">ZÁPIS:</b> Formátujte text v našem editoru.<br>
                3. <b style="color: #111;">SDÍLENÍ:</b> Pošlete unikátní kód komukoliv.
            </div>

            <div style="margin-top: 20px;">
                <button class="btn btn-primary" onclick="toggleModal(true)">Začít psát</button>
                <p style="font-size: 11px; color: #9ca3af; margin-top: 15px;">Pro přístup k archivu a psaní je nutné se přihlásit.</p>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> <a href="https://0ndra.maweb.eu" target="_blank">0ndra_M_</a></p>
    </footer>

    <script>
        function toggleModal(show) {
            document.getElementById('loginModal').style.display = show ? 'flex' : 'none';
        }
        window.onclick = function(event) {
            let modal = document.getElementById('loginModal');
            if (event.target == modal) modal.style.display = "none";
        }
    </script>
</body>
</html>