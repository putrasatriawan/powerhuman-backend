<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CreateEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmpoloyeeController extends Controller
{
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $email = $request->input('email');
        $age = $request->input('age');
        $phone = $request->input('phone');
        $company_id = $request->input('company_id');
        $team_id = $request->input('team_id');
        $role_id = $request->input('role_id');
        $limit = $request->input('limit', 10);

        //mencari data employee berdasarkan id
        $employeesQuery = Employee::with('team', 'role');
        if ($id) {
            $employee = $employeesQuery->with(['team', 'role'])->find($id);

            if ($employee) {
                return ResponseFormatter::success($employee, 'Employee Found');
            }
            return ResponseFormatter::error('Employee Not Found', 404);
        }

        //mencari data di model Employee mencari company_id yang value di dapat dari request
        $employees = $employeesQuery;

        if ($name) {
            $employees->where('name', 'like', '%' . $name . '%');
        }

        if ($email) {
            $employees->where('email', $email);
        }

        if ($age) {
            $employees->where('email', $age);
        }

        if ($phone) {
            $employees->where('phone', 'like', '%' . $phone . '%');
        }

        if ($team_id) {
            $employees->where('team_id', $team_id);
        }

        if ($role_id) {
            $employees->where('role_id', $role_id);
        }

        if ($company_id) {
            $employees->whereHas('team', function ($query) use ($company_id) {
                $query->where('company_id', $company_id);
            });
        }

        return ResponseFormatter::success(
            $employees->paginate($limit),
            'Employees found'
        );
    }
    public function create(CreateEmployeeRequest $request)
    {
        try {
            //upload photo
            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('public/photos');
            }

            //membuat $employee
            $employee = Employee::create([
                'name' => $request->name,
                'email' => $request->email,
                'gender' => $request->gender,
                'age' => $request->age,
                'phone' => $request->phone,
                'team_id' => $request->team_id,
                'role_id' => $request->role_id,
            ]);

            if ($request->hasFile('photo')) {
                $image = $request->file('photo');

                $randomString = Str::random(5);
                $imageName = 'photo-' . time() . '-' . $randomString . '.' . $image->getClientOriginalExtension();

                $path = 'photos';
                if (!Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->makeDirectory($path);
                }


                Storage::disk('public')->putFileAs($path, $image, $imageName);

                $employee_id = $employee->id;

                Employee::where('id', $employee_id)->update(['photo' => isset($imageName) ? $imageName : ""]);
            }
            if (!$employee) {
                throw new Exception('Employee Not Created');
            }
            return ResponseFormatter::success($employee, 'Employee Created');
        } catch (Exception $e) {
            return ResponseFormatter::error('Employee Not Created', 404);
        }
    }
    public function update(UpdateEmployeeRequest $request, $id)
    {
        try {

            $employee = Employee::find($id);

            //jika employee tidak ada maka return error
            if (!$employee) {
                throw new Exception('Employee not found');
            }
            //upload photo = jika photo ada maka disimpan di public/photos
            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('public/photos');
            }
            //update data employee
            $employee->update([
                'name' => $request->name,
                'email' => $request->email,
                'gender' => $request->gender,
                'age' => $request->age,
                'phone' => $request->phone,
                'photo' => isset($path) ? $path : $employee->photo,
                'team_id' => $request->team_id,
                'role_id' => $request->role_id,
            ]);

            return ResponseFormatter::success($employee, 'Employee Updated');
        } catch (Exception $e) {
            //kalau try error atau tidak ada maka masuk ke sini
            return ResponseFormatter::error('Employee Not Updated', 404);
        }
    }
    public function destroy($id)
    {
        try {
            //mengambil employee berdasarkan id
            $employee = Employee::find($id);

            //TODO : Check if employee is owned by user
            if (!$employee) {
                throw new Exception('Employee Not Found');
            }
            //jika ada maka hapus
            $employee->delete();
            return ResponseFormatter::success($employee, 'Employee Deleted');
        } catch (Exception $e) {
            return ResponseFormatter::error('Employee Not Deleted', 404);
        }
    }
}
