<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DiagnosaHistory extends Model {
    protected $guarded = [];
    protected $casts = ['hasil_diagnosa' => 'array'];
}