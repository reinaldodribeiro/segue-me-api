<?php

namespace App\Exports;

use App\Domain\People\Models\Person;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PeopleExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private readonly string $parishId,
        private readonly array $filters = [],
    ) {}

    public function collection(): Collection
    {
        $query = Person::query()
            ->where('parish_id', $this->parishId)
            ->where('active', true)
            ->orderBy('name');

        if (! empty($this->filters['type'])) {
            $query->where('type', $this->filters['type']);
        }

        if (! empty($this->filters['skills'])) {
            foreach ((array) $this->filters['skills'] as $skill) {
                $query->whereJsonContains('skills', $skill);
            }
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Tipo',
            'Nome',
            'Nome do Cônjuge',
            'Data de Nascimento',
            'Data de Nascimento (Cônjuge)',
            'Data de Casamento',
            'Telefone',
            'E-mail',
            'Habilidades',
            'Ano do Encontro',
            'Pontuação de Engajamento',
            'Cadastrado em',
        ];
    }

    public function map($person): array
    {
        return [
            $person->id,
            $person->type->value,
            $person->name,
            $person->partner_name ?? '',
            $person->birth_date?->format('d/m/Y') ?? '',
            $person->partner_birth_date?->format('d/m/Y') ?? '',
            $person->wedding_date?->format('d/m/Y') ?? '',
            implode(', ', $person->phones ?? []),
            $person->email ?? '',
            implode(', ', $person->skills ?? []),
            $person->encounter_year ?? '',
            $person->engagement_score,
            $person->created_at?->format('d/m/Y') ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
