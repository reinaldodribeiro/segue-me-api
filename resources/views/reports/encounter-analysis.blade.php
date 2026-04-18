<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Análise — {{ $encounter->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #333; font-size: 11px; line-height: 1.6; }
        .header { text-align: center; border-bottom: 2px solid {{ $parish->primary_color ?? '#2e6da4' }}; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { color: {{ $parish->primary_color ?? '#2e6da4' }}; margin: 0; font-size: 20px; }
        .header h2 { margin: 4px 0 0; font-size: 14px; font-weight: normal; }
        .header .subtitle { font-size: 10px; color: #666; margin-top: 4px; }
        .meta { margin-bottom: 20px; font-size: 10px; color: #666; }
        .meta span { margin-right: 20px; }
        .section { margin-bottom: 28px; }
        .section-title { color: {{ $parish->primary_color ?? '#2e6da4' }}; font-size: 15px; font-weight: bold; margin-bottom: 8px; border-bottom: 1px solid #ddd; padding-bottom: 4px; }
        .content { text-align: justify; white-space: pre-line; }
        /* Team card */
        .team-block { margin-bottom: 22px; page-break-inside: avoid; border: 1px solid #ddd; border-radius: 4px; overflow: hidden; }
        .team-header { background: {{ $parish->primary_color ?? '#2e6da4' }}; color: white; padding: 6px 10px; font-weight: bold; font-size: 12px; display: flex; justify-content: space-between; }
        .team-ratings { padding: 6px 10px; background: #f8f8f8; border-bottom: 1px solid #eee; font-size: 10px; color: #555; }
        .team-ratings span { margin-right: 14px; }
        .comment-row { padding: 4px 10px; font-size: 10px; border-bottom: 1px solid #f0f0f0; }
        .comment-row strong { color: #555; }
        /* Members table */
        .members-table { width: 100%; border-collapse: collapse; font-size: 10px; }
        .members-table th { background: #f0f0f0; padding: 4px 8px; text-align: left; font-weight: bold; color: #555; border-bottom: 1px solid #ddd; }
        .members-table td { padding: 4px 8px; border-bottom: 1px solid #f5f5f5; vertical-align: top; }
        .members-table tr:last-child td { border-bottom: none; }
        .tag-s { color: #2e7d32; font-weight: bold; }
        .tag-p { color: #e65100; font-weight: bold; }
        .tag-n { color: #c62828; font-weight: bold; }
        .footer { text-align: center; color: #999; font-size: 10px; margin-top: 30px; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        @if($parish->logo)
            <img src="{{ storage_path('app/public/' . $parish->logo) }}" height="50" alt="{{ $parish->name }}">
        @endif
        <h1>{{ $parish->name }}</h1>
        <h2>Análise do Encontro — {{ $encounter->name }}
            @if($encounter->edition_number) ({{ $encounter->edition_number }}ª Edição) @endif
        </h2>
        <div class="subtitle">Análise geral gerada por IA · Avaliações dos coordenadores</div>
    </div>

    <div class="meta">
        <span><strong>Data:</strong> {{ $encounter->date?->format('d/m/Y') }}</span>
        @if($encounter->location)
        <span><strong>Local:</strong> {{ $encounter->location }}</span>
        @endif
        <span><strong>Movimento:</strong> {{ $encounter->movement?->name }}</span>
        <span><strong>Gerado em:</strong> {{ $analysis->generated_at?->format('d/m/Y H:i') }}</span>
    </div>

    {{-- Análise Geral (IA) --}}
    <div class="section">
        <div class="section-title">Análise Geral do Encontro</div>
        <div class="content">{{ $analysis->general_analysis }}</div>
    </div>

    {{-- Avaliações por Equipe (dados do banco) --}}
    @if($teamEvaluations->count())
    <div class="section">
        <div class="section-title">Avaliações por Equipe</div>

        @foreach($teamEvaluations as $eval)
        <div class="team-block">
            <div class="team-header">
                <span>{{ $eval->team->name }}</span>
                <span>Nota geral: {{ $eval->overall_team_rating }}/5</span>
            </div>

            <div class="team-ratings">
                <span><strong>Preparação:</strong> {{ $eval->preparation_rating }}/5</span>
                <span><strong>Trabalho em equipe:</strong> {{ $eval->teamwork_rating }}/5</span>
                <span><strong>Materiais:</strong> {{ $eval->materials_rating }}/5</span>
            </div>

            @if($eval->preparation_comment || $eval->teamwork_comment || $eval->materials_comment)
            <div class="comment-row">
                @if($eval->preparation_comment)<div><strong>Prep.:</strong> {{ $eval->preparation_comment }}</div>@endif
                @if($eval->teamwork_comment)<div><strong>Equipe:</strong> {{ $eval->teamwork_comment }}</div>@endif
                @if($eval->materials_comment)<div><strong>Materiais:</strong> {{ $eval->materials_comment }}</div>@endif
            </div>
            @endif

            @if($eval->issues_text)
            <div class="comment-row"><strong>Problemas:</strong> {{ $eval->issues_text }}</div>
            @endif
            @if($eval->improvements_text)
            <div class="comment-row"><strong>Melhorias sugeridas:</strong> {{ $eval->improvements_text }}</div>
            @endif

            @if($eval->memberEvaluations->count())
            <table class="members-table">
                <thead>
                    <tr>
                        <th>Membro</th>
                        <th style="width:60px;text-align:center">Comp.</th>
                        <th style="width:60px;text-align:center">Resp.</th>
                        <th style="width:60px;text-align:center">Rec.</th>
                        <th>Destaque positivo</th>
                        <th>Problema observado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($eval->memberEvaluations as $me)
                    <tr>
                        <td>{{ $me->person->name }}</td>
                        <td style="text-align:center">{{ $me->commitment_rating }}/5</td>
                        <td style="text-align:center">
                            @if($me->fulfilled_responsibilities === 'yes') <span class="tag-s">Sim</span>
                            @elseif($me->fulfilled_responsibilities === 'partially') <span class="tag-p">Parc.</span>
                            @else <span class="tag-n">Não</span>
                            @endif
                        </td>
                        <td style="text-align:center">
                            @if($me->recommend === 'yes') <span class="tag-s">Sim</span>
                            @elseif($me->recommend === 'with_reservations') <span class="tag-p">Ressalvas</span>
                            @else <span class="tag-n">Não</span>
                            @endif
                        </td>
                        <td>{{ $me->positive_highlight ?: '—' }}</td>
                        <td>{{ $me->issue_observed ?: '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    <div class="footer">
        Gerado em {{ now()->format('d/m/Y H:i') }} — Segue-me
    </div>
</body>
</html>
