<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>{{ $encounter->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #333; font-size: 12px; }
        .header { text-align: center; border-bottom: 2px solid {{ $parish->primary_color ?? '#2e6da4' }}; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { color: {{ $parish->primary_color ?? '#2e6da4' }}; margin: 0; font-size: 20px; }
        .header h2 { margin: 4px 0 0; font-size: 14px; font-weight: normal; }
        .meta { display: flex; gap: 20px; margin-bottom: 20px; font-size: 11px; color: #666; }
        .team { margin-bottom: 24px; page-break-inside: avoid; }
        .team-header { background: {{ $parish->primary_color ?? '#2e6da4' }}; color: white; padding: 6px 10px; border-radius: 4px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        th { background: #f0f0f0; text-align: left; padding: 5px 8px; font-size: 11px; }
        td { padding: 5px 8px; border-bottom: 1px solid #eee; }
        .status-confirmed { color: #16a34a; font-weight: bold; }
        .status-pending    { color: #d97706; }
        .status-refused    { color: #dc2626; }
        .footer { text-align: center; color: #999; font-size: 10px; margin-top: 30px; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        @if($parish->logo)
            <img src="{{ storage_path('app/public/' . $parish->logo) }}" height="50" alt="{{ $parish->name }}">
        @endif
        <h1>{{ $parish->name }}</h1>
        <h2>{{ $encounter->name }}
            @if($encounter->edition_number) — {{ $encounter->edition_number }}ª Edição @endif
        </h2>
    </div>

    <div class="meta">
        <span><strong>Data:</strong> {{ $encounter->date?->format('d/m/Y') }}</span>
        @if($encounter->location)
        <span><strong>Local:</strong> {{ $encounter->location }}</span>
        @endif
        <span><strong>Movimento:</strong> {{ $encounter->movement?->name }}</span>
        <span><strong>Responsável:</strong> {{ $encounter->responsibleUser?->name }}</span>
    </div>

    @foreach($encounter->teams as $team)
    <div class="team">
        <div class="team-header">
            {{ $team->name }}
            ({{ $team->members->whereNotIn('status', ['refused'])->count() }}/{{ $team->max_members }})
        </div>
        @if($team->members->count())
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Tipo</th>
                    <th>Habilidades</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($team->members as $member)
                <tr>
                    <td>{{ $member->person?->name }}</td>
                    <td>{{ $member->person?->type?->label() }}</td>
                    <td>{{ implode(', ', $member->person?->skills ?? []) }}</td>
                    <td class="status-{{ $member->status->value }}">{{ $member->status->label() }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p style="color:#999;padding:6px 10px;">Nenhum membro alocado.</p>
        @endif
    </div>
    @endforeach

    <div class="footer">
        Gerado em {{ now()->format('d/m/Y H:i') }} — Segue-me
    </div>
</body>
</html>
