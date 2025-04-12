<?php
namespace App\Http\Controllers;

use App\Models\chercheuremploi;
use App\Models\Entreprise;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
class AuthController extends Controller{
public function register(Request $request)
{
    $request->validate([
        'nom' => 'required',
        'prenom' => 'required',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8',
        'role' => 'required|in:chercheur,entreprise' // Add role management
    ]);

    $user = User::create([
        'nom' => $request->nom,
        'prenom' => $request->prenom,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => $request->role
    ]);

    // Create associated profile
    if($request->role === 'entreprise') {
        Entreprise::create(['user_id' => $user->id]);
    }

    return response()->json(['token' => $user->createToken('API_TOKEN')->plainTextToken]);
}
}
?>