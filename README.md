# ACLI servizio BAR

Applicazione PHP 8 (PSR-4) con MariaDB per la gestione dei turni bar.

## Stack
- PHP 8+
- MariaDB/MySQL
- Bootstrap 5
- FontAwesome

## Avvio rapido
1. Installare dipendenze:
   ```bash
   composer install
   ```
2. Avviare server locale:
   ```bash
   php -S 0.0.0.0:8080 -t public
   ```
3. Aprire `http://localhost:8080`.
4. Se il DB non è configurato/disponibile, comparirà l'interfaccia di installazione.
5. La configurazione DB viene salvata in `config/app.php`.

## Credenziali seed
- `admin` / `admin`
- `user` / `user` (ruolo user)

## Funzioni principali
- Installazione guidata DB, creazione schema e pagina di riepilogo finale
- Login/logout con ruoli
- Dashboard amministratore
- CRUD utenti (password hash), tipo giorno, numero turni, calendario annuale
- Creazione/modifica/eliminazione tabelloni mensili
- Gestione turni giornalieri e annotazioni
- Interfaccia consultazione e segnalazioni
- Vista stampabile/esportabile PDF via stampa browser
