<?php

namespace App\Services;

use App\Models\User;
use App\Models\Coin;
use App\Models\Wallet;
use Exception;
use Illuminate\Support\Str;

class UserService
{
    /**
     * Register new user
     */
    public function registerUser(array $data): User
    {
        try {
            $data['password'] = bcrypt($data['password']);
            $data['user_type'] = $data['user_type'] ?? 'customer';
            $data['is_active'] = true;

            $user = User::create($data);

            // Initialize coin wallet
            Coin::create(['user_id' => $user->id]);

            // Initialize payment wallet
            Wallet::create(['user_id' => $user->id]);

            return $user;
        } catch (Exception $e) {
            throw new Exception('User registration failed: ' . $e->getMessage());
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile(User $user, array $data): User
    {
        try {
            $user->update($data);
            return $user;
        } catch (Exception $e) {
            throw new Exception('Profile update failed: ' . $e->getMessage());
        }
    }

    /**
     * Update password
     */
    public function updatePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        try {
            if (!password_verify($currentPassword, $user->password)) {
                throw new Exception('Current password is incorrect');
            }

            $user->update(['password' => bcrypt($newPassword)]);

            return true;
        } catch (Exception $e) {
            throw new Exception('Password update failed: ' . $e->getMessage());
        }
    }

    /**
     * Verify email
     */
    public function verifyEmail(User $user): bool
    {
        try {
            $user->update([
                'email_verified_at' => now(),
                'is_verified' => true,
            ]);

            return true;
        } catch (Exception $e) {
            throw new Exception('Email verification failed: ' . $e->getMessage());
        }
    }

    /**
     * Suspend user
     */
    public function suspendUser(User $user): bool
    {
        try {
            $user->update(['is_active' => false]);
            return true;
        } catch (Exception $e) {
            throw new Exception('User suspension failed: ' . $e->getMessage());
        }
    }

    /**
     * Activate user
     */
    public function activateUser(User $user): bool
    {
        try {
            $user->update(['is_active' => true]);
            return true;
        } catch (Exception $e) {
            throw new Exception('User activation failed: ' . $e->getMessage());
        }
    }

    /**
     * Generate referral code
     */
    public function generateReferralCode(User $user): string
    {
        return strtoupper(Str::random(8));
    }
}
