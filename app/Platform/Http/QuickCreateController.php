<?php

namespace App\Platform\Http;

use App\Platform\Models\Household;
use App\Platform\QuickCapture\QuickCaptureRegistry;
use App\Platform\QuickCapture\QuickCreateContract;
use Filament\Facades\Filament;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Generički endpoint brzog dodavanja (modal Alpine + fetch POST). Ruta panela
 * (SetUpPanel middleware) — auth i pripadnost domaćinstvu provjeravamo ovdje
 * (kao SearchController), tenant iz `h`. Modul se ne zna pojedinačno — handler
 * dolazi iz registryja po ključu (CLAUDE.md §12/§18).
 */
class QuickCreateController
{
    public function __invoke(Request $request, string $key, QuickCaptureRegistry $registry): JsonResponse
    {
        $user = auth()->user();
        abort_unless($user !== null, 403);

        $household = Household::find((int) $request->query('h'));
        abort_unless(
            $household instanceof Household
                && $household->members()->where('user_id', $user->getKey())->exists(),
            404,
        );

        $handlerClass = $registry->handlerClassFor($key);
        abort_unless($handlerClass !== null, 404);

        /** @var QuickCreateContract $handler */
        $handler = app($handlerClass);

        // Eksplicitna validacija + JSON 422 (fetch iz modala to čita); izbjegava
        // framework redirect-with-errors put pod panel middleware grupom.
        $validator = Validator::make($request->all(), $handler->rules());

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->toArray()], 422);
        }

        Filament::setTenant($household);
        $handler->create($validator->validated(), $household, $user);

        return response()->json(['ok' => true]);
    }
}
