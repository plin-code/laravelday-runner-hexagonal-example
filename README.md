# Running Club

Applicazione di esempio per gestire le iscrizioni alle gare di un running club. Laravel 13 con React e Inertia per le pagine pubbliche, Filament per il pannello di amministrazione, Sanctum per l'API di check-in. Il codice segue una struttura a tre layer (`app/Domain`, `app/Application`, `app/Infrastructure`) verificata con phparkitect.

Il percorso file per file di ogni flusso e le decisioni prese sono in [docs/flow.md](docs/flow.md).

## Requisiti

- Docker con Docker Compose (il progetto gira dentro Laravel Sail)
- PHP 8.4 o superiore e Composer sull'host, solo per la prima `composer install`
- Node 24 (dentro il container di Sail)

## Installazione

```bash
composer install
cp .env.example .env
vendor/bin/sail up -d
vendor/bin/sail artisan key:generate
vendor/bin/sail npm install
vendor/bin/sail npm run build
```

Tutti i comandi PHP, Composer e Node girano dentro Sail. Solo `git` gira sull'host.

## Seed

```bash
vendor/bin/sail artisan migrate:fresh --seed
```

Il seed crea l'utente admin, le preferenze dei runner (custom fields), i dieci contest di `database/data/contests.csv` e cinque runner già iscritti a un contest.

## Import dei contest

```bash
vendor/bin/sail artisan contests:import
vendor/bin/sail artisan contests:import percorso/del/file.csv
```

Senza argomento il comando legge `database/data/contests.csv`. La prima riga deve essere l'intestazione `name,date,available_places,registrations_open`. L'import si può ripetere: un contest è identificato da nome e data, quindi una seconda esecuzione aggiorna le righe invece di duplicarle. Una riga non valida annulla tutto l'import.

## Credenziali admin

| Email               | Password   |
| ------------------- | ---------- |
| `admin@example.com` | `password` |

## URL

| Pagina                             | URL                                       |
| ---------------------------------- | ----------------------------------------- |
| Home pubblica con i contest futuri | http://localhost/                         |
| Form di iscrizione                 | http://localhost/contests/{id}/iscrizione |
| Pannello Filament                  | http://localhost/admin                    |

La mail di conferma dell'iscrizione è in coda e il mailer è `log`. Per vederla in `storage/logs/laravel.log` serve un worker:

```bash
vendor/bin/sail artisan queue:work
```

In alternativa `vendor/bin/sail composer run dev` avvia insieme server, coda e Vite.

## API check-in

L'endpoint richiede un token Sanctum di un utente admin. Per crearne uno:

```bash
vendor/bin/sail artisan tinker --execute 'echo App\Domain\Users\Models\User::firstWhere("email", "admin@example.com")->createToken("check-in")->plainTextToken;'
```

Poi, con l'id del contest e l'id del runner:

```bash
curl -X POST http://localhost/api/contests/1/runners/1/check-in \
  -H "Accept: application/json" \
  -H "Authorization: Bearer IL_TUO_TOKEN"
```

| Risposta | Significato                         |
| -------- | ----------------------------------- |
| 200      | check-in registrato                 |
| 401      | token assente o non valido          |
| 403      | l'utente non è admin                |
| 404      | il runner non è iscritto al contest |
| 409      | il check-in è già stato fatto       |

## Test e controlli

```bash
vendor/bin/sail artisan test --compact
vendor/bin/sail bin pint
vendor/bin/sail bin phpstan analyse
vendor/bin/sail npm run types:check
vendor/bin/sail npm run check
```

## Regole di architettura (phparkitect)

```bash
vendor/bin/sail artisan clean-arch:make-arch-rules --force
vendor/bin/sail bin phparkitect check
```

Il primo comando rigenera `phparkitect.php` da `config/clean-architecture.php`, il secondo fallisce se una classe viola le regole dei layer o vive fuori da essi.
