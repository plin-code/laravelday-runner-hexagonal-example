# Flussi dell'applicazione

Il codice vive in tre layer (`app/Domain`, `app/Application`, `app/Infrastructure`). Fuori dai layer resta solo `app/Providers`. Ogni flusso qui sotto è elencato file per file, nell'ordine in cui la richiesta li attraversa.

## Home pubblica

`GET /`

1. `routes/web.php`: la rotta `home` punta a `HomeController`.
2. `app/Infrastructure/Http/Controllers/Web/HomeController.php`: chiama l'action e trasforma ogni contest in un array (`id`, `name`, `date`, `places_left`, `status`). Lo stato vale `open`, `closed` oppure `sold_out`.
3. `app/Application/Actions/Contests/ListUpcomingContestsAction.php`: legge i contest con data da oggi in poi, ordinati per data, con il conteggio dei runner iscritti (`withCount('runners')`, una sola query).
4. `app/Domain/Contests/Models/Contest.php`: lo scope `upcoming()` e il metodo `placesLeft()`.
5. `resources/js/pages/home.tsx`: elenco con i componenti dello starter kit (`Card`, `Badge`, `Button`). Un contest `open` mostra il link al form, gli altri l'etichetta "Iscrizioni chiuse" o "Posti esauriti".
6. `resources/js/lib/dates.ts`: formatta la data in italiano senza spostarla di fuso.

## Iscrizione a un contest

`GET /contests/{contest}/iscrizione` e `POST /contests/{contest}/iscrizione` (con `throttle:10,1`)

1. `routes/web.php`: rotte `contests.registration.create` e `contests.registration.store`.
2. `app/Infrastructure/Http/Controllers/Web/ContestRegistrationController.php`, metodo `create()`: rende `resources/js/pages/contests/registration.tsx` dentro il layout di autenticazione dello starter kit (`resources/js/app.tsx` lo assegna alle pagine `contests/`).
3. `app/Infrastructure/Http/Requests/Contests/StoreRegistrationRequest.php`: valida nome, cognome, data di nascita ed email. I messaggi sono in italiano.
4. `ContestRegistrationController::store()`: passa `$request->validated()` al DTO e il DTO all'action.
5. `app/Application/DTOs/RegistrationData.php`: applica `NameFixer` ed `EmailFixer` con locale `it` e compone il nome per `users.name`.
6. `app/Application/Actions/Contests/RegisterToContestAction.php`: apre una transazione, blocca la riga del contest con `lockForUpdate()`, esegue le guardie nell'ordine richiesto, crea `User` e `Runner` se servono, inserisce la riga in `contest_runner` e programma la notifica con `DB::afterCommit()`.
7. `app/Domain/Contests/Models/Contest.php`: le regole `hasBeenHeld()`, `placesLeft()` e `admitsRunnerBornOn()`.
8. `app/Domain/Contests/Exceptions/RegistrationRejectedException.php` con `app/Domain/Contests/Enums/RegistrationRejection.php`: una guardia fallita lancia l'eccezione con il motivo.
9. `ContestRegistrationController::rejectionMessage()`: traduce il motivo in un errore di validazione in italiano sulla chiave giusta (`contest`, `email` oppure `birth_date`). La pagina mostra la chiave `contest` come avviso sopra il form.
10. `app/Application/Contracts/RegistrationNotifier.php`: l'interfaccia da cui dipende l'action.
11. `app/Infrastructure/Mail/MailRegistrationNotifier.php`: l'implementazione, legata all'interfaccia in `app/Providers/AppServiceProvider.php`.
12. `app/Infrastructure/Mail/RegistrationConfirmedMail.php` e `resources/views/mail/registration-confirmed.blade.php`: la mail Markdown di conferma, messa in coda.

## API check-in

`POST /api/contests/{contest}/runners/{runner}/check-in`

1. `routes/api.php`: middleware `auth:sanctum`, poi `can('checkIn', 'contest')`.
2. `app/Domain/Contests/Policies/ContestPolicy.php`: `checkIn()` passa solo per gli admin. Laravel trova la policy da sola perché sta accanto al modello.
3. `app/Infrastructure/Http/Controllers/Api/CheckInController.php`: chiama l'action e traduce le eccezioni di dominio in risposte JSON.
4. `app/Application/Actions/Contests/CheckInRunnerAction.php`: un solo `UPDATE` condizionato su `checked_in_at IS NULL`. Se non aggiorna nessuna riga, distingue tra runner non iscritto e check-in già fatto.
5. `app/Domain/Contests/Exceptions/RunnerNotRegisteredException.php` e `RunnerAlreadyCheckedInException.php`.

| Risposta | Quando                                                              |
| -------- | ------------------------------------------------------------------- |
| 200      | check-in registrato                                                 |
| 401      | token assente o non valido                                          |
| 403      | utente autenticato ma non admin                                     |
| 404      | contest o runner inesistente, oppure runner non iscritto al contest |
| 409      | check-in già fatto                                                  |

## Import CSV

`php artisan contests:import {path?}`

1. `bootstrap/app.php`: registra il comando con `withCommands()`, perché Laravel scopre da solo soltanto `app/Console/Commands`.
2. `app/Infrastructure/Console/Commands/ImportContestsCommand.php`: risolve il percorso (default `database/data/contests.csv`), chiama l'action e stampa l'esito.
3. `app/Application/Actions/Contests/ImportContestsFromCsvAction.php`: pretende l'intestazione `name,date,available_places,registrations_open`, valida ogni riga e scrive tutto in una transazione con `updateOrCreate()` su nome e data.
4. `app/Application/Exceptions/InvalidContestsCsvException.php`: file illeggibile, intestazione mancante o riga non valida (con il numero di riga).
5. `database/data/contests.csv`: dieci contest di esempio.
6. `database/seeders/DatabaseSeeder.php`: riusa la stessa action per il seed.

