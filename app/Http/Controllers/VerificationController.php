<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;

class VerificationController extends Controller
{
    /**
     * Verify email
     *
     * @param Request $request
     * @param string $id
     * @param string $hash
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function verify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Invalid verification link'], 403);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified'], 200);
            // Optionally: return redirect(config('app.url') . '/auth/login?msg=AlreadyVerified');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        // Return JSON if expecting API response, or redirect to frontend login
        return redirect(url('/auth/login?msg=Email%20verificado%20exitosamente'));
    }
}
