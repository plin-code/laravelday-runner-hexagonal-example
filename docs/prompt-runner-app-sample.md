Lavora in questo progetto Laravel (frontend React + Inertia, Fortify già presenti).

## Branch

Crea il branch `runner-app-sample` partendo da `main` e lavora lì. Sei autorizzato a farlo.

## Ambiente

- Se il progetto usa Sail, tutti i comandi PHP, Composer, Node e DB girano dentro Sail (`./vendor/bin/sail ...`) e solo `git` gira sull'host. Se i container sono fermi avviali con `sail up -d`. Senza Sail usa i comandi normali (`php artisan`, `composer`, `npm`).
- Prima della build lancia `npm install` nello stesso ambiente. Se `package-lock.json` cambia solo nel campo `name`, ripristinalo.
- Non modificare `DB_*`, `REDIS_*`, `APP_URL` nel `.env`, né `compose.yaml`. Imposta `MAIL_MAILER=log` in `.env` e `.env.example`.
- Se un comando fallisce per un problema di container, fermati e segnalalo.

## Setup

1. Installa il pacchetto e phparkitect:

    ```bash
    composer require "plin-code/laravel-clean-architecture:^3.8.1"
    composer require --dev phparkitect/phparkitect
    ```

2. Attiva la skill Boost del pacchetto, `clean-architecture-development`. Boost non la aggiunge da solo senza interazione: aggiungi `plin-code/laravel-clean-architecture` a `packages` in `boost.json`, poi lancia `boost:update`. **Segui quella skill per tutto ciò che qui non è scritto: dove va ogni classe, transazioni, comandi, regole phparkitect.**
3. Lancia `php artisan clean-arch:install --migrate-app` e `composer dump-autoload`, poi attiva `validation.rules.no_classes_outside_layers` in `config/clean-architecture.php`.
4. La suite esistente deve essere verde prima di aggiungere funzionalità. Fai un commit qui.

## Struttura

Tutto il codice dell'applicazione vive in `app/Domain`, `app/Application`, `app/Infrastructure`. Fuori dai layer resta solo `app/Providers`.

- Sei autorizzato a spostare e cancellare codice esistente: le regole "non toccare codice non correlato" e "chiedi prima di cancellare" qui non valgono.
- Un modello creato con `make:class` dichiara `newFactory()` (come fa `User` dopo la migrazione), perché Laravel non indovina la factory da un modello dentro `App\Domain`.
- I modelli estendono `Model` di Eloquent, non `BaseModel` (che impone soft delete e `$guarded` a tutti). A fine lavoro cancella le classi generate che nessuno usa e le cartelle vuote.

## Dominio

- `User` con `is_admin` boolean (non mass assignable, `false` di default nella factory).
- `Runner`: nome, cognome, data di nascita, soft delete, 1:1 con `User` tramite `user_id` unico. L'email vive solo su `users`. Preferenze con `plin-code/laravel-custom-fields`.
- `Contest`: nome, data, `available_places` (capienza totale), `registrations_open`.
- Iscrizione: pivot `contest_runner` con `registered_at` e `checked_in_at` nullable, unica per contest e runner.

## Dati di esempio

- Factory per User, Runner, Contest.
- `database/data/contests.csv`: dieci contest finti, date future, almeno uno chiuso.
- Comando `contests:import {path?}` → `ImportContestsFromCsvAction`. Ripetibile (chiave nome e data), intestazione obbligatoria.
- `DatabaseSeeder`: admin `admin@example.com` / `password`, preferenze dei runner, contest dal CSV, cinque runner già iscritti. Senza `WithoutModelEvents` (lo slug dei custom fields nasce da un observer).

## Home pubblica

`/` sostituisce la `welcome`: elenco dei contest futuri per data (nome, data, posti rimasti). Quelli aperti e con posti hanno il link al form di iscrizione, gli altri l'etichetta del motivo. Controller → `ListUpcomingContestsAction`. Componenti dello starter kit.

## Iscrizione a un contest

