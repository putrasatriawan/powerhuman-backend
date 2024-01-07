<?php

namespace App\Http\Controllers\API;

use App\Models\Role;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Exception;

class RoleController extends Controller
{
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);
        $with_responsibility = $request->input('with_responsibility', false);


        //mencari data role berdasarkan id
        $rolesQuery = Role::withCount('employees');
        if ($id) {
            $role = $rolesQuery->with('responsibilities')->find($id);

            if ($role) {
                return ResponseFormatter::success($role, 'Role Found');
            }
            return ResponseFormatter::error('Role Not Found', 404);
        }

        //mencari data di model Role mencari company_id yang value di dapat dari request
        $roles = $rolesQuery->where('company_id', $request->company_id);

        if ($name) {
            $roles->where('name', 'like', '%' . $name . '%');
        }

        if ($with_responsibility) {
            $roles->with('responsibilities');
        }

        return ResponseFormatter::success(
            $roles->paginate($limit),
            'Roles found'
        );
    }
    public function create(CreateRoleRequest $request)
    {
        try {
            //membuat $role
            $role = Role::create([
                'name' => $request->name,
                'company_id' => $request->company_id,
            ]);

            if (!$role) {
                throw new Exception('Role Not Created');
            }
            return ResponseFormatter::success($role, 'Role Created');
        } catch (Exception $e) {
            return ResponseFormatter::error('Role Not Created', 404);
        }
    }
    public function update(UpdateRoleRequest $request, $id)
    {
        try {

            $role = Role::find($id);

            //jika role tidak ada maka return error
            if (!$role) {
                throw new Exception('Role not found');
            }
            //update data role
            $role->update([
                'name' => $request->name,
                'company_id' => $request->company_id,
            ]);

            return ResponseFormatter::success($role, 'Role Updated');
        } catch (Exception $e) {
            //kalau try error atau tidak ada maka masuk ke sini
            return ResponseFormatter::error('Role Not Updated', 404);
        }
    }
    public function destroy($id)
    {
        try {
            //mengambil role berdasarkan id
            $role = Role::find($id);

            //TODO : Check if role is owned by user
            if (!$role) {
                throw new Exception('Role Not Found');
            }
            //jika ada maka hapus
            $role->delete();
            return ResponseFormatter::success($role, 'Role Deleted');
        } catch (Exception $e) {
            return ResponseFormatter::error('Role Not Deleted', 404);
        }
    }
}
