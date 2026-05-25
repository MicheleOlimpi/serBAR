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

## NOVITA' RECENTI
- **Identità applicativa aggiornata**:
  - titolo pagina aggiornato da `ACLI servizio BAR` a `serBAR`.
- **Utenti e credenziali più robusti**:
  - aggiunto il campo email nella gestione utenti;
  - bloccato il salvataggio con username troppo lunghi;
  - bloccato il salvataggio di password vuote;
  - migliorata la chiarezza del placeholder password per l'utente `supervisor` in fase di setup.
- **Comunicazioni e segnalazioni migliorate**:
  - aggiunto il badge colore dello stato direttamente nel titolo della modale segnalazione;
  - introdotte modali di dettaglio più complete per notifiche e comunicazioni;
  - rinominata la colonna riepilogo testo in segnalazioni da `Testo (prime 20)` a `Testo`.
- **Setup e test email più comodi**:
  - aggiunta l'icona di esito per il test SMTP;
  - corretta l'apertura della modale test connessione mail dopo il caricamento Bootstrap;
  - corretto un refuso nell'indirizzo server SMTP nella documentazione.
- **Personalizzazione UI estesa**:
  - migliorata la tematizzazione dell'icona hamburger su mobile (incluso colore personalizzabile);
  - aggiunta intestazione mese calendario personalizzabile;
  - aggiornati stili di titolo e sottotitolo header nel tema.
- **Documentazione aggiornata**:
  - sezione installazione/avvio rapido e descrizione interfacce riallineate alle ultime modifiche.

## INSTALLAZIONE
1. Copiare i files nel repository nella directory di installazione (es. /var/www/serBAR)
2. Installare dipendenze: con composer install
3. Aprire la pagina del programma (Es. http://localhost/serBAR)
4. Se il DB non è configurato/disponibile, comparirà l'interfaccia di installazione.
5. La configurazione DB viene salvata in `config/app.php`.

## FUNZIONI PRINCIPALI
- Installazione guidata DB e creazione schema
- Gestione multiutente con ruoli (`admin`, `supervisor`, `user`)
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
  - E' disponibile un sistema di messaggiustica interna, con stato del messaggio variabile dagli admin/supervisor, consultabile dagli users
