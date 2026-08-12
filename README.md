![Static Badge](https://img.shields.io/badge/version-alpha-red?logo=alert)
![GitHub License](https://img.shields.io/github/license/MicheleOlimpi/serBAR)
![GitHub last commit](https://img.shields.io/github/last-commit/MicheleOlimpi/serBAR)

<p align="center">
  <img src="public/serBAR-square.svg" alt="Logo serBAR" width="150" height="150">
</p>

# serBAR

Applicazione PHP 8 (PSR-4) con MariaDB per la gestione dei turni di servizio BAR in un circolo di volontari.
Viene gestita anche la comunicazione di eventuali messaggi sullo stile di un software di ticketing.<br>
Il sistema dispone di tre interfacce web:
- Amministrativa, dedicata ai gestori
- Di consultazione/comunicazione, disabilitabile, dedicata ai volontari
- Al pubblico, disabilitabile, per la visualizzazione dei turni al pubblico.

## STACK
- PHP 8+
- phpmailer
- MariaDB/MySQL
- Bootstrap 5
- FontAwesome

## TEMA
Il tema dell'applicazione è centralizzato in `public/css/theme.css` e usa le variabili CSS di Bootstrap 5 (`--bs-*`) per personalizzare colori, radius e aspetto generale senza modificare le viste.

## NOVITA' RECENTI (20260812)
- **EDITING TABELLONE**
  - Se viene cambiata la tipologia di giorno vengono aggiornati anche gli orari per quel tipo di giorno, viene salvato il cartellone e riportato il focus sul giorno modificato
  - Se presente il valore alias nei dati del volontario selezionato per il turno viene utilizzato, altrimenti viene generato un testo basato su nome-cognome
- **DASHBOARD Sia nella sezione pagina admin che consultation**
  - Migliorato aspetto lista comunicazioni
- **PAGINA CALENDARIO**
  - Migliorato testo esplicativo
- **PAGINA COMUNICAZIONI in admin**
  - Migliorato la leggibilità delle comunicazioni
  - "creato il" diventa "Data"
- **PAGINA CONFIGURAZIONE TURNI**
  - Migliorato leggibilità 
- **AGGIUNTI NEL FILE seed_app_settings.sql:**
  - print_forcedPageBreak con valore numerico predefinito 0 (per futuro uso)
  - print_tableTitle con valore testo predefinito "SERVIZIO BAR" (per futuro uso)
  - print_tableMoonPhases con valore bool predefinito false (per futuro uso)
  - program_whatsNewSplash con valore bool predefinito false (per futuro uso)
- **PAGINA GIORNI CHIUSURA**
  - Aggiunto testo esplicativo
  - Invertito colonna "chiuso" con colonna "descrizione"
  - Se il campo "chiuso" non è flaggato, il campo descrizione non è editabile
  - Se viene tolto il flag del campo chiuso elimina anche il contenuto del campo descrizione
  - A destra del tasto "Salva" aggiunto un tasto "Annulla", che non salva i valori editati e ricarica la pagina
- **PAGINA INFORMAZIONI Sia nella sezione pagina admin che consultation**
  - Modificato testo licenza
  - Se si preme il bottone licenza, mostra la licenza del programma in una finestra modale bootstrap
- **STAMPA CARTELLONE**
  - Celle dei giorni con riga più scura
- **SETUP**
  - cambiato colori bottonni per renderli coerenti con quelli del progamma

## INSTALLAZIONE
1. Copiare i files nel repository nella directory di installazione (es. /var/www/serBAR)
2. Installare dipendenze: con composer install
3. Aprire la pagina del programma (Es. http://localhost/serBAR)
4. Se il DB non è configurato/disponibile, comparirà l'interfaccia di installazione.
5. La configurazione DB viene salvata in `config/app.php`.

## FUNZIONI PRINCIPALI
- Installazione guidata DB e creazione schema
- Gestione multiutente con ruoli (`admin`, `supervisor`, `user`, `operator`)
- Dashboard riassuntiva per tutti i tipi di utente
- Interfaccia di amministrazione per:
  - Gestione tabelloni con editing con supporto a selezione rapida utenti, anotazioni e responsabile chiusura
  - Stampa tabelloni stampabile/esportabile PDF via stampa browser
  - Gestione utenti
  - tipi di giorno
  - tipi di turni
  - Calendario annuale
- Interfaccia per consultazione turni, recapiti volontari e segnalazioni dedicata all'utenza standard, eventualmente disabilitabile anche parzialmente
- Interfaccia al pubblico accessibile solo tramite token, eventualmente disabilitabile.

## CREDENZIALI POST INSTALLAZIONE
- username: admin password:admin per l'account amministrativo per configurazione, gestione turni e consultazione
- username: user password:user per l'account utente standard solo per consultazione
- username: supervisor password:supervisor per l'account di gestione turni
- Il ruolo `operator` identifica operatori censiti in anagrafica: la password viene preimpostata a `NoPass@serBAR` alla creazione, ma il login alle interfacce di consultazione e amministrazione resta bloccato.

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
     
## MESSAGGISTICA INTERNA
  - E' disponibile un sistema di messaggistica interna, con stato del messaggio editabile dagli admin/supervisor, consultabile dagli users.
