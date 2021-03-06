<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Argument;
use App\Test;
use Carbon\Carbon;

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



    $this->validate($request, [
      'question' => 'required'
    ]);
    //dd($request->input('question'));
    $question = $request->input('question');
    $dataQuestion = array();
    foreach ($question as $questionKey => $questionValue) {
      array_push($dataQuestion, $questionValue);
    }
    for ($i=0; $i < count($dataQuestion); $i++) {
      $questions = new Test([
        'topic_id' => $request->input('topicID'),
        'questions' => $dataQuestion[$i]

        ]);

        $id = $request->input('topicID');
      $questions->save();
    }


    return view('studentsList', compact('id'));
  }

  /**
  * Display the specified resource.
  *
  * @param  int  $id
  * @return \Illuminate\Http\Response
  */
  public function show($id)
  {

  }

  function getAllMonths(){
		$month_array = array();
		$posts_dates = Argument::all();
		$posts_dates = json_decode( $posts_dates );

  if (! empty($posts_dates)) {
    foreach ($posts_dates as $topicTime) {
      $month_name = Carbon::parse($topicTime->created_at)->format('M');
          array_push($month_array, $month_name);
      }
      return $month_array;
    }

	}
	function getMonthlyPostCount( $month ) {
		$monthly_post_count = Argument::whereMonth( 'created_at', $month )->get()->count();
		return $monthly_post_count;
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
    return view('studentsList', compact('students', 'id'));
  }

  public function editCustom($id, $sub_id)
  {
    $classroom = \App\ClassModel::find($id);
    $students = \App\Student::where('class_id', $classroom->id)->get();
    $classMedia = DB::table('students_marks')->where('sub_id', $sub_id)->get();
    $present = array();
    $classMark = 0;
    $total = 0;
    foreach ($classMedia as $classes) {
      $classMark += $classes->mark;
    }


    if (count($classMedia)>0) {
      $total = $classMark/count($classMedia);
    }
    else {
      $total = 0;
    }
    if ($total > 0) {
      //dd('soo');
      foreach ($students as $pres) {
        array_push($present, DB::table('students_marks')->where('stud_id', $pres->id)->exists());
      }

      if (!in_array(false, $present)) {
      if (DB::table('class_avereage')->where('sub_id', $sub_id)->exists()==false) {
      DB::table('class_avereage')->where('id', '[0-9]+')->updateOrInsert([
        'sub_id' => $sub_id,
        'avereage' => $total,
        'date' => Carbon::now()->format('Y-m-d')
      ]);
    }
    else {
      $current = DB::table('class_avereage')->where('sub_id', $sub_id)->get();
      foreach ($current as $detail) {
        $month = Carbon::parse($detail->date)->format('m');
        //dd($month);
        if (Carbon::now()->format('m') != $month) {
          DB::table('class_avereage')->where('id', '[0-9]+')->updateOrInsert([
            'sub_id' => $sub_id,
            'avereage' => $total,
            'date' => Carbon::now()->format('Y-m-d')
          ]);
          // dd($total);
        }
      }
    }
    }

    }
    $timestamp = Carbon::now()->format('Y-m-d');

    $monthly_avg_count_array = array();
		$month_array = $this->getAllMonths();
		$month_name_array = array();
		if ( ! empty( $month_array ) ) {
			foreach ( $month_array as $month_no => $month_name ){
				// $monthly_post_count = $this->getMonthlyPostCount( $month_no );
				array_push( $monthly_avg_count_array, $total );
				array_push( $month_name_array, $month_name );
			}

		}

		$monthly_post_data_array = array(
			'months' => $month_name_array,
      'avgData' => $monthly_avg_count_array,

		);
    $pizza = implode(",", $month_name_array);
    $thisMonth = Carbon::now()->format('F Y');

    return view('studentsList', compact('students', 'id', 'sub_id', 'pizza', 'total', 'monthly_avg_count_array', 'thisMonth'));
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
    if (!is_null($values)) {
    foreach ($values as $studKey => $studValue) {
      array_push($dataStudent, (int)$studValue);

    }
  }
  else {
    DB::table('students_subjects')
    ->where('sub_id', (int)$valuesSub)
    ->increment('absence_hours', 200/(float)$subSelect->totHours);
  }
    $currentSubject = DB::table('students_subjects')->where('sub_id', (int)$valuesSub)->exists();
    if (count($dataStudent) > 0) {
    for ($i=0; $i<count($dataStudent); $i++) {
      $currentStudent = DB::table('students_subjects')->where('stud_id', $dataStudent[$i])->exists();
      if ($currentStudent == false) {
        DB::table('students_subjects')
        ->where('id', '[0-9]+')
        ->updateOrInsert([
          'stud_id' => $dataStudent[$i],
          'sub_id' => (int)$valuesSub,
          'absence_hours' => 300/$subSelect->totHours,
          'date' => Carbon::now()->format('Y-m-d')
        ]);
      }
      elseif ($currentStudent == true && $currentSubject == false) {
        DB::table('students_subjects')
        ->where('id', '[0-9]+')
        ->updateOrInsert([
          'stud_id' => $dataStudent[$i],
          'sub_id' => (int)$valuesSub,
          'absence_hours' => 300/$subSelect->totHours,
          'date' => Carbon::now()->format('Y-m-d')
        ]);
      }
      elseif ($currentStudent == true && $currentSubject == true) {
        DB::table('students_subjects')
        ->where('stud_id', $dataStudent[$i])
        ->where('sub_id', (int)$valuesSub)
        ->increment('absence_hours', 200/(float)$subSelect->totHours);
      }
    }
  }


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
    $this->validate($request, [
      'dayArgument' => 'required|max:100'
    ]);

    $arguments = new Argument([
      'topic' => $request->get('dayArgument'),
      'sub_id' => $request->get('subjects')
    ]);
    $arguments->save();

    // $argumentData = Argument::where('sub_id', $request->get('subjects'))->get();
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
