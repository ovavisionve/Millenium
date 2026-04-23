<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $q = User::query()->orderBy('name');

        if ($request->filled('buscar')) {
            $s = $request->string('buscar');
            $q->where(function ($query) use ($s) {
                $query->where('name', 'like', '%'.$s.'%')
                    ->orWhere('email', 'like', '%'.$s.'%');
            });
        }

        return view('admin.users.index', [
            'users' => $q->paginate(15)->withQueryString(),
            'roleLabels' => User::roleLabels(),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'roleLabels' => User::roleLabels(),
            'roles' => User::$roles,
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $data['email_verified_at'] = now();

        User::create($data);

        return redirect()->route('usuarios.index')
            ->with('status', 'Usuario creado correctamente.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user,
            'roleLabels' => User::roleLabels(),
            'roles' => User::$roles,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $willBeActive = $request->boolean('is_active');
        $newRole = $data['role'];
        $wasOnlyActiveAdmin = $user->role === 'admin' && $user->is_active
            && ! User::where('role', 'admin')
                ->where('is_active', true)
                ->where('id', '!=', $user->id)
                ->exists();

        if ($wasOnlyActiveAdmin && ($newRole !== 'admin' || ! $willBeActive)) {
            return back()->withErrors([
                'role' => 'Debe existir al menos otro administrador activo antes de quitar el rol o desactivar este usuario.',
            ])->withInput();
        }

        $user->update($data);

        return redirect()->route('usuarios.index')
            ->with('status', 'Usuario actualizado.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'No puedes eliminar tu propio usuario.']);
        }

        if ($user->role === 'admin' && $user->is_active) {
            $other = User::where('role', 'admin')
                ->where('is_active', true)
                ->where('id', '!=', $user->id)
                ->exists();
            if (! $other) {
                return back()->withErrors(['error' => 'No puedes eliminar el único administrador activo.']);
            }
        }

        $user->delete();

        return redirect()->route('usuarios.index')
            ->with('status', 'Usuario eliminado.');
    }
}
