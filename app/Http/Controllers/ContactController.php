<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Mostrar o formulário de contato.
     */
    public function showContactForm()
    {
        return view('pages.contact');
    }

    /**
     * Enviar o email com o problema reportado.
     */
    public function sendContactEmail(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $username = $request->input('username');
        $subject = $request->input('subject');
        $message = $request->input('message');

        // Envia o email usando Mailtrap
        Mail::raw("User: $username\n\nSubject: $subject\n\nMessage:\n$message", function ($mail) use ($username, $subject) {
            $mail->to('support@connectify.com')
                ->from('noreply@connectify.com', 'Connectify Contact Form')
                ->subject("Contact Form: $subject");
        });

        return redirect()->route('contact')->with('status', 'Your message has been sent successfully!');
    }
}
