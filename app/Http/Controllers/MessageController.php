<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    public function index()
    {
        $messagesRecus = User::messagesReceived()
            ->with('expediteur')
            ->latest('date_envoi')
            ->paginate(15);

        return view('messages.index', compact('messagesRecus'));
    }

    public function sent()
    {
        $messagesEnvoyes = User::messagesSent()
            ->with('destinataire')
            ->latest('date_envoi')
            ->paginate(15);

        return view('messages.sent', compact('messagesEnvoyes'));
    }

    public function create(Request $request)
    {
        $destinataire = null;

        // If replying to a message or sending to a specific user
        if ($request->has('reply_to')) {
            $originalMessage = Message::findOrFail($request->reply_to);
            $destinataire = $originalMessage->expediteur;

            // Check if user is the recipient of the original message
            if ($originalMessage->destinataire_id !== Auth::id()) {
                return redirect()->route('messages.index')->with('error', 'Accès non autorisé');
            }

        } elseif ($request->has('user_id')) {
            $destinataire = User::findOrFail($request->user_id);
        }

        return view('messages.create', compact('destinataire'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'destinataire_id' => 'required|exists:utilisateurs,id',
            'sujet' => 'required|string|max:255',
            'contenu' => 'required|string',
        ]);

        // Don't allow sending messages to oneself
        if ((int)$request->destinataire_id === Auth::id()) {
            return redirect()->back()->with('error', 'Vous ne pouvez pas vous envoyer un message');
        }

        $message = Message::create([
            'sujet' => $request->sujet,
            'contenu' => $request->contenu,
            'expediteur_id' => Auth::id(),
            'destinataire_id' => $request->destinataire_id,
            'date_envoi' => now(),
            'lu' => false,
        ]);

        return redirect()->route('messages.sent')->with('success', 'Message envoyé avec succès');
    }

    public function show(Message $message)
    {
        // Check if user is the sender or recipient
        if ($message->expediteur_id !== Auth::id() && $message->destinataire_id !== Auth::id()) {
            return redirect()->route('messages.index')->with('error', 'Accès non autorisé');
        }

        // Mark as read if user is the recipient
        if ($message->destinataire_id === Auth::id() && !$message->lu) {
            $message->update(['lu' => true]);
        }

        return view('messages.show', compact('message'));
    }

    public function destroy(Message $message)
    {
        // Check if user is the sender or recipient
        if ($message->expediteur_id !== Auth::id() && $message->destinataire_id !== Auth::id()) {
            return redirect()->route('messages.index')->with('error', 'Accès non autorisé');
        }

        $message->delete();

        return redirect()->route('messages.index')->with('success', 'Message supprimé avec succès');
    }

    public function markAsRead(Message $message)
    {
        // Check if user is the recipient
        if ($message->destinataire_id !== Auth::id()) {
            return redirect()->route('messages.index')->with('error', 'Accès non autorisé');
        }

        $message->update(['lu' => true]);

        return redirect()->back()->with('success', 'Message marqué comme lu');
    }

    public function markAllAsRead()
    {
        User::messagesReceived()->where('lu', false)->update(['lu' => true]);

        return redirect()->route('messages.index')->with('success', 'Tous les messages ont été marqués comme lus');
    }
}
