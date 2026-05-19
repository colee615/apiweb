<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ContactController extends Controller
{
    public function send(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:160'],
            'subject' => ['nullable', 'string', 'max:200'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $recipient = trim((string) env('CONTACT_FORM_TO', 'atencionalcliente@correos.gob.bo'));
        $subject = trim((string) ($data['subject'] ?? ''));

        try {
            Mail::raw($this->buildBody($data), function ($mail) use ($recipient, $data, $subject) {
                $mail->to($recipient)
                    ->replyTo($data['email'], $data['name'])
                    ->subject($subject !== '' ? $subject : 'Nuevo mensaje de contacto desde la web');
            });
        } catch (Throwable $exception) {
            Log::error('No se pudo enviar el correo del formulario de contacto.', [
                'recipient' => $recipient,
                'sender_email' => $data['email'],
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'No se pudo enviar el mensaje en este momento. Verifica la configuracion del correo del servidor.',
            ], 502);
        }

        return response()->json([
            'message' => 'Tu mensaje fue enviado correctamente.',
        ]);
    }

    protected function buildBody(array $data): string
    {
        return implode("\n", [
            'Nuevo mensaje enviado desde el formulario de contacto.',
            '',
            'Nombre: ' . $data['name'],
            'Correo: ' . $data['email'],
            'Asunto: ' . (($data['subject'] ?? '') !== '' ? $data['subject'] : 'Sin asunto'),
            '',
            'Mensaje:',
            $data['message'],
        ]);
    }
}