- Pagina pubblica `/contests/{contest}/iscrizione`: nome, cognome, data di nascita, email, link alla home. POST con `throttle:10,1`.
- FormRequest → Controller → DTO `RegistrationData` → `RegisterToContestAction`.
- Pacchetti `plin-code` già installati (leggi i README in `vendor/` e usa le API reali):
    - `laravel-name-fixer` e `laravel-email-fixer` con locale `it`, applicati nel DTO, che compone anche il nome per `users.name`
    - `laravel-full-name` serve solo a cercare e ordinare: nella tabella Filament dei runner usa le macro di `Builder` `searchFullName()` e `orderByFullName()`
- FormRequest:
    - nome e cognome: solo lettere, spazi, apostrofi (anche `’`), trattini, più un punto o una virgola finale (finiscono in una mail Markdown)
    - email: `SanitizedEmail(locale: 'it')`, senza `rejectGarbage`
- Guardie, in ordine: contest non ancora svolto (il giorno della gara vale ancora), iscrizioni aperte, posti disponibili, runner non cancellato, maggiorenne (18 anni compiuti entro la data del contest, compresa), non già iscritto.
- Email già nota: riusa User e Runner senza sovrascriverli e valuta l'età sulla data di nascita salvata. Un runner con soft delete non viene ripristinato. Email nota senza Runner (per esempio l'admin): crea solo il Runner.
- User nuovo: password `Str::password(32)`.
- Mail di conferma dopo il commit: l'action dipende da un'interfaccia `RegistrationNotifier` in `app/Application/Contracts`, implementata in `app/Infrastructure/Mail` e legata in un service provider. `validation.application_infrastructure_allowed` resta vuota.
- Le guardie diventano errori di validazione in italiano: minorenne su `birth_date`, già iscritto e runner cancellato su `email`, contest già svolto, iscrizioni chiuse e posti esauriti sulla chiave `contest`, mostrata come avviso sopra il form.

## Pannello Filament

- La dipendenza Filament è già installata, non cambiare versione. Il pannello non esiste ancora: crealo con `make:filament-panel admin`, su `/admin`, accesso solo con `is_admin`.
- Etichette di risorse, campi e colonne in italiano; `APP_LOCALE` resta `en`.
- Resource Contest (lista, crea, modifica) e Runner (lista, modifica).
- Edit del Runner: un campo per ogni custom field attivo, con `getCustomFields()` e `setCustomFields()`. Il pacchetto salva i nomi in minuscolo: `Str::ucfirst` sulle etichette.

## API check-in

- Sanctum con `php artisan install:api`. Il comando lancia le migration: se fallisce perché le tabelle esistono già, fai prima `migrate:fresh`.
- `POST /api/contests/{contest}/runners/{runner}/check-in`, `auth:sanctum`, solo admin (Policy).
- `CheckInRunnerAction` con update atomico. Risposte: 200, 401, 403, 404 se il runner non è iscritto, 409 se il check-in è già stato fatto.

## Documentazione

- `docs/flow.md`: percorso file per file di home, iscrizione, check-in, import CSV, più la sezione "Decisioni".
- `README.md`: requisiti, installazione, seed, import, credenziali admin, URL, `curl` del check-in con token, comandi di test e phparkitect.

## Chiusura (tutto verde)

1. `clean-arch:make-arch-rules --force` e `phparkitect check`; `ls app` mostra solo i tre layer e `Providers`
2. `pint`, `phpstan analyse` (niente ignore né baseline)
3. Test Pest: home, iscrizione riuscita, un test per ogni guardia e per ogni caso di email già nota, nomi con Markdown o link, normalizzazione, import CSV, ogni risposta del check-in, accesso al pannello
4. `npm run build`, `npm run types:check`, `npm run check`
5. `migrate:fresh --seed`
6. Commit piccoli su `runner-app-sample`. Niente push.

## Regole di esecuzione

- Non fare domande e non chiudere con offerte. Davanti a un'ambiguità scegli l'opzione più coerente con la struttura, non la più semplice, e annotala in "Decisioni".
- Se un vincolo non è realizzabile, fermati e spiega perché invece di aggirarlo.
- Il riepilogo finale riporta l'output dei controlli e ciò che NON è stato fatto.
