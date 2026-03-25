<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Spatie\Browsershot\Browsershot;

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

        // Registrar la actividad de confirmación en la tabla de auditoría
        DB::table('activity_logs')->insert([
            'user_id' => $request->user_id,
            'user_email' => $request->user_email,
            'activity_type' => 'Creacion de personal',
            'description' => 'El usuario creó un personal',
            'new_values' => json_encode($user), // Guardar los nuevos valores en formato JSON
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
        // Guardar los valores antiguos antes de la actualización
        $oldValues = $user->getOriginal();

        $user->nombre = $request->nombre;
        $user->apellido = $request->apellido;
          $user->telefono = $request->telefono;
        $user->email = $request->email;
        $user->carnet = $request->carnet;
        $user->rol = $request->rol;
        $user->estado = $request->estado;

        if ($request->has('imagen') && $request->imagen !== $user->imagen) {
            // Eliminar la imagen antigua si existe
            if ($user->imagen) {
                // Extraer la ruta relativa del archivo
                $oldImagePath = parse_url($user->imagen, PHP_URL_PATH);
                $relativePath = str_replace('/storage/', '', $oldImagePath);


                // Verificar y eliminar la imagen antigua si existe
                if (Storage::disk('public')->exists($relativePath)) {

                    Storage::disk('public')->delete($relativePath);
                } else {
                    Log::info('La imagen antigua no existe: ' . $relativePath);
                }
            }

            // Decodificar la nueva imagen
            $base64Image = $request->imagen;
            $imageData = explode(',', $base64Image);
            $mimeType = explode(';', explode(':', $imageData[0])[1])[0];
            $extension = explode('/', $mimeType)[1]; // Extraer la extensión del tipo MIME
            $image = base64_decode($imageData[1]); // Decodificar base64

            // Generar un nombre único para la nueva imagen
            $imageName = time() . '.' . $extension;

            // Guardar la nueva imagen en el directorio 'user' dentro de 'storage/app/public'
            $path = 'user/' . $imageName;
            Storage::disk('public')->put($path, $image);

            // Obtener la URL completa de la nueva imagen
            $imageUrl = url('storage/' . $path);

            // Actualizar la URL de la imagen en la base de datos
            $user->imagen = $imageUrl;
        }
        // Manejar la actualización de la contraseña
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        // Guardar los cambios
        $user->save();

        // Guardar los valores nuevos después de la actualización
        $newValues = $user->getAttributes();

        // Registrar la actividad de actualización en la tabla de auditoría
        DB::table('activity_logs')->insert([
            'user_id' => $request->user_id,
            'user_email' => $request->user_email,
            'activity_type' => 'Actualización de personal',
            'description' => 'El usuario actualizó un personal',
            'old_values' => json_encode($oldValues), // Guardar los valores antiguos en formato JSON
            'new_values' => json_encode($newValues), // Guardar los nuevos valores en formato JSON
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
        // Validar la solicitud
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            if (!Hash::check($request->password, $user->password)) {
                // Registrar en el log de actividad antes de salir
                $this->registrarActividad($request, 'Login error', 'Intento de login fallido por contraseña incorrecta', $user->id);
                return response()->json(['error' => 'Credenciales incorrectas'], 401);
            }

            try {
                if (!$token = auth('api_users')->login($user)) {
                    return response()->json(['error' => 'No se pudo crear el token'], 500);
                }

                $this->registrarActividad($request, 'Login exitoso', 'Login exitoso para la user', $user->id);

                return response()->json([
                    'message' => 'Inicio de sesión correcto (user)',
                    'token' => $token,
                    'user' => $user,
                ]);
            } catch (JWTException $e) {
                Log::error('Excepción al crear el token (user): ' . $e->getMessage());
                return response()->json(['error' => 'No se pudo crear el token'], 500);
            }
        }

        return response()->json(['error' => 'El correo electrónico no está registrado'], 400);
    }

    public function exportPdf(Request $request)
    {
        try {
            $path = storage_path('app/public/usuarios.pdf');

            // Traer todos los usuarios
            $usuarios = User::all();

            // Procesar imágenes a base64
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

            // Resumen por rol
            $resumenPorRol = $usuarios->groupBy('rol')->map(function ($u) {
                return [
                    'total' => $u->count(),
                    'activos' => $u->where('estado', 1)->count(),
                    'inactivos' => $u->where('estado', 0)->count(),
                ];
            });

            // === NUEVO: obtener número de reporte ===
            $ultimoNumero = DB::table('activity_logs')
                ->where('activity_type', 'Exportación de reporte de usuarios')
                ->max('report_number');

            $nuevoNumero = $ultimoNumero ? $ultimoNumero + 1 : 1;

            // Pasar número a la vista
            $html = view('exports.users-pdf', compact('usuarios', 'resumenPorRol', 'nuevoNumero'))->render();

            // Crear PDF
            Browsershot::html($html)
                ->showBackground()
                ->margins(20, 10, 20, 10)
                ->format('A4')
                ->save($path);

            // === Guardar auditoría ===
            DB::table('activity_logs')->insert([
                'user_id' => $request->user_id ?? null,
                'user_email' => $request->user_email ?? null,

                'activity_type' => 'Exportación de reporte de usuarios',
                'description'   => "El usuario exportó el reporte de usuarios Nº $nuevoNumero",
                'old_values'    => null,
                'new_values'    => null,
                'ip_address'    => $request->ip(),
                'status'        => true,
                'report_number' => $nuevoNumero,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            return response()->download($path);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error generando PDF',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function perfil($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        return response()->json($user);
    }
}
