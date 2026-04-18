<?php

namespace App\Domain\Parish\Actions;

use App\Domain\Parish\DTOs\CreateParishDTO;
use App\Domain\Parish\Models\Parish;
use App\Domain\Parish\Repositories\ParishRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateParish
{
    public function __construct(
        private readonly ParishRepositoryInterface $parishes,
    ) {}

    public function execute(CreateParishDTO $dto): Parish
    {
        return DB::transaction(fn () => $this->parishes->create([
            'sector_id' => $dto->sectorId,
            'name' => $dto->name,
            'slug' => $dto->slug ?: Str::slug($dto->name),
            'logo' => $dto->logo,
            'primary_color' => $dto->primaryColor,
            'secondary_color' => $dto->secondaryColor,
        ]));
    }
}
