<?php
// app/Models/Rating.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Rating extends Model {
    protected $fillable = ['report_id','user_id','score'];
    public function report(){ return $this->belongsTo(Report::class); }
    public function user(){ return $this->belongsTo(User::class); }
}