<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Encontristas — {{ $encounter->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #333; font-size: 12px; }
        .header { text-align: center; border-bottom: 2px solid {{ $parish->primary_color ?? '#6d28d9' }}; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { color: {{ $parish->primary_color ?? '#6d28d9' }}; margin: 0; font-size: 20px; }
        .header h2 { margin: 4px 0 0; font-size: 14px; font-weight: normal; }
        .meta { display: flex; gap: 20px; margin-bottom: 20px; font-size: 11px; color: #666; }
        .meta span { font-weight: bold; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        th { background: #f0f0f0; text-align: left; padding: 5px 8px; font-size: 11px; }
        td { padding: 5px 8px; border-bottom: 1px solid #eee; }
        .badge-couple { background: #ede9fe; color: #6d28d9; padding: 1px 6px; border-radius: 4px; font-size: 10px; }
        .badge-youth  { background: #dbeafe; color: #1d4ed8; padding: 1px 6px; border-radius: 4px; font-size: 10px; }
.footer { text-align: center; color: #999; font-size: 10px; margin-top: 30px; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        @if($parish->logo)
            <img src="{{ storage_path('app/public/' . $parish->logo) }}" height="50" alt="{{ $parish->name }}">
        @endif
        <h1>{{ $parish->name }}</h1>
        <h2>Encontristas — {{ $encounter->name }}
            @if($encounter->edition_number) — {{ $encounter->edition_number }}ª Edição @endif
        </h2>
    </div>

    <div class="meta">
        <div>Data: <span>{{ $encounter->date?->format('d/m/Y') ?? '—' }}</span></div>
        @if($encounter->location)
            <div>Local: <span>{{ $encounter->location }}</span></div>
        @endif
        <div>Total de encontristas: <span>{{ $participants->count() }}</span></div>
        @if($encounter->max_participants)
            <div>Vagas máximas: <span>{{ $encounter->max_participants }}</span></div>
        @endif
    </div>

    @if($participants->isEmpty())
        <p style="color:#999; text-align:center; padding: 30px 0;">Nenhum encontrista cadastrado.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nome</th>
                    <th>Tipo</th>
                    <th>Telefone</th>
                    <th>E-mail</th>
                </tr>
            </thead>
            <tbody>
                @foreach($participants as $i => $p)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>
                            {{ $p->name }}
                            @if($p->partner_name)
                                <br><small style="color:#888">/ {{ $p->partner_name }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="{{ $p->type->value === 'couple' ? 'badge-couple' : 'badge-youth' }}">
                                {{ $p->type->label() }}
                            </span>
                        </td>
                        <td>{{ $p->phone ?? '—' }}</td>
                        <td>{{ $p->email ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Gerado em {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
