<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Rule extends Model {
    protected $guarded = [];
    public function gejala() { return $this->belongsTo(Gejala::class); }
    public function penyakit() { return $this->belongsTo(Penyakit::class); }
}