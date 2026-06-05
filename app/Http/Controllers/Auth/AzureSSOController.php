<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class AzureSSOController extends Controller
{
    /**
     * Redirect the user to the Azure AD authentication page.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('azure')->redirect();
    }

    /**
     * Handle the callback from Azure AD.
     * Authenticates the user against the existing local user base.
     * Does NOT create new users — authorization remains fully local.
     */
    public function callback(): RedirectResponse
    {
        try {
            $azureUser = Socialite::driver('azure')->user();
        } catch (Throwable $e) {
            Log::error('Azure SSO callback error', [
                'message' => $e->getMessage(),
                'class' => get_class($e),
            ]);

            return redirect()->route('login')
                ->withErrors(['email' => __('auth.sso_failed')]);
        }

        $email = $azureUser->getEmail();

        if (empty($email)) {
            return redirect()->route('login')
                ->withErrors(['email' => __('auth.sso_no_email')]);
        }

        /** @var User|null $user */
        $user = User::firstWhere('email', $email);

        if (! $user) {
            return redirect()->route('login')
                ->withErrors(['email' => __('auth.sso_user_not_found')]);
        }

        // Link the Azure object ID on first SSO login for future reference
        if (empty($user->azure_id)) {
            $user->updateQuietly(['azure_id' => $azureUser->getId()]);
        }

        Auth::login($user, remember: true);

        return redirect()->intended(RouteServiceProvider::HOME);
    }
}
