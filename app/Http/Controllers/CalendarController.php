<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CalendarController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
      $this->middleware('auth');
  }

  /**
   * Show the application dashboard.
   *
   * @return \Illuminate\Contracts\Support\Renderable
   */
  public function index()
  {
    if (isset($_GET['ym'])) {
        $ym = $_GET['ym'];
    } else {
        // This month
        $ym = date('Y-m');
    }
    // Check format
    $timestamp = strtotime($ym . '-01');  // the first day of the month
    if ($timestamp === false) {
        $ym = date('Y-m');
        $timestamp = strtotime($ym . '-01');
    }
    // Today (Format:2018-08-8)
    $today = date('Y-m-j');
    // Title (Format:August, 2018)
    $title = date('F, Y', $timestamp);
    // Create prev & next month link
    $prev = date('Y-m', strtotime('-1 month', $timestamp));
    $next = date('Y-m', strtotime('+1 month', $timestamp));
    // Number of days in the month
    $day_count = date('t', $timestamp);
    // 1:Mon 2:Tue 3: Wed ... 7:Sun
    $str = date('N', $timestamp);
    // Array for calendar
    $weeks = [];
    $week = '';
    // Add empty cell(s)
    $week .= str_repeat('<td></td>', $str - 1);
    for ($day = 1; $day <= $day_count; $day++, $str++) {
        $date = $ym . '-' . $day;
        if ($today == $date) {
            $week .= '<td class="calendarToday">';
        } else {
            $week .= '<td>';
        }
        $week .= $day . '</td>';
        // Sunday OR last day of the month
        if ($str % 7 == 0 || $day == $day_count) {
            // last day of the month
            if ($day == $day_count && $str % 7 != 0) {
                // Add empty cell(s)
                $week .= str_repeat('<td class="calendarAll"></td>', 7 - $str % 7);
            }
            $weeks[] = '<tr>' . $week . '</tr>';
            $week = '';
        }
    }
      return view('calendar')->with('weeks', $weeks)->with('title', $title)->with('prev', $prev)->with('next', $next);
  }
}
