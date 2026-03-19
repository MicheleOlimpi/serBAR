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
- una amministrativa dedicata ai gestori
- una di consultazione/comunicazione dedicata ai volontari
- una al pubblico, disabilitabile, per la visualizzazione dei turni al pubblico.

## STACK
- PHP 8+
- MariaDB/MySQL
- Bootstrap 5
- FontAwesome

## Tema
Il tema dell'applicazione è centralizzato in `public/css/theme.css` e usa le variabili CSS di Bootstrap 5 (`--bs-*`) per personalizzare colori, radius e aspetto generale senza modificare le viste.
Il file viene caricato con riferimento relativo così l'app continua a funzionare anche quando è pubblicata in una sottocartella del dominio.

## Novità recenti
- **Tabellone generato più completo e leggibile**:
  - aggiunta la colonna **Chiusura** nella vista generata/stampabile del tabellone per evidenziare il responsabile di chiusura;
  - i turni del tabellone generato sono ora centrati verticalmente per migliorare l'impaginazione;
  - inserita una riga di separazione sotto l'intestazione del mese per rendere più chiaro il layout.
- **Tema del tabellone generato più personalizzabile**:
  - spostati nel file tema centralizzato gli stili della vista generata, così header, celle, spaziature e dimensioni possono essere configurati via variabili CSS;
  - introdotte variabili dedicate per titolo/sottotitolo del tabellone e larghezze delle colonne principali.
- **Navigazione supervisor ottimizzata**:
  - il ruolo `supervisor` non vede più la voce di menu **Setup**, mantenendo la barra di navigazione coerente con i permessi disponibili.
- **Branding README aggiornato**:
  - inserito il logo ufficiale **square** del progetto nell'intestazione;
  - dimensionamento impostato a **150x150 px** per mantenere una resa coerente su GitHub.
- **Installazione guidata migliorata**:
  - aggiunta una schermata di avanzamento durante il setup iniziale;
  - introdotto un riepilogo finale più chiaro al termine dell'installazione;
  - interfaccia di installazione semplificata (navbar nascosta e contenuti centrati).
- **Nuove impostazioni per interfaccia di consultazione**:
  - aggiunti parametri dedicati nella configurazione applicativa per controllare la parte pubblica/consultazione.
- **Gestione giorni di chiusura settimanali (area admin)**:
  - introdotta una sezione dedicata per impostare i giorni di chiusura ricorrenti;
  - corretta la visualizzazione dei nomi dei giorni nella pagina di modifica.
- **Dashboard consultazione più leggibile**:
  - badge giorno colorato in base al tipo giorno per migliorare la leggibilità immediata.
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
- Vista stampabile/esportabile PDF via stampa browser
- Interfaccia per consultazione e segnalazioni dedicata all'utenza standard, eventualmente disabilitabile
- Interfaccia al pubblico accessibile solo tramite token, eventualmente disabilitabile.

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
