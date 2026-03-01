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

## Tema (v0.1.1)
Il tema dell'applicazione è centralizzato in `public/css/theme.css` e usa le variabili CSS di Bootstrap 5 (`--bs-*`) per personalizzare colori, radius e aspetto generale senza modificare le viste.
Il file viene caricato con riferimento relativo così l'app continua a funzionare anche quando è pubblicata in una sottocartella del dominio.

## Novità recenti
- **Editor tabellone mensile (area admin) migliorato**:
  - badge giorno più leggibile (numero giorno più grande, metadati compatti);
  - disposizione dei campi turno ottimizzata su desktop/mobile;
  - ordinamento turni per priorità e orario.
- **Ricerca e inserimento rapido volontari/responsabile**:
  - in modifica tabellone è disponibile un selettore con elenco utenti attivi;
  - i nominativi dei volontari possono essere aggiunti in forma abbreviata (es. `M. Rossi`);
  - il responsabile chiusura è selezionabile rapidamente solo per i turni che chiudono il bar.
- **Campo “Santo” allineato correttamente**:
  - nella vista tabellone il santo viene recuperato in base al giorno/mese,
    mantenendo la coerenza anche tra anni diversi.

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
- Editing tabellone con supporto a selezione rapida utenti e responsabile chiusura
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
