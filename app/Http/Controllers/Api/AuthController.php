<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Referral;
use App\Services\UserService;
use App\Services\CoinService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    protected $userService;
    protected $coinService;

    public function __construct(UserService $userService, CoinService $coinService)
    {
        $this->userService = $userService;
        $this->coinService = $coinService;
    }

    /**
     * Register new user
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|unique:users',
            'user_type' => 'in:customer,vendor',
            'referral_code' => 'nullable|string|exists:users,referral_code',
        ]);

        try {
            $user = $this->userService->registerUser($validated);

            // Handle referral
            if ($request->filled('referral_code')) {
                $referrer = User::where('id', $request->input('referral_code'))->first();
                if ($referrer) {
                    $referralCode = $this->userService->generateReferralCode($user);
                    Referral::create([
                        'referrer_id' => $referrer->id,
                        'referred_user_id' => $user->id,
                        'referral_code' => $referralCode,
                        'status' => 'pending',
                    ]);
                    
                    $this->coinService->awardReferralCoins($referrer->id, $user->id);
                }
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->success([
                'user' => $user,
                'token' => $token,
            ], 'User registered successfully', 201);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !password_verify($validated['password'], $user->password)) {
            return $this->error('Invalid credentials', 401);
        }

        if (!$user->is_active) {
            return $this->error('Account is suspended', 403);
        }

        $user->update(['last_login_at' => now()]);
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'user' => $user,
            'token' => $token,
        ], 'Login successful');
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return $this->success(null, 'Logged out successfully');
    }

    /**
     * Get current user
     */
    public function me(Request $request)
    {
        $user = $request->user()->load(['coins', 'wallet', 'vendor']);
        return $this->success($user, 'User data retrieved');
    }
}
