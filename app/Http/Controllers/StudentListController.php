<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentListController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

     public function __construct()
     {
         $this->middleware('auth');
     }

    public function index()
    {
        return view('studentsList');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
     $classroom = \App\ClassModel::find($id);

     $students = \App\Student::where('class_id', $classroom->id)->get();


      // $subjects = \App\Subject::where('user_id', $teacher->id)->get();
      return view('studentsList', compact('students', 'id'));
    }

    public function editCustom($id, $sub_id)
    {
     $classroom = \App\ClassModel::find($id);

     $students = \App\Student::where('class_id', $classroom->id)->get();


      // $subjects = \App\Subject::where('user_id', $teacher->id)->get();
      return view('studentsList', compact('students', 'id', 'sub_id'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $values = $request->input('students');
        $valuesSub = $request->input('subjects');
        $dataStudent = array();
        foreach ($values as $studKey => $studValue) {
          array_push($dataStudent, (int)$studValue);
        }

        for ($i=0; $i<count($dataStudent); $i++) {
          DB::table('students_subjects')
              ->where('id', '[0-9]+')
              ->updateOrInsert([
                'stud_id' => $dataStudent[$i],
                'sub_id' => (int)$valuesSub,
                'absence_hours' => 3
              ]);
        }
        return redirect('/home');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}