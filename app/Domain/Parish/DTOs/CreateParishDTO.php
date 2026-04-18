<?php

namespace App\Domain\Parish\DTOs;

use App\Http\Requests\Parish\StoreParishRequest;
use Illuminate\Support\Str;

final readonly class CreateParishDTO
{
    public function __construct(
        public string $sectorId,
        public string $name,
        public string $slug,
        public ?string $logo = null,
        public string $primaryColor = '#2e6da4',
        public string $secondaryColor = '#4a9fd4',
    ) {}

    public static function fromRequest(StoreParishRequest $request, string $sectorId): self
    {
        return new self(
            sectorId: $sectorId,
            name: $request->validated('name'),
            slug: $request->validated('slug', Str::slug($request->validated('name'))),
            logo: $request->validated('logo'),
            primaryColor: $request->validated('primary_color', '#2e6da4'),
            secondaryColor: $request->validated('secondary_color', '#4a9fd4'),
        );
    }
}
