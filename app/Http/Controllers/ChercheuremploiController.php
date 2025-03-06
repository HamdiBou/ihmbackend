<?php

namespace App\Http\Controllers;

use App\Models\chercheuremploi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChercheuremploiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'nom'=>'required',
            'prenom'=>'required',
            'civilité'=>'required',
            'email'=>'required|email|unique:users,email',
            'password'=>'required|min:8|same:confirm_password',
            'confirmpassword'=>'required',
            'datenaissance'=>'required|date|before:today',
            'gouvernorat'=>'required'
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(),400);
        }
        $chercheuremploi=chercheuremploi::create($request->all());
        return response()->json($chercheuremploi,201);
    }

    /**
     * Display the specified resource.
     */
    public function show(chercheuremploi $chercheuremploi)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, chercheuremploi $chercheuremploi)
    {
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(chercheuremploi $chercheuremploi)
    {
        //
    }
}
