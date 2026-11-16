<x-mail::message>
# Iscrizione confermata

Ciao {{ $runner->first_name }} {{ $runner->last_name }},

la tua iscrizione a **{{ $contest->name }}** del {{ $contest->date->format('d/m/Y') }} è confermata.

Presentati al check-in il giorno della gara con un documento di identità.

A presto,<br>
{{ config('app.name') }}
</x-mail::message>
