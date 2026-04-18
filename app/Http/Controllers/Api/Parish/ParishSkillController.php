<?php

namespace App\Http\Controllers\Api\Parish;

use App\Domain\Parish\Repositories\ParishRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParishSkillController extends Controller
{
    public function __construct(
        private readonly ParishRepositoryInterface $parishes,
    ) {}

    public function index(string $parishId): JsonResponse
    {
        $parish = $this->parishes->findOrFail($parishId);

        return response()->json([
            'data' => $parish->available_skills ?? [],
        ]);
    }

    public function store(Request $request, string $parishId): JsonResponse
    {
        $request->validate([
            'skill' => ['required', 'string', 'max:100'],
        ]);

        $parish = $this->parishes->findOrFail($parishId);
        $skills = $parish->available_skills ?? [];
        $skill = trim($request->string('skill'));

        if (in_array($skill, $skills)) {
            return response()->json(['message' => 'Habilidade já cadastrada.'], 409);
        }

        $skills[] = $skill;
        sort($skills);

        $this->parishes->update($parish, ['available_skills' => $skills]);

        return response()->json(['data' => $skills], 201);
    }

    public function destroy(Request $request, string $parishId): JsonResponse
    {
        $request->validate([
            'skill' => ['required', 'string'],
        ]);

        $parish = $this->parishes->findOrFail($parishId);
        $skill = trim($request->string('skill'));
        $skills = array_values(array_filter(
            $parish->available_skills ?? [],
            fn ($s) => $s !== $skill
        ));

        $this->parishes->update($parish, ['available_skills' => $skills]);

        return response()->json(['data' => $skills]);
    }
}
