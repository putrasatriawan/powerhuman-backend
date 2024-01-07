<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Team;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CreateTeamRequest;
use App\Http\Requests\UpdateTeamRequest;

class TeamController extends Controller
{
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);

        //mencari data team berdasarkan id
        $teamsQuery = Team::withCount('employees');
        if ($id) {
            $team = $teamsQuery->find($id);

            if ($team) {
                return ResponseFormatter::success($team, 'Team Found');
            }
            return ResponseFormatter::error('Team Not Found', 404);
        }

        //mencari data di model Team mencari company_id yang value di dapat dari request
        $teams = $teamsQuery->where('company_id', $request->company_id);

        if ($name) {
            $teams->where('name', 'like', '%' . $name . '%');
        }

        return ResponseFormatter::success(
            $teams->paginate($limit),
            'Teams found'
        );
    }
    public function create(CreateTeamRequest $request)
    {
        try {
            //upload icon
            if ($request->hasFile('icon')) {
                $path = $request->file('icon')->store('public/icons');
            }

            //membuat $team
            $team = Team::create([
                'name' => $request->name,
                'icon' =>  isset($path) ? $path : '',
                'company_id' => $request->company_id,
            ]);

            if (!$team) {
                throw new Exception('Team Not Created');
            }
            return ResponseFormatter::success($team, 'Team Created');
        } catch (Exception $e) {
            return ResponseFormatter::error('Team Not Created', 404);
        }
    }
    public function update(UpdateTeamRequest $request, $id)
    {
        try {

            $team = Team::find($id);

            //jika team tidak ada maka return error
            if (!$team) {
                throw new Exception('Team not found');
            }
            //upload icon = jika icon ada maka disimpan di public/icons
            if ($request->hasFile('icon')) {
                $path = $request->file('icon')->store('public/icons');
            }
            //update data team
            $team->update([
                'name' => $request->name,
                'icon' => isset($path) ? $path : $team->icon,
                'company_id' => $request->company_id,
            ]);

            return ResponseFormatter::success($team, 'Team Updated');
        } catch (Exception $e) {
            //kalau try error atau tidak ada maka masuk ke sini
            return ResponseFormatter::error('Team Not Updated', 404);
        }
    }
    public function destroy($id)
    {
        try {
            //mengambil team berdasarkan id
            $team = Team::find($id);

            //TODO : Check if team is owned by user
            if (!$team) {
                throw new Exception('Team Not Found');
            }
            //jika ada maka hapus
            $team->delete();
            return ResponseFormatter::success($team, 'Team Deleted');
        } catch (Exception $e) {
            return ResponseFormatter::error('Team Not Deleted', 404);
        }
    }
}
