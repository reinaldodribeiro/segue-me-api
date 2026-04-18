<?php

namespace App\Domain\Parish\Actions;

use App\Domain\Parish\DTOs\UpdateDioceseDTO;
use App\Domain\Parish\Models\Diocese;
use App\Domain\Parish\Repositories\DioceseRepositoryInterface;

class UpdateDiocese
{
    public function __construct(
        private readonly DioceseRepositoryInterface $dioceses,
    ) {}

    public function execute(Diocese $diocese, UpdateDioceseDTO $dto): Diocese
    {
        $data = array_filter([
            'name' => $dto->name,
            'logo' => $dto->logo,
            'active' => $dto->active,
        ], fn ($v) => $v !== null);

        return $this->dioceses->update($diocese, $data);
    }
}