## Pannello Filament

1. `app/Providers/Filament/AdminPanelProvider.php`: pannello `admin` su `/admin`, con le risorse scoperte in `app/Infrastructure/Filament/Resources`.
2. `app/Domain/Users/Models/User.php`: `canAccessPanel()` lascia entrare solo chi ha `is_admin`.
3. `app/Infrastructure/Filament/Resources/Contests`: lista, creazione e modifica dei contest.
4. `app/Infrastructure/Filament/Resources/Runners`: lista e modifica dei runner. La tabella cerca e ordina con le macro `searchFullName()` e `orderByFullName()`. La pagina di modifica legge i custom fields con `getCustomFields()` e li salva con `setCustomFields()`.

## Decisioni

Scelte fatte davanti a un'ambiguità della specifica.

**Struttura**

- `app/Domain/Shared/BaseModel.php` resta anche se nessun modello lo estende. Il file `phparkitect.php` generato da `clean-arch:make-arch-rules` lo importa come controllo che l'autoload funzioni, quindi cancellarlo rompe `phparkitect check` a ogni rigenerazione con `--force`. Le altre classi generate e inutilizzate (`BaseService`, `BaseRequest`, le tre eccezioni in `Infrastructure/Exceptions`) sono state cancellate.
- Le action estendono `BaseAction` e usano il suo `log()` per registrare iscrizioni, check-in e import.
- L'iscrizione non ha un modello proprio. È la pivot `contest_runner` della relazione `belongsToMany` tra `Contest` e `Runner`.
- Le eccezioni delle guardie sono una sola classe (`RegistrationRejectedException`) con un enum per il motivo. La traduzione in messaggio italiano sta nel controller, perché è un dettaglio di presentazione.
- `User` implementa `FilamentUser` nel layer di dominio. È un'interfaccia del vendor, non dell'infrastruttura dell'app, e le regole dei layer restano verdi.
- `InvalidContestsCsvException` sta in `app/Application/Exceptions` perché riguarda il formato del file, non una regola di dominio.
- `config/sanctum.php` ha un cast a stringa su `env('SANCTUM_STATEFUL_DOMAINS')`, necessario per passare PHPStan senza ignore né baseline.

**Iscrizione**

- I posti rimasti sono `available_places` meno i runner iscritti non cancellati. Un runner con soft delete libera il posto ma la sua riga in `contest_runner` resta.
- La riga del contest è bloccata per tutta la transazione, così due iscrizioni concorrenti non superano la capienza. Due iscrizioni concorrenti con la stessa email nuova su due contest diversi possono invece scontrarsi sull'indice unico di `users.email`: la seconda fallisce con un errore e va ripetuta.
- La maggiore età si calcola con `birth_date + 18 anni <= data del contest`. Chi è nato il 29 febbraio compie gli anni il primo marzo negli anni non bisestili.
- Un utente nuovo viene creato senza email verificata e non riceve la mail di verifica di Fortify. Per accedere deve usare il recupero password.
- Il campo email del form è `type="text"` con `inputMode="email"`. Con `type="email"` il browser bloccherebbe proprio gli indirizzi malformati che `SanitizedEmail` sa riparare.
- Senza `rejectGarbage` il pacchetto ripara anche input improbabili (per esempio `not an email` diventa `notan@email.it`). È la conseguenza della scelta richiesta.
- Il messaggio di errore di `SanitizedEmail` è in inglese perché è scritto nel pacchetto. Gli altri messaggi del form sono in italiano.
- Il controllo sui nomi usa una regex nel FormRequest invece di `ValidName` del pacchetto, perché deve accettare anche l'apostrofo tipografico e il punto o la virgola finali, che poi `NameFixer` toglie.
- La mail di conferma è in coda (`ShouldQueue`). Con `QUEUE_CONNECTION=database` serve un worker attivo per vederla nel log.
- Dopo l'iscrizione l'utente torna alla home con un toast di conferma.

**Check-in**

- La policy viene applicata dopo il binding dei modelli. Un utente non admin che chiede un contest inesistente riceve 404 e non 403.
- Un runner con soft delete non è risolto dal binding, quindi il check-in risponde 404.
- Le risposte usano gli helper `successResponse()` ed `errorResponse()` del controller base generato dal pacchetto.

**Pannello**

- Il Runner ha le azioni di cancellazione (soft delete) e ripristino nella pagina di modifica, più il filtro sui cancellati in lista. Senza di esse la guardia "runner non cancellato" non sarebbe raggiungibile dall'interfaccia. Non c'è cancellazione definitiva.
- Il Contest non ha azioni di cancellazione: la specifica chiede lista, creazione e modifica, e cancellare un contest eliminerebbe a cascata le iscrizioni.
- L'email del runner è mostrata in sola lettura, perché vive su `users`.
- Modificare nome o cognome di un runner dal pannello non aggiorna `users.name`.
- Un custom field booleano è un toggle, che ha sempre un valore: al salvataggio viene scritto `false` anche se prima non c'era risposta.
- I testi propri di Filament (pulsanti, paginazione) restano in inglese perché `APP_LOCALE` resta `en`. Sono in italiano le etichette di risorse, campi e colonne.

**Dati di esempio**

- Le preferenze dei runner sono quattro custom fields: taglia maglietta (select), newsletter (boolean), contatto di emergenza (phone), note per gli organizzatori (textarea).
- I cinque runner del seed sono iscritti al contest aperto con meno posti (cinque), così la home mostra subito un caso "Posti esauriti".
- Le date del CSV sono fisse nel 2027. Quando saranno passate il file andrà aggiornato.
