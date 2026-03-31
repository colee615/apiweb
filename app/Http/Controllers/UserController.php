<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Browsershot;
use Tymon\JWTAuth\Exceptions\JWTException;

class UserController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return User::orderBy('created_at', 'desc')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = new User();
        $user->nombre = $request->nombre;
        $user->apellido = $request->apellido;
        $user->telefono = $request->telefono;
        $user->email = $request->email;
        $user->carnet = $request->carnet;
        $user->rol = $request->rol;
        $user->password = Hash::make($request->input('password'));
        $user->save();

        DB::table('activity_logs')->insert([
            'user_id' => $request->user_id,
            'user_email' => $request->user_email,
            'activity_type' => 'Creacion de personal',
            'description' => 'El usuario creo un personal',
            'new_values' => json_encode($user),
            'ip_address' => request()->ip(),
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $user;
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return $user;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $oldValues = $user->getOriginal();

        $user->nombre = $request->nombre;
        $user->apellido = $request->apellido;
        $user->telefono = $request->telefono;
        $user->email = $request->email;
        $user->carnet = $request->carnet;
        $user->rol = $request->rol;
        $user->estado = $request->estado;

        if ($request->has('imagen') && $request->imagen !== $user->imagen) {
            if ($user->imagen) {
                $oldImagePath = parse_url($user->imagen, PHP_URL_PATH);
                $relativePath = str_replace('/storage/', '', $oldImagePath);

                if (Storage::disk('public')->exists($relativePath)) {
                    Storage::disk('public')->delete($relativePath);
                } else {
                    Log::info('La imagen antigua no existe: ' . $relativePath);
                }
            }

            $base64Image = $request->imagen;
            $imageData = explode(',', $base64Image);
            $mimeType = explode(';', explode(':', $imageData[0])[1])[0];
            $extension = explode('/', $mimeType)[1];
            $image = base64_decode($imageData[1]);
            $imageName = time() . '.' . $extension;
            $path = 'user/' . $imageName;

            Storage::disk('public')->put($path, $image);

            $user->imagen = url('storage/' . $path);
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        $newValues = $user->getAttributes();

        DB::table('activity_logs')->insert([
            'user_id' => $request->user_id,
            'user_email' => $request->user_email,
            'activity_type' => 'Actualizacion de personal',
            'description' => 'El usuario actualizo un personal',
            'old_values' => json_encode($oldValues),
            'new_values' => json_encode($newValues),
            'ip_address' => request()->ip(),
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $user;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->estado = 0;
        $user->save();

        return $user;
    }

    protected function registrarActividad(Request $request, $tipoActividad, $descripcion, $veterinariaId = null)
    {
        DB::table('activity_logs')->insert([
            'activity_type' => $tipoActividad,
            'description' => $descripcion,
            'user_email' => $request->email,
            'user_id' => $request->user_id,
            'ip_address' => $request->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)
            ->where('is_active', true)
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            $this->registrarActividad($request, 'Login error', 'Intento de login fallido', $user?->id);

            return response()->json(['error' => 'Credenciales incorrectas'], 401);
        }

        try {
            if (! $token = auth('api_users')->login($user)) {
                return response()->json(['error' => 'No se pudo crear el token'], 500);
            }

            $this->registrarActividad($request, 'Login exitoso', 'Login exitoso para la user', $user->id);

            return response()->json([
                'message' => 'Inicio de sesion correcto (user)',
                'token' => $token,
                'user' => $user,
            ]);
        } catch (JWTException $e) {
            Log::error('Excepcion al crear el token (user): ' . $e->getMessage());

            return response()->json(['error' => 'No se pudo crear el token'], 500);
        }
    }

    public function exportPdf(Request $request)
    {
        try {
            $path = storage_path('app/public/usuarios.pdf');
            $usuarios = User::all();

            foreach ($usuarios as $usuario) {
                if ($usuario->imagen) {
                    $imagePath = public_path('storage/user/' . basename($usuario->imagen));
                    $usuario->imageSrc = file_exists($imagePath)
                        ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($imagePath))
                        : null;
                } else {
                    $usuario->imageSrc = null;
                }
            }

            $resumenPorRol = $usuarios->groupBy('rol')->map(function ($u) {
                return [
                    'total' => $u->count(),
                    'activos' => $u->where('estado', 1)->count(),
                    'inactivos' => $u->where('estado', 0)->count(),
                ];
            });

            $ultimoNumero = DB::table('activity_logs')
                ->where('activity_type', 'Exportacion de reporte de usuarios')
                ->max('report_number');

            $nuevoNumero = $ultimoNumero ? $ultimoNumero + 1 : 1;

            $html = view('exports.users-pdf', compact('usuarios', 'resumenPorRol', 'nuevoNumero'))->render();

            Browsershot::html($html)
                ->showBackground()
                ->margins(20, 10, 20, 10)
                ->format('A4')
                ->save($path);

            DB::table('activity_logs')->insert([
                'user_id' => $request->user_id ?? null,
                'user_email' => $request->user_email ?? null,
                'activity_type' => 'Exportacion de reporte de usuarios',
                'description' => "El usuario exporto el reporte de usuarios Nro $nuevoNumero",
                'old_values' => null,
                'new_values' => null,
                'ip_address' => $request->ip(),
                'status' => true,
                'report_number' => $nuevoNumero,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->download($path);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error generando PDF',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function perfil($id)
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        return response()->json($user);
    }
}
