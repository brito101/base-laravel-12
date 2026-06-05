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

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|Factory|Application|JsonResponse|\Illuminate\Contracts\Foundation\Application
    {
        CheckPermission::checkAuth('Listar Permissões');

        if ($request->ajax()) {
            $permissions = Permission::all();
            $token = csrf_token();

            return DataTables::of($permissions)
                ->addIndexColumn()
                ->addColumn('action', function ($row) use ($token) {
                    return '<a class="btn btn-xs btn-primary mx-1 shadow" title="Editar" href="permission/'.$row->id.'/edit"><i class="fa fa-lg fa-fw fa-pen"></i></a>'.'<form method="POST" action="permission/'.$row->id.'" class="btn btn-xs px-0"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="'.$token.'"><button class="btn btn-xs btn-danger mx-1 shadow" title="Excluir" onclick="return confirm(\'Confirma a exclusão desta permissão?\')"><i class="fa fa-lg fa-fw fa-trash"></i></button></form>';
                })
                ->rawColumns(['action'])
                ->make();
        }

        return view('admin.acl.permissions.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View|Factory|Application|\Illuminate\Contracts\Foundation\Application
    {
        CheckPermission::checkAuth('Criar Permissões');

        return view('admin.acl.permissions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        CheckPermission::checkAuth('Criar Permissões');

        $check = Permission::where('name', $request->name)->first();
        if ($check) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Nome da permissão já está em uso!');
        }

        $permission = Permission::create($request->all());

        if ($permission->save()) {
            return redirect()
                ->route('admin.permission.index')
                ->with('success', 'Permissão cadastrada!');
        } else {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Falha ao cadastrar a permissão!');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View|Factory|Application|\Illuminate\Contracts\Foundation\Application
    {
        CheckPermission::checkAuth('Editar Permissões');

        $permission = Permission::find($id);
        if (! $permission) {
            abort(403, 'Acesso não autorizado');
        }

        return view('admin.acl.permissions.edit', compact('permission'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        CheckPermission::checkAuth('Editar Permissões');

        $check = Permission::where('name', $request->name)->where('id', '!=', $id)->first();
        if ($check) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'O nome desta permissão já está em uso!');
        }

        $permission = Permission::find($id);
        if (! $permission) {
            abort(403, 'Acesso não autorizado');
        }

        if ($permission->update($request->all())) {
            return redirect()
                ->route('admin.permission.index')
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
        CheckPermission::checkAuth('Excluir Permissões');

        $permission = Permission::find($id);
        if (! $permission) {
            abort(403, 'Acesso não autorizado');
        }

        if ($permission->delete()) {
            return redirect()
                ->route('admin.permission.index')
                ->with('success', 'Exclusão realizada!');
        } else {
            return redirect()
                ->back()
                ->with('error', 'Erro ao excluir!');
        }
    }
}
