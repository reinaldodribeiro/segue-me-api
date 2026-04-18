<?php

namespace App\Domain\Parish\DTOs;

use App\Http\Requests\Parish\UpdateDioceseRequest;

final readonly class UpdateDioceseDTO
{
    public function __construct(
        public string $name,
        public ?string $logo = null,
        public ?bool $active = null,
    ) {}

    public static function fromRequest(UpdateDioceseRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            logo: $request->validated('logo'),
            active: $request->validated('active'),
        );
    }
}
