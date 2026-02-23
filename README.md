# ✉️ Chronos – Zanechte stopu

Webová aplikace pro psaní a sdílení dopisů. Zaregistrujte se, napište dopis s formátovaným textem a sdílejte ho přes unikátní odkaz s kýmkoliv.

---

## Funkce

- **Registrace a přihlášení** – vytvoření anonymního účtu chráněného heslem
- **Rich-text editor** – formátování textu (tučné, kurzíva, podtržení, zarovnání, seznamy) pomocí [Quill.js](https://quilljs.com/)
- **Publikování dopisu** – každý uložený dopis získá unikátní náhodný token (např. `4f2a9b1c5e`)
- **Sdílení** – jednoduchý odkaz ve tvaru `view.php?id=<token>` lze poslat komukoli
- **Archiv** – přehled všech vlastních dopisů s náhledem a datumem vytvoření
- **Úpravy** – autor dopisu ho může kdykoliv upravit

---

## Technologie

| Vrstva | Technologie |
|--------|------------|
| Backend | PHP 8.2 + Apache |
| Databáze | MySQL 8.0 |
| Frontend editor | Quill.js 1.3.6 |
| Správa DB | phpMyAdmin |
| Kontejnerizace | Docker / docker-compose |

---

## Instalace (Docker)

### Požadavky

- [Docker](https://docs.docker.com/get-docker/) a [Docker Compose](https://docs.docker.com/compose/install/)

### Kroky

1. **Naklonujte repozitář:**
   ```bash
   git clone https://github.com/0ndraM/Letter.git
   cd Letter
   ```

2. **Spusťte kontejnery:**
   ```bash
   docker-compose up -d
   ```

3. **Importujte databázové schéma:**

   Otevřete [phpMyAdmin](http://localhost:8081), přihlaste se (uživatel: `user`, heslo: `user_password`, databáze: `wwww`) a importujte soubor `db.sql`.

   Nebo přes příkazový řádek:
   ```bash
   docker exec -i projekt_db mysql -u user -puser_password wwww < db.sql
   ```

4. **Otevřete aplikaci:**

   - Aplikace: [http://localhost:8080](http://localhost:8080)
   - phpMyAdmin: [http://localhost:8081](http://localhost:8081)

---

## Struktura projektu

```
Letter/
├── docker-compose.yml   # Konfigurace Docker služeb
├── db.sql               # SQL schéma databáze
└── www/                 # Zdrojové soubory aplikace
    ├── db.php           # Připojení k databázi (PDO)
    ├── index.php        # Hlavní stránka + přihlášení
    ├── register.php     # Registrace nového uživatele
    ├── write.php        # Editor pro nový dopis
    ├── save.php         # Uložení dopisu do DB
    ├── view.php         # Zobrazení dopisu
    ├── edit.php         # Úprava existujícího dopisu
    └── logout.php       # Odhlášení
```

---

## Databázové schéma

```sql
-- Uživatelé
CREATE TABLE users (
  id            INT(11) AUTO_INCREMENT PRIMARY KEY,
  username      VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role          VARCHAR(10) DEFAULT 'user'
);

-- Dopisy
CREATE TABLE content_table (
  id          VARCHAR(12) NOT NULL PRIMARY KEY,  -- náhodný token
  user_id     INT(11) NOT NULL,
  letter_text LONGTEXT NOT NULL,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

---

## Autor

Vytvořil [0ndra\_M\_](https://0ndra.maweb.eu)
