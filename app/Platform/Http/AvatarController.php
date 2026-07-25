<?php

namespace App\Platform\Http;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Servira profilnu sliku s PRIVATNOG diska (`documents`). Slike ne idu u public/
 * — Nginx u produkciji servira host checkout, ne storage kontejnera, pa
 * `storage:link` ne bi ni pomogao (isto rješenje kao prilozi dokumenata, Faza 5b).
 *
 * Vidjeti sliku smije samo prijavljeni korisnik, i to svoju ili sliku člana s
 * kojim dijeli domaćinstvo — nikad tuđu (CLAUDE.md §1 "domaćinstvo zadržava kontrolu").
 */
class AvatarController
{
    public function __invoke(User $user): StreamedResponse
    {
        $viewer = auth()->user();
        abort_unless($viewer instanceof User, 403);

        abort_unless($viewer->is($user) || $this->shareHousehold($viewer, $user), 403);
        abort_unless(filled($user->avatar_path) && Storage::disk('documents')->exists($user->avatar_path), 404);

        return Storage::disk('documents')->response(
            $user->avatar_path,
            headers: ['Cache-Control' => 'private, max-age=3600'],
        );
    }

    private function shareHousehold(User $viewer, User $user): bool
    {
        return $viewer->households()
            ->whereIn('households.id', $user->households()->select('households.id'))
            ->exists();
    }
}
