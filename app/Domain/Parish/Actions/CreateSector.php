<?php

namespace App\Domain\Parish\Actions;

use App\Domain\Parish\DTOs\CreateSectorDTO;
use App\Domain\Parish\Models\Sector;
use App\Domain\Parish\Repositories\SectorRepositoryInterface;
use Illuminate\Support\Str;

class CreateSector
{
    public function __construct(
        private readonly SectorRepositoryInterface $sectors,
    ) {}

    public function execute(CreateSectorDTO $dto): Sector
    {
        return $this->sectors->create([
            'diocese_id' => $dto->dioceseId,
            'name' => $dto->name,
            'slug' => $dto->slug ?: Str::slug($dto->name),
        ]);
    }
}
