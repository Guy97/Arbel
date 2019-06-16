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
        $subSelect = \App\Subject::where('id', (int)$valuesSub)->first();
        $dataStudent = array();
        foreach ($values as $studKey => $studValue) {
          array_push($dataStudent, (int)$studValue);

        }
        $currentSubject = DB::table('students_subjects')->where('sub_id', (int)$valuesSub)->exists();
        for ($i=0; $i<count($dataStudent); $i++) {
          $currentStudent = DB::table('students_subjects')->where('stud_id', $dataStudent[$i])->exists();
          if ($currentStudent == false) {
            DB::table('students_subjects')
                ->where('id', '[0-9]+')
                ->updateOrInsert([
                  'stud_id' => $dataStudent[$i],
                  'sub_id' => (int)$valuesSub,
                  'absence_hours' => 300/$subSelect->totHours
                ]);
          }
          elseif ($currentStudent == true && $currentSubject == false) {
            DB::table('students_subjects')
                ->where('id', '[0-9]+')
                ->updateOrInsert([
                  'stud_id' => $dataStudent[$i],
                  'sub_id' => (int)$valuesSub,
                  'absence_hours' => 300/$subSelect->totHours
                ]);
          }
          elseif ($currentStudent == true && $currentSubject == true) {
            DB::table('students_subjects')
                ->where('stud_id', $dataStudent[$i])
                ->where('sub_id', (int)$valuesSub)
                ->increment('absence_hours', 200/(float)$subSelect->totHours);
          }
        }
        //controllo assenze

        $absPerc = $subSelect->totHours * (20/100);
        $subStudent = DB::table('students_subjects')
                          ->where('sub_id', (int)$valuesSub)->get();
        $studAbs = array();
        foreach ($subStudent as $student) {
          if ((float)$student->absence_hours > 20.0) {
            //dd("entratoooo");
            $studName = \App\Student::where('id', $student->stud_id)->first();
            array_push($studAbs, $studName->name);
          }
        }

         //dd($studAbs);
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
