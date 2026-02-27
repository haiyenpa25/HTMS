<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');

        $users = User::with('roles')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString()
            ->through(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->roles->first()?->name ?? 'Guest',
                    'created_at' => $user->created_at->format('d/m/Y'),
                ];
            });

        $roles = Role::pluck('name');

        return Inertia::render('Users/Index', [
            'users' => $users,
            'roles' => $roles,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'nullable|string|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        if (!empty($validated['role'])) {
            $user->assignRole($validated['role']);
        }

        return redirect()->back()->with('message', 'Đã tạo tài khoản thành công.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
         $validated = $request->validate([
             'name' => 'required|string|max:255',
             'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
             'password' => 'nullable|string|min:8',
             'role' => 'nullable|string|exists:roles,name',
         ]);
 
         $user->name = $validated['name'];
         $user->email = $validated['email'];
         
         if (!empty($validated['password'])) {
             $user->password = Hash::make($validated['password']);
         }
         
         $user->save();
 
         // Update Role
         if (!empty($validated['role'])) {
             $user->syncRoles([$validated['role']]);
         } else {
             // If no role provided, we can choose to detach or leave as is.
             // We'll sync empty array if they clear the role
             $user->syncRoles([]);
         }
 
         return redirect()->back()->with('message', 'Cập nhật tài khoản thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Bạn không thể tự xóa tài khoản của chính mình.');
        }

        $user->delete();

        return redirect()->back()->with('message', 'Xóa tài khoản thành công.');
    }
}
