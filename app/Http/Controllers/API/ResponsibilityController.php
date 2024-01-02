<?php

namespace App\Http\Controllers\API;

use App\Models\Responsibility;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateResponsibilityRequest;
use Exception;

class ResponsibilityController extends Controller
{
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);

        //mencari data responsibility berdasarkan id
        $responsibilitiesQuery = Responsibility::query();
        if ($id) {
            $responsibility = $responsibilitiesQuery->find($id);

            if ($responsibility) {
                return ResponseFormatter::success($responsibility, 'Responsibility Found');
            }
            return ResponseFormatter::error('Responsibility Not Found', 404);
        }

        //mencari data di model Responsibility mencari role_id yang value di dapat dari request
        $responsibilities = $responsibilitiesQuery->where('role_id', $request->role_id);

        if ($name) {
            $responsibilities->where('name', 'like', '%' . $name . '%');
        }

        return ResponseFormatter::success(
            $responsibilities->paginate($limit),
            'Responsibilities found'
        );
    }
    public function create(CreateResponsibilityRequest $request)
    {
        try {
            //membuat $responsibility
            $responsibility = Responsibility::create([
                'name' => $request->name,
                'role_id' => $request->role_id,
            ]);

            if (!$responsibility) {
                throw new Exception('Responsibility Not Created');
            }
            return ResponseFormatter::success($responsibility, 'Responsibility Created');
        } catch (Exception $e) {
            return ResponseFormatter::error('Responsibility Not Created', 404);
        }
    }

    public function destroy($id)
    {
        try {
            //mengambil responsibility berdasarkan id
            $responsibility = Responsibility::find($id);

            //TODO : Check if responsibility is owned by user
            if (!$responsibility) {
                throw new Exception('Responsibility Not Found');
            }
            //jika ada maka hapus
            $responsibility->delete();
            return ResponseFormatter::success($responsibility, 'Responsibility Deleted');
        } catch (Exception $e) {
            return ResponseFormatter::error('Responsibility Not Deleted', 404);
        }
    }
}
