<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TwoFactorChallenge;
use App\Models\TwoFactorCode;
use App\Models\User;
use App\Notifications\TwoFactorCodeNotification;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class AuthController extends Controller
{
    /**
     * POST /api/login
     */
    public function login(Request $request)
    {
        try {
            $data = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required', 'string'],
            ]);

            $user = User::where('email', $data['email'])->where('activo', true)->first();

            if (! $user || ! Hash::check($data['password'], $user->password)) {
                return response()->json(['message' => 'Las credenciales proporcionadas no son correctas.'], 422);
            }

            if ($user->two_factor_confirmed_at) {
                $challenge = TwoFactorChallenge::create([
                    'user_id' => $user->id,
                    'token' => Str::random(64),
                    'expires_at' => now()->addMinutes(10),
                ]);

                if ($user->two_factor_method === 'email') {
                    $codigo = (string) random_int(100000, 999999);

                    TwoFactorCode::updateOrCreate(
                        ['user_id' => $user->id],
                        ['code' => Hash::make($codigo), 'expires_at' => now()->addMinutes(5)]
                    );

                    $user->notify(new TwoFactorCodeNotification($codigo));
                }

                return response()->json([
                    'requires_2fa' => true,
                    'method' => $user->two_factor_method,
                    'challenge_token' => $challenge->token,
                ]);
            }

            $user->tokens()->delete();
            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'requires_2fa' => false,
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'nombre_completo' => $user->nombre_completo,
                    'email' => $user->email,
                ],
                'roles' => $user->roles()->pluck('nombre'),
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('AuthController@login: error al iniciar sesión', [
                'email' => $request->input('email'),
                'mensaje' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile(),
            ]);

            return response()->json(['message' => 'Ocurrió un error al iniciar sesión. Intenta de nuevo.'], 500);
        }
    }

    /**
     * POST /api/logout
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json(['message' => 'Sesión cerrada correctamente.']);
        } catch (Throwable $e) {
            Log::error('AuthController@logout: error al cerrar sesión', [
                'user_id' => $request->user()?->id,
                'mensaje' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile(),
            ]);

            return response()->json(['message' => 'No se pudo cerrar la sesión.'], 500);
        }
    }

    /**
     * GET /api/me
     */
    public function me(Request $request)
    {
        try {
            $user = $request->user();

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'nombre_completo' => $user->nombre_completo,
                    'email' => $user->email,
                ],
                'roles' => $user->roles()->pluck('nombre'),
                'two_factor_enabled' => (bool) $user->two_factor_confirmed_at,
                'two_factor_method' => $user->two_factor_method,
            ]);
        } catch (Throwable $e) {
            Log::error('AuthController@me: error al obtener el usuario autenticado', [
                'mensaje' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile(),
            ]);

            return response()->json(['message' => 'No se pudo obtener la información de la sesión.'], 500);
        }
    }

    /**
     * POST /api/forgot-password
     */
    public function forgotPassword(Request $request)
    {
        try {
            $request->validate(['email' => ['required', 'email']]);
            app()->setLocale('es'); 
            Password::sendResetLink($request->only('email'));

            return response()->json(['message' => 'Si el correo está registrado, se envió un enlace de recuperación.']);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('AuthController@forgotPassword: error al enviar el enlace de recuperación', [
                'email' => $request->input('email'),
                'mensaje' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile(),
            ]);

            // Igual devolvemos un mensaje genérico: no revelar detalles internos ni si el correo existe
            return response()->json(['message' => 'Si el correo está registrado, se envió un enlace de recuperación.']);
        }
    }

    /**
     * POST /api/reset-password
     */
    public function resetPassword(Request $request)
    {
        try {
            $data = $request->validate([
                'token' => ['required'],
                'email' => ['required', 'email'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            $status = Password::reset(
                $data,
                function (User $user, string $password) {
                    $user->forceFill([
                        'password' => Hash::make($password),
                        'remember_token' => Str::random(60),
                    ])->save();

                    $user->tokens()->delete();

                    event(new PasswordReset($user));
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return response()->json(['message' => 'Contraseña actualizada correctamente.']);
            }

            return response()->json(['message' => 'El enlace de recuperación no es válido o ya expiró.'], 422);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('AuthController@resetPassword: error al restablecer la contraseña', [
                'email' => $request->input('email'),
                'mensaje' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile(),
            ]);

            return response()->json(['message' => 'No se pudo restablecer la contraseña.'], 500);
        }
    }
}
