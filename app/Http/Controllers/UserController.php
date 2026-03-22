<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Show the profile for a given user.
     */
    public function showProfile(string $id): View {
        return view('user.profile', ['user' => User::findOrFail($id)]);
    }

    public function index()
    {
        // $users = User::latest()->paginate(15);   // 15 par page, le plus récent en premier
        
        /* // Seulement certains champs + tri
        $users = User::select('id_user', 'name', 'email', 'role', 'created_at')
                    ->orderBy('created_at', 'desc')
                    ->paginate(20);

        // Avec relation (ex: compter les transactions par user)
        $users = User::withCount('transactions')->paginate(15);

        // Seulement les User
        $users = User::where('role', 'User')->paginate(25); */
        
        $users = User::all(); // ← ou ::latest()->get() ou paginate(15)
        // Option 1 - la plus claire et moderne
        return view('usersList', compact('users'));

        // Option 2 - très lisible aussi
        // return view('userList', ['users' => $users]);

        // Option 3 - style "with"
        // return view('userList')->with('users', $users);
    }

}