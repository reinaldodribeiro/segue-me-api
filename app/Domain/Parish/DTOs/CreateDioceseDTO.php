<?php

namespace App\Domain\Parish\DTOs;

use App\Http\Requests\Parish\StoreDioceseRequest;
use Illuminate\Support\Str;

final readonly class CreateDioceseDTO
{
    public function __construct(
        public string $name,
        public string $slug,
        public ?string $logo = null,
    ) {}

    public static function fromRequest(StoreDioceseRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            slug: $request->validated('slug', Str::slug($request->validated('name'))),
            logo: $request->validated('logo'),
        );
    }
}
