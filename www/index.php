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
$deleted = isset($_GET['deleted']) && $_GET['deleted'] == 1;
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
    <title>LETTER | Zanechte stopu</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>✉️</text></svg>">
    <link rel="manifest" href="manifest.webmanifest">
    <link rel="apple-touch-icon" href="icons/icon-192.png">
    <link rel="stylesheet" href="style.css">
   <style>
        /* Notifikace */
        .notification { padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; }
        .notification-success { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        
        /* Modal */
        .modal { display: <?php echo ($error ? 'flex' : 'none'); ?>; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); align-items: center; justify-content: center; backdrop-filter: blur(4px); }
        .modal-content { background: white; padding: 32px; border-radius: 12px; width: 85%; max-width: 320px; position: relative; color: #111; }
        .close { position: absolute; right: 15px; top: 10px; cursor: pointer; font-size: 24px; color: #9ca3af; }
        .modal-content input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #d1d5db; border-radius: 6px; font-family: inherit; box-sizing: border-box; }

        .letter-container { text-align: center; padding: 60px 40px; }
        .author-header { margin-bottom: 40px; }
        h1 { font-size: 1.8rem; margin-bottom: 20px; }
        .instruction { text-align: left; max-width: 400px; margin: 40px auto; color: #4b5563; line-height: 1.8; }
    </style>
</head>
<body>

    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="toggleModal(false)">&times;</span>
            <form method="POST">
                <h3 class="modal-title">Přihlášení</h3>
                <?php if ($error): ?><p class="text-danger fs-12"><?php echo $error; ?></p><?php endif; ?>
                <input type="text" name="username" placeholder="Uživatel" required>
                <input type="password" name="password" placeholder="Heslo" required>
                <button type="submit" name="login_action" class="btn btn-primary btn-full">Vstoupit</button>
                <p class="fs-11 text-center mt-15">
                    Nemáte účet? <a href="register.php" class="text-accent">Zaregistrujte se</a>
                </p>
            </form>
        </div>
    </div>

    <main class="letter-container">
        <div class="author-header">PROJEKT_LETTER</div>
        
        <?php if ($deleted): ?>
            <div class="notification notification-success">✓ Dopis byl úspěšně smazán.</div>
        <?php endif; ?>
        
        <?php if ($isLoggedIn): ?>
    <h1>Vítejte zpět, <?php echo htmlspecialchars($_SESSION['username'] ?? 'poutníku'); ?>.</h1>
    <p>Vaše slova čekají na zapsání nebo pokračování.</p>
    
    <div class="mt-30">
        <a href="write.php" class="btn btn-primary">✍️ Napsat nový dopis</a>
    </div>

    <div class="mt-50 text-left archive-wrap">
        <h3 class="archive-title">MOJE ARCHIVY</h3>
        
        <?php
        // Načteme dopisy přihlášeného uživatele včetně statistik zobrazení
        $stmt = $pdo->prepare("SELECT c.id, c.letter_text, c.created_at, COUNT(v.id) as total_views, COUNT(DISTINCT v.ip_address) as unique_views 
                               FROM content_table c 
                               LEFT JOIN view_logs v ON c.id = v.letter_id 
                               WHERE c.user_id = ? 
                               GROUP BY c.id 
                               ORDER BY c.created_at DESC");
        $stmt->execute([$_SESSION['user_id']]);
        $myLetters = $stmt->fetchAll();

        if ($myLetters): ?>
            <ul class="list-reset mt-15">
                <?php foreach ($myLetters as $myLetter): 
                    // Vytáhneme kousek textu jako náhled (odstraníme HTML značky)
                    $preview = strip_tags($myLetter['letter_text']);
                    $preview = mb_substr($preview, 0, 40) . (mb_strlen($preview) > 40 ? "..." : "");
                    if (empty($preview)) $preview = "<i>Dopis bez textu</i>";
                ?>
                    <li class="archive-item">
                        <div class="truncate">
                            <a href="view.php?id=<?php echo $myLetter['id']; ?>" class="no-decoration bold fs-14 text-main">
                                <?php echo $preview; ?>
                            </a>
                            <div class="fs-10 text-muted">
                                <?php echo date("d. m. Y H:i", strtotime($myLetter['created_at'])); ?>
                                                            </div>
                        </div>
                        <a href="view.php?id=<?php echo $myLetter['id']; ?>" class="fs-12 text-accent no-decoration">Otevřít →</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="fs-13 text-muted italic text-center mt-20">Zatím jste nenapsal žádný dopis.</p>
        <?php endif; ?>
    </div>

    <div class="mt-40">
        <a href="logout.php" class="text-muted fs-12 no-decoration">Odhlásit se</a>
    </div>

<?php else: ?>
            <h1>Pošlete dopis.</h1>
            <p>Aplikace pro posílání dlouhých zpráv.</p>
            
            <div class="instruction">
                1. <b class="text-dark">IDENTITA:</b> Vytvořte si anonymní profil.<br>
                2. <b class="text-dark">ZÁPIS:</b> Formátujte text v našem editoru.<br>
                3. <b class="text-dark">SDÍLENÍ:</b> Pošlete unikátní kód komukoliv.
            </div>

            <div class="mt-20">
                <button class="btn btn-primary" onclick="toggleModal(true)">Začít psát</button>
                <button id="installAppBtn" class="btn btn-outline" style="display: none; margin-top: 10px;">Nainstalovat aplikaci</button>
                <p class="fs-11 text-muted mt-15">Pro přístup k archivu a psaní je nutné se přihlásit.</p>
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
    <script src="pwa-register.js"></script>
</body>
</html>