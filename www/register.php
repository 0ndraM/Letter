<?php
require 'db.php';

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (strlen($username) < 3) {
        $error = "Uživatelské jméno musí mít alespoň 3 znaky.";
    } elseif (strlen($password) < 6) {
        $error = "Heslo musí mít alespoň 6 znaků.";
    } else {
        // Kontrola, zda uživatel již neexistuje
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->fetch()) {
            $error = "Toto uživatelské jméno je již obsazené.";
        } else {
            // Hashování hesla a uložení
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
            
            if ($stmt->execute([$username, $hash])) {
                $success = "Registrace proběhla úspěšně! Nyní se můžete přihlásit.";
            } else {
                $error = "Něco se nepovedlo. Zkuste to prosím znovu.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrace | Chronos</title>
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
            display: flex; align-items: center; justify-content: center; min-height: 100vh;
        }

        .register-container { 
            max-width: 400px; 
            width: 90%; 
            background: var(--paper-color); 
            padding: 50px 40px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.3); 
            border-radius: 4px;
            box-sizing: border-box;
            color: var(--text-color);
        }

        .author-header { 
            font-size: 0.75rem; 
            color: #9ca3af; 
            border-bottom: 1px solid rgba(0,0,0,0.05); 
            padding-bottom: 10px; 
            margin-bottom: 30px; 
            letter-spacing: 2px;
            text-align: center;
        }

        h2 { text-align: center; margin-bottom: 30px; font-size: 1.5rem; }

        input { 
            width: 100%; padding: 12px; margin: 10px 0; 
            border: 1px solid #d1d5db; border-radius: 6px; 
            font-family: inherit; box-sizing: border-box; 
            outline: none;
        }

        input:focus { border-color: var(--accent); box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1); }

        .btn { 
            width: 100%; padding: 12px; margin-top: 20px;
            background: var(--accent); color: white; border: none; 
            border-radius: 6px; cursor: pointer; font-family: inherit; 
            font-size: 14px; font-weight: 500; transition: 0.2s;
        }

        .btn:hover { background: #4338ca; }

        .msg { font-size: 12px; text-align: center; margin-bottom: 15px; }
        .error { color: #ef4444; }
        .success { color: #10b981; }

        .footer-link { text-align: center; margin-top: 25px; font-size: 11px; }
        .footer-link a { color: var(--accent); text-decoration: none; }
    </style>
</head>
<body>

    <div class="register-container">
        <div class="author-header">NOVÁ_IDENTITA</div>
        
        <h2>Vytvořit účet</h2>

        <?php if ($error): ?>
            <div class="msg error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="msg success">
                <?php echo $success; ?><br>
                <a href="index.php" style="color: inherit; font-weight: bold;">Přejít k přihlášení</a>
            </div>
        <?php else: ?>
            <form method="POST">
                <input type="text" name="username" placeholder="Uživatelské jméno" required>
                <input type="password" name="password" placeholder="Heslo (min. 6 znaků)" required>
                <button type="submit" class="btn">Zaregistrovat se</button>
            </form>
        <?php endif; ?>

        <div class="footer-link">
            Už máte účet? <a href="index.php">Přihlaste se zde</a>
        </div>
    </div>
</body>
</html>