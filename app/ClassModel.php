<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
  protected $table = 'classes';
  public function students()
  {
      return $this->hasMany('App\Student');
  }

  public function school()
  {
      return $this->hasOne('App\School');
  }

  public function subjects()
  {
      return $this->hasMany('App\Subject');
  }

}
