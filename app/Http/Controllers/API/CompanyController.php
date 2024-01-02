<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);


        $companiesQuery = Company::with(['users'])->whereHas('users', function ($query) {
            $query->where('user_id', Auth::id());
        });
        if ($id) {
            $company = $companiesQuery->find($id);

            if ($company) {
                return ResponseFormatter::success($company, 'Company Found');
            }
            return ResponseFormatter::error('Company Not Found', 404);
        }

        //mencari data di model company di function users yang user_id nya sama dengan auth id pada yang login
        $companies = $companiesQuery;

        if ($name) {
            $companies->where('name', 'like', '%' . $name . '%');
        }

        return ResponseFormatter::success(
            $companies->paginate($limit),
            'Companies found'
        );
    }

    public function create(CreateCompanyRequest $request)
    {
        try {

            //upload logo = jika logo ada maka disimpan di public/logos
            if ($request->hasFile('logo')) {
                $path = $request->file('logo')->store('public/logos');
            }
            //membuat data company
            $company = Company::create([
                'name' => $request->name,
                'logo' => $path
            ]);

            //jika company tidak ada maka return error
            if (!$company) {
                throw new Exception('Company not created');
            }

            //menambahkan company ke user ke table company_user
            $user = User::find(Auth::id());
            $user->companies()->attach($company->id);

            //untuk menambahkan users ke return responseformatter
            $company->load('users');
            return ResponseFormatter::success($company, 'Company Created');
        } catch (Exception $e) {
            //kalau try error atau tidak ada maka masuk ke sini
            return ResponseFormatter::error('Company Not Created', 404);
        }
    }
    public function update(UpdateCompanyRequest $request, $id)
    {
        try {

            $company = Company::find($id);

            //jika company tidak ada maka return error
            if (!$company) {
                throw new Exception('Company not found');
            }
            //upload logo = jika logo ada maka disimpan di public/logos
            if ($request->hasFile('logo')) {
                $path = $request->file('logo')->store('public/logos');
            }
            //update data company
            $company->update([
                'name' => $request->name,
                'logo' => $path
            ]);

            return ResponseFormatter::success($company, 'Company Updated');
        } catch (Exception $e) {
            //kalau try error atau tidak ada maka masuk ke sini
            return ResponseFormatter::error('Company Not Updated', 404);
        }
    }
}
