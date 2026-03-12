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

## Tema
Il tema dell'applicazione è centralizzato in `public/css/theme.css` e usa le variabili CSS di Bootstrap 5 (`--bs-*`) per personalizzare colori, radius e aspetto generale senza modificare le viste.
Il file viene caricato con riferimento relativo così l'app continua a funzionare anche quando è pubblicata in una sottocartella del dominio.

## Novità recenti
- **Interfaccia admin tabelloni aggiornata**:
  - aggiunta una vista tabellone generata a schermo intero per una consultazione più immediata;
  - migliorata la navigazione nell'elenco tabelloni con accesso rapido alla nuova vista.
- **Modale eliminazione utenti (area admin) migliorata**:
  - conferma di eliminazione centrata e resa più chiara nella gestione utenti.
- **Nuovo ruolo `supervisor`**:
  - aggiunto nella gestione utenti in area admin;
  - abilitato l'accesso alle funzionalità amministrative come alternativa al ruolo `admin`.
- **Modali più personalizzabili**:
  - introdotte variabili CSS dedicate (`--modal-icon-color`, `--modal-icon-size`) per icone delle finestre di conferma;
  - uniformata la resa visiva delle modali nelle sezioni principali di amministrazione.
- **Gestione segnalazioni potenziata**:
  - aggiunta una pagina dedicata alla **segnalazione libera** in area consultazione;
  - migliorata la UI di gestione segnalazioni lato admin;
  - introdotta la conferma tramite **modale di sicurezza** prima dell'eliminazione.
- **Conferme di eliminazione più sicure (area admin)**:
  - modali di conferma per eliminazione utenti e tabelloni;
  - flusso di cancellazione dei tipi giorno corretto e reso più chiaro.
- **Tipi giorno migliorati**:
  - gestione colori ottimizzata in amministrazione;
  - aggiornamento della creazione schema/installazione per supportare le nuove impostazioni.
- **Aggiornamento risorse grafiche**:
  - file logo aggiornati nell'interfaccia applicativa.
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

## CREDENZIALI POST INSTALLAZIONE
- username: admin password:admin per l'account amministrativo per configurazione, gestione turni e consultazione
- username: user password:user per l'account utente standard solo per consultazione
- username: supervisor password:supervisor per l'account di gestione turni

## FUNZIONI PRINCIPALI
- Installazione guidata DB, creazione schema e pagina di riepilogo finale
- Login/logout con ruoli (`admin`, `supervisor`, `user`)
- Dashboard amministratore
- CRUD utenti (password hash), tipo giorno, numero turni, calendario annuale
- Creazione/modifica/eliminazione tabelloni mensili
- Gestione turni giornalieri e annotazioni
- Editing tabellone con supporto a selezione rapida utenti e responsabile chiusura
- Interfaccia consultazione e segnalazioni
- Vista stampabile/esportabile PDF via stampa browser

## GESTIONE DELLE FESTIVITA'
Vengono gestite le festività nazionali, cattoliche e eventuali giorni speciali sia fisse che mobili.
### FISSE
   Definite da calendario su tabella interna del programma
### MOBILI
- Gestione dei giorni prefestivi
- Gestione dei giorni Festivi
- Gestione delle festività cattoliche e dei giorni speciali mobili:
   - Martedì Grasso
   - Mercoledì delle ceneri
   - Domenica delle Palme
   - Pasqua
   - Lunedì dell'angelo
