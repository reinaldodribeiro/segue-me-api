<?php

namespace App\Domain\Parish\DTOs;

use App\Http\Requests\Parish\StoreSectorRequest;
use Illuminate\Support\Str;

final readonly class CreateSectorDTO
{
    public function __construct(
        public string $dioceseId,
        public string $name,
        public string $slug,
    ) {}

    public static function fromRequest(StoreSectorRequest $request, string $dioceseId): self
    {
        return new self(
            dioceseId: $dioceseId,
            name: $request->validated('name'),
            slug: $request->validated('slug', Str::slug($request->validated('name'))),
        );
    }
}
