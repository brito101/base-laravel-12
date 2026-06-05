<?php

namespace App\Http\Controllers\Admin\ACL;

use App\Helpers\CheckPermission;
use App\Http\Controllers\Controller;
use DataTables;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Application|View|Factory|JsonResponse|\Illuminate\Contracts\Foundation\Application
    {
        CheckPermission::checkAuth('Listar Perfis');

        if ($request->ajax()) {
            $roles = Role::all(['id', 'name']);
            $token = csrf_token();

            return DataTables::of($roles)
                ->addIndexColumn()
                ->addColumn('action', function ($row) use ($token) {
                    return '<a class="btn btn-xs btn-primary mx-1 shadow" title="Editar" href="role/'.$row->id.'/edit"><i class="fa fa-lg fa-fw fa-pen"></i></a>'.'<a class="btn btn-xs btn-secondary mx-1 shadow" title="Sincronizar" href="role/'.$row->id.'/permission"><i class="fa fa-lg fa-fw fa-sync"></i></a>'.'<form method="POST" action="role/'.$row->id.'" class="btn btn-xs px-0"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="'.$token.'"><button class="btn btn-xs btn-danger mx-1 shadow" title="Excluir" onclick="return confirm(\'Confirma a exclusão deste perfil?\')"><i class="fa fa-lg fa-fw fa-trash"></i></button></form>';
                })
                ->rawColumns(['action'])
                ->make();
        }

        return view('admin.acl.roles.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View|Factory|Application|\Illuminate\Contracts\Foundation\Application
    {
        CheckPermission::checkAuth('Criar Perfis');

        return view('admin.acl.roles.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        CheckPermission::checkAuth('Criar Perfis');

        $check = Role::where('name', $request->name)->first();
        if ($check) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Nome do perfil já está em uso!');
        }

        $role = Role::create($request->all());

        if ($role->save()) {
            return redirect()
                ->route('admin.role.index')
                ->with('success', 'Perfil cadastrado!');
        } else {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Falha ao cadastrar perfil!');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View|Factory|Application|\Illuminate\Contracts\Foundation\Application
    {
        CheckPermission::checkAuth('Editar Perfis');

        $role = Role::find($id);
        if (! $role) {
            abort(403, 'Acesso não autorizado');
        }

        return view('admin.acl.roles.edit', compact('role'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        CheckPermission::checkAuth('Editar Perfis');

        $role = Role::find($id);
        if (! $role) {
            abort(403, 'Acesso não autorizado');
        }

        $check = Role::where('name', $request->name)->where('id', '!=', $id)->first();
        if ($check) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'O nome deste perfil já está em uso!');
        }

        if ($role->update($request->all())) {
            return redirect()
                ->route('admin.role.index')
                ->with('success', 'Atualização realizada!');
        } else {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar!');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        CheckPermission::checkAuth('Excluir Perfis');

        $role = Role::find($id);
        if (! $role) {
            abort(403, 'Acesso não autorizado');
        }

        if ($role->delete()) {
            return redirect()
                ->route('admin.role.index')
                ->with('success', 'Exclusão realizada!');
        } else {
            return redirect()
                ->back()
                ->with('error', 'Erro ao excluir!');
        }
    }

    public function permissions($id): View|Application|Factory|\Illuminate\Contracts\Foundation\Application
    {
        CheckPermission::checkAuth('Sincronizar Perfis');

        $role = Role::find($id);

        if (! $role) {
            abort(403, 'Acesso não autorizado');
        }

        $permissions = Permission::all();

        foreach ($permissions as $permission) {
            if ($role->hasPermissionTo($permission->name)) {
                $permission->can = true;
            } else {
                $permission->can = false;
            }
        }

        return view('admin.acl.roles.permissions', compact('role', 'permissions'));
    }

    public function permissionsSync(Request $request, int $id): RedirectResponse
    {

        CheckPermission::checkAuth('Sincronizar Perfis');

        $permissionsRequest = $request->except(['_token', '_method']);
        foreach ($permissionsRequest as $key => $value) {
            $permissions[] = Permission::where('id', $key)->first();
        }

        $role = Role::find($id);
        if (! $role) {
            abort(403, 'Acesso não autorizado');
        }

        if (! empty($permissions)) {
            $role->syncPermissions($permissions);
        } else {
            $role->syncPermissions(null);
        }

        return redirect()
            ->route('admin.role.permissions', ['role' => $role->id])
            ->with('success', 'Permissão sincronizada');
    }
}
