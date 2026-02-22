![Static Badge](https://img.shields.io/badge/version-alpha-red?logo=alert)
![GitHub License](https://img.shields.io/github/license/MicheleOlimpi/serBAR)
![GitHub last commit](https://img.shields.io/github/last-commit/MicheleOlimpi/serBAR)


# ACLI servizio BAR

Applicazione PHP 8 (PSR-4) con MariaDB per la gestione dei turni di servizio BAR in un circolo di volontari.
Viene gestita anche la comunicazione di eventuali messaggi sullo stile di un software di ticketing.<br>
Il sistema dispone di due interfacce web, una amministrativa dedicata ai gestori e una di consultazione/comunicazione dedicata ai volontari.

## STACK
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

## FUNZIONI PRINCIPALI
- Installazione guidata DB, creazione schema e pagina di riepilogo finale
- Login/logout con ruoli
- Dashboard amministratore
- CRUD utenti (password hash), tipo giorno, numero turni, calendario annuale
- Creazione/modifica/eliminazione tabelloni mensili
- Gestione turni giornalieri e annotazioni
- Interfaccia consultazione e segnalazioni
- Vista stampabile/esportabile PDF via stampa browser

## GESTIONE DELLE FESTIVITA'
Vengono gestite sia le festività nazionali che cattoliche
### FISSE
   Definite da calendario su tabella intera del programma
### MOBILI
- Gestione dei giorni prefestivi
- Gestione dei giorni Festivi
- Gestione delle festività cattoliche e dei giorni speciali mobili:
   - Martedì Grasso
   - Mercoledì delle ceneri
   - Domenica delle Palme
   - Pasqua
   - Lunedì dell'angelo

