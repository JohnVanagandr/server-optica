<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * Iniciar sesión y emitir Tokens (Acceso y Refresco)
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $user = User::where('email', $request->email)->first();

            // 1. Validación de credenciales
            if (!$user || !Hash::check($request->password, $user->password)) {
                return $this->error('Credenciales incorrectas.', 401);
            }

            // 2. Escudo de Integridad: Validación de cuenta activa
            if (!$user->is_active) {
                return $this->error('Esta cuenta ha sido suspendida. Contacte al administrador.', 403);
            }

            // 3. Cargar las relaciones necesarias para el Resource (Incluyendo 'permissions')
            $user->load(['branch.account', 'roles', 'permissions']);

            // 4. Generación de Tokens
            $accessToken = $user->createToken('access_token', ['*'], now()->addHours(2))->plainTextToken;
            $refreshToken = $user->createToken('refresh_token', ['issue-access-token'], now()->addDays(7))->plainTextToken;

            return $this->success([
                'user'          => new UserResource($user),
                'access_token'  => $accessToken,
                'refresh_token' => $refreshToken,
                'token_type'    => 'Bearer'
            ], 'Inicio de sesión exitoso.');
            
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error interno durante el inicio de sesión');
        }
    }

    /**
     * Renovar el Token de Acceso
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user->currentAccessToken()->can('issue-access-token')) {
                return $this->error('Token inválido para esta operación.', 403);
            }

            $user->tokens()->where('name', 'access_token')->delete();

            $newAccessToken = $user->createToken('access_token', ['*'], now()->addHours(2))->plainTextToken;

            return $this->success([
                'access_token' => $newAccessToken,
                'token_type'   => 'Bearer'
            ], 'Token renovado con éxito.');
            
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al renovar el token');
        }
    }

    /**
     * Cerrar sesión
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->tokens()->delete();
            return $this->success([], 'Sesión cerrada correctamente.');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al cerrar sesión');
        }
    }

    /**
     * Registro básico del usuario (Protegido con Transacción)
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            // Usamos una transacción para garantizar que usuario y rol se creen juntos o ninguno
            $user = DB::transaction(function () use ($request) {
                $newUser = User::create([
                    'name'     => $request->name,
                    'email'    => $request->email,
                    'password' => Hash::make($request->password),
                ]);

                $newUser->assignRole('client');
                
                return $newUser;
            });

            // Cargar relaciones requeridas, incluyendo roles y permisos
            $user->load(['branch', 'roles', 'permissions']);

            $accessToken = $user->createToken('access_token', ['*'], now()->addHours(2))->plainTextToken;
            $refreshToken = $user->createToken('refresh_token', ['issue-access-token'], now()->addDays(7))->plainTextToken;

            return $this->success([
                'user'          => new UserResource($user),
                'access_token'  => $accessToken,
                'refresh_token' => $refreshToken,
                'token_type'    => 'Bearer'
            ], 'Registro completado. Por favor, configura tu empresa para continuar.', 201);

        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al registrar el usuario');
        }
    }

    /**
     * Obtener el perfil del usuario autenticado (Para sincronizar AuthContext en Frontend)
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Cargar el plan y el widget activo de la sucursal
            $user->load([
                'roles', 
                'permissions', 
                'branch.account',
                'branch.activeWidget'
            ]);

            return $this->success(
                new \App\Http\Resources\User\UserResource($user),
                'Perfil de usuario obtenido con éxito.'
            );
            
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al obtener el perfil del usuario');
        }
    }   
}