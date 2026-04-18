<?php

namespace App\Exports;

use App\Domain\Encounter\Models\EncounterParticipant;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EncounterParticipantsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private readonly string $encounterId,
    ) {}

    public function collection(): Collection
    {
        return EncounterParticipant::query()
            ->where('encounter_id', $this->encounterId)
            ->orderBy('name')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nome',
            'Nome do Cônjuge',
            'Tipo',
            'Telefone',
            'E-mail',
            'Data de Nascimento',
            'Data de Nascimento (Cônjuge)',
            'Convertido em Pessoa',
        ];
    }

    public function map($participant): array
    {
        return [
            $participant->name,
            $participant->partner_name ?? '',
            $participant->type->label(),
            $participant->phone ?? '',
            $participant->email ?? '',
            $participant->birth_date?->format('d/m/Y') ?? '',
            $participant->partner_birth_date?->format('d/m/Y') ?? '',
            $participant->isConverted() ? 'Sim' : 'Não',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
