<?php
/* ============================================================
   FILE: app/Models/Gejala.php
   ============================================================ */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gejala extends Model
{
    use HasFactory;

    protected $table    = 'gejala';
    protected $fillable = ['kode', 'nama_gejala', 'deskripsi', 'judul', 'slug', 'kategori', 'ringkasan','konten', 'foto'];

    /*
     * A symptom can appear in many knowledge base rules.
     */
    public function penyakits(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Penyakit::class, 'basis_pengetahuan', 'gejala_id', 'penyakit_id')
                    ->withPivot(['mb', 'md'])
                    ->withTimestamps();
    }
}

/* ============================================================
   FILE: app/Models/Penyakit.php
   ============================================================ */

// namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;

class Penyakit extends Model
{
    use HasFactory;

    protected $table    = 'penyakit';
    protected $fillable = [
        'kode',
        'nama_penyakit',
        'deskripsi',
        'penanganan',
        'pencegahan',
        'gambar',
    ];

    /**
     * A disease is linked to many symptoms via the knowledge base.
     */
    public function gejalas(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Gejala::class, 'basis_pengetahuan', 'penyakit_id', 'gejala_id')
                    ->withPivot(['mb', 'md'])
                    ->withTimestamps();
    }
}

/* ============================================================
   FILE: app/Models/Artikel.php
   ============================================================ */

// namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;

class Artikel extends Model
{
    use HasFactory;

    protected $table    = 'artikel';
    protected $fillable = [
        'judul',
        'slug',
        'ringkasan',
        'konten',
        'gambar',
        'kategori',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    /**
     * Scope to only return published articles.
     */
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')
                     ->where('published_at', '<=', now());
    }
}