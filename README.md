# Steam-like Store Project

Een PHP/MySQL webapplicatie voor een Steam-achtige game store. Gebruikers kunnen registreren, inloggen, games bekijken, games in hun winkelwagen plaatsen, een gesimuleerde checkout uitvoeren en hun aangekochte games terugvinden in hun library. Admins kunnen games beheren en spelersbibliotheken aanpassen.

## Functionaliteiten

- Registreren en inloggen met sessies
- Wachtwoorden worden veilig gehasht met `password_hash()`
- Uitloggen via sessie-verwijdering
- Dynamische store op basis van databasegegevens
- Bootstrap navigatiebalk met profiel-dropdown
- Winkelwagen als Bootstrap sidebar/offcanvas
- Gesimuleerde checkout:
  - order wordt aangemaakt
  - games worden toegevoegd aan de speler-library
  - winkelwagen wordt geleegd
- Library pagina met sidebar en geselecteerde game-details
- Admin dashboard met tabs
- Admin kan games toevoegen, wijzigen, tijdelijk verbergen, verwijderen en in sale zetten
- Admin kan games uit gebruikersbibliotheken verwijderen

## Gebruikte technieken

In dit project zijn de volgende programmeertalen, libraries en frameworks gebruikt:

- PHP: backendlogica, sessies, authenticatie, databasequeries en pagina-opbouw
- MySQL: opslag van gebruikers, games, winkelwagens, orders en libraries
- SQL: database-tabellen, relaties, foreign keys en JOIN-query's
- HTML: structuur van de pagina's
- CSS: styling van de store, library, adminpagina en gedeelde componenten
- Bootstrap 5: responsive layout, navbar, tabs, buttons, modals en offcanvas winkelwagen
- Bootstrap Icons: iconen in knoppen, navigatie en profielmenu
- PDO: veilige databaseverbinding en prepared statements in PHP


## Projectstructuur

```text
Back-End/
  AdminLogic.php
  Auth.php
  Checkout.php
  CheckoutLogic.php
  DB_access.php
  LibraryLogic.php
  StoreLogic.php
  import.sql

Front-End/
  Admin.php
  Checkout.php
  Library.php
  Login.php
  Logout.php
  Sign-up.php
  Store.php
  Partials/
    Navbar.php
  styling/
    Admin.css
    Global.css
    Library.css
    Sign-up.css
    Store.css
```

## Installatie

1. Plaats de projectmap in je lokale webserver, bijvoorbeeld in `htdocs` of de MAMP/XAMPP webroot.

2. Maak de database aan door [Back-End/import.sql](Back-End/import.sql) te importeren in MySQL/phpMyAdmin.

3. Controleer de databaseverbinding in [Back-End/DB_access.php](Back-End/DB_access.php):

```php
$servername = "127.0.0.1";
$username = "root";
$password = "password";
$port = 3306;
$dbname = "backend_eindproject";
```

Pas deze waarden aan als jouw MySQL-gebruiker, wachtwoord of poort anders is.

4. Open de applicatie via je lokale server, bijvoorbeeld:

```text
http://localhost/Steam-back-end-project/Front-End/Store.php
```

De exacte URL hangt af van waar je de projectmap hebt geplaatst.

## Testaccounts

Na het importeren van `import.sql` zijn deze accounts beschikbaar:

```text
Admin
Email: admin@example.com
Wachtwoord: admin123

Speler
Email: player@example.com
Wachtwoord: player123
```

Je kunt inloggen met e-mailadres of gebruikersnaam.

## Database

De database heet standaard:

```text
backend_eindproject
```

Belangrijke tabellen:

- `users`: gebruikers, wachtwoorden en adminrol
- `games`: games in de store
- `cart`: games in de winkelwagen van een gebruiker
- `player_libraries`: games die een gebruiker bezit
- `orders`: geplaatste orders
- `order_items`: games binnen een order

## Belangrijke pagina's

- `Front-End/Store.php`: store met gamecards en winkelwagen
- `Front-End/Library.php`: persoonlijke game library
- `Front-End/Login.php`: inloggen
- `Front-End/Sign-up.php`: account aanmaken
- `Front-End/Admin.php`: admin dashboard
- `Front-End/Checkout.php`: checkout-afhandeling
- `Front-End/Logout.php`: uitloggen

## Admin functies

Alleen gebruikers met `IsAdmin = TRUE` kunnen de adminpagina openen. In het admin dashboard kan een admin:

- Nieuwe games toevoegen
- Gamegegevens wijzigen
- Games tijdelijk uitschakelen in de store
- Games permanent verwijderen
- Een percentage-based sale instellen
- Sales verwijderen
- Games uit de library van spelers verwijderen

## Styling

De gedeelde kleuren, knoppen, panels, navbar en profiel-dropdown staan in:

```text
Front-End/styling/Global.css
```

Pagina-specifieke styling staat in de eigen CSS-bestanden, zoals `Store.css`, `Library.css` en `Admin.css`.

## Opmerking

Dit project gebruikt een gesimuleerde checkout. Er wordt dus geen echte betaling verwerkt. De checkout maakt alleen een order aan, voegt de games toe aan de library en leegt daarna de winkelwagen.
