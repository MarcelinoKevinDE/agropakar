<?php
// =============================================================================
// FILE: app/Models/Plant.php
// =============================================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Plant extends Model
{
    use HasFactory;

    protected $fillable = ['kode', 'nama_tanaman', 'deskripsi', 'gambar', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function symptomCategories(): HasMany
    {
        return $this->hasMany(SymptomCategory::class, 'plant_id')
            ->orderBy('urutan');
    }

    public function diseases(): HasMany
    {
        return $this->hasMany(Disease::class, 'plant_id');
    }

    public function diagnosisSessions(): HasMany
    {
        return $this->hasMany(DiagnosisSession::class, 'plant_id');
    }
}

// =============================================================================
// FILE: app/Models/SymptomCategory.php
// =============================================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SymptomCategory extends Model
{
    use HasFactory;

    protected $table = 'symptom_categories';

    protected $fillable = ['plant_id', 'nama_kategori', 'slug', 'urutan'];

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    public function gejala(): HasMany
    {
        return $this->hasMany(Gejala::class, 'category_id');
    }
}

// =============================================================================
// FILE: app/Models/Gejala.php
// =============================================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Gejala extends Model
{
    use HasFactory;

    protected $table = 'gejala';

    // Column names preserved: id, kode, nama_gejala (per spec)
    protected $fillable = ['category_id', 'kode', 'nama_gejala', 'deskripsi', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(SymptomCategory::class, 'category_id');
    }

    public function rules(): HasMany
    {
        return $this->hasMany(Rule::class, 'gejala_id');
    }

    public function diagnosisSymptoms(): HasMany
    {
        return $this->hasMany(DiagnosisSymptom::class, 'gejala_id');
    }
}

// =============================================================================
// FILE: app/Models/Disease.php
// =============================================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Disease extends Model
{
    use HasFactory;

    protected $table = 'diseases';

    protected $fillable = [
        'plant_id', 'kode', 'nama_penyakit',
        'deskripsi', 'penyebab', 'gambar', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    public function rules(): HasMany
    {
        return $this->hasMany(Rule::class, 'disease_id');
    }

    public function knowledgeBase(): HasMany
    {
        return $this->hasMany(KnowledgeBase::class, 'disease_id')->orderBy('urutan');
    }

    public function solutions(): HasMany
    {
        return $this->hasMany(KnowledgeBase::class, 'disease_id')
            ->where('tipe', 'solusi')
            ->orderBy('urutan');
    }

    public function preventions(): HasMany
    {
        return $this->hasMany(KnowledgeBase::class, 'disease_id')
            ->where('tipe', 'pencegahan')
            ->orderBy('urutan');
    }

    /**
     * Total number of symptoms (rules) defined for this disease.
     * Used by the similarity calculation.
     */
    public function getTotalSymptomsAttribute(): int
    {
        return $this->rules()->count();
    }
}

// =============================================================================
// FILE: app/Models/Rule.php
// =============================================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rule extends Model
{
    use HasFactory;

    protected $table = 'rules';

    protected $fillable = ['disease_id', 'gejala_id', 'mb', 'md', 'is_primary'];

    protected $casts = [
        'mb'         => 'float',
        'md'         => 'float',
        'is_primary' => 'boolean',
    ];

    public function disease(): BelongsTo
    {
        return $this->belongsTo(Disease::class);
    }

    public function gejala(): BelongsTo
    {
        return $this->belongsTo(Gejala::class);
    }

    /**
     * CF for this single rule = MB - MD.
     */
    public function getCfAttribute(): float
    {
        return round($this->mb - $this->md, 4);
    }
}

// =============================================================================
// FILE: app/Models/KnowledgeBase.php
// =============================================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KnowledgeBase extends Model
{
    use HasFactory;

    protected $table = 'knowledge_base';

    protected $fillable = ['disease_id', 'tipe', 'konten', 'urutan'];

    public function disease(): BelongsTo
    {
        return $this->belongsTo(Disease::class);
    }
}

// =============================================================================
// FILE: app/Models/DiagnosisSession.php
// =============================================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DiagnosisSession extends Model
{
    use HasFactory;

    protected $table = 'diagnosis_sessions';

    protected $fillable = [
        'session_code', 'nama_user', 'plant_id', 'metode_akhir',
        'ip_address', 'user_agent', 'image_analysis_payload', 'image_path',
    ];

    protected $casts = [
        'image_analysis_payload' => 'array',
    ];

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(DiagnosisResult::class, 'session_id')->orderBy('peringkat');
    }

    public function symptoms(): HasMany
    {
        return $this->hasMany(DiagnosisSymptom::class, 'session_id');
    }

    public function topResult(): ?DiagnosisResult
    {
        return $this->results()->where('peringkat', 1)->first();
    }
}

// =============================================================================
// FILE: app/Models/DiagnosisResult.php
// =============================================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DiagnosisResult extends Model
{
    use HasFactory;

    protected $table = 'diagnosis_results';

    protected $fillable = [
        'session_id', 'disease_id', 'cf_value', 'cf_percentage',
        'similarity_score', 'metode', 'peringkat', 'cf_steps',
    ];

    protected $casts = [
        'cf_value'        => 'float',
        'cf_percentage'   => 'float',
        'similarity_score'=> 'float',
        'cf_steps'        => 'array',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(DiagnosisSession::class, 'session_id');
    }

    public function disease(): BelongsTo
    {
        return $this->belongsTo(Disease::class);
    }

    public function getConfidenceLabelAttribute(): string
    {
        return match (true) {
            $this->cf_percentage >= 80 => 'SANGAT TINGGI',
            $this->cf_percentage >= 60 => 'TINGGI',
            $this->cf_percentage >= 40 => 'SEDANG',
            default                    => 'RENDAH',
        };
    }
}

// =============================================================================
// FILE: app/Models/DiagnosisSymptom.php
// =============================================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DiagnosisSymptom extends Model
{
    use HasFactory;

    protected $table = 'diagnosis_symptoms';

    protected $fillable = ['session_id', 'gejala_id', 'prefilled_by_image'];

    protected $casts = ['prefilled_by_image' => 'boolean'];

    public function session(): BelongsTo
    {
        return $this->belongsTo(DiagnosisSession::class, 'session_id');
    }

    public function gejala(): BelongsTo
    {
        return $this->belongsTo(Gejala::class);
    }
}