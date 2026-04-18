<?php

namespace App\Domain\Parish\Actions;

use App\Domain\Parish\DTOs\CreateDioceseDTO;
use App\Domain\Parish\Models\Diocese;
use App\Domain\Parish\Repositories\DioceseRepositoryInterface;
use App\Exceptions\DuplicateDioceseSlugException;
use Illuminate\Support\Str;

class CreateDiocese
{
    public function __construct(
        private readonly DioceseRepositoryInterface $dioceses,
    ) {}

    public function execute(CreateDioceseDTO $dto): Diocese
    {
        $slug = $dto->slug ?: Str::slug($dto->name);

        if ($this->dioceses->findBySlug($slug)) {
            throw new DuplicateDioceseSlugException;
        }

        return $this->dioceses->create([
            'name' => $dto->name,
            'slug' => $slug,
            'logo' => $dto->logo,
            'active' => true,
        ]);
    }
}
