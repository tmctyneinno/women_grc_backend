<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'short_description',
        'slug',
        'featured_image',
        'gallery_images',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'venue',
        'address',
        'city',
        'state',
        'country',
        'zip_code',
        'latitude',
        'longitude',
        'status',
        'type',
        'visibility',
        'capacity',
        'registered_count',
        'price',
        'currency',
        'registration_fields', 
        'is_featured',
        'is_online',
        'meeting_link',
        'speakers', 
        'sponsors',
        'tags',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'has_speakers',
        'speakers_title',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'gallery_images' => 'array',
        'registration_fields' => 'array',
        'speakers' => 'array',
        'sponsors' => 'array',
        'tags' => 'array',
        'meta_keywords' => 'array',
        'is_featured' => 'boolean',
        'is_online' => 'boolean',
        'price' => 'decimal:2',
        'capacity' => 'integer',
        'registered_count' => 'integer',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'organizer' => 'array',
    ];

    protected $appends = [
        'formatted_price',
        'is_upcoming',
        'is_ongoing',
        'is_past',
        'registration_percentage',
        'duration_hours',
    ];

    public function speakers()
    {
        return $this->hasMany(Speaker::class);
    }
    
    
    // Relationships
    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    // public function organizer()
    // {
    //     // If organizer is stored as JSON in the event table
    //     if ($this->organizer_data) {
    //         return (object) json_decode($this->organizer_data, true);
    //     }
        
    //     // If organizer is a separate table with foreign key
    //     return $this->belongsTo(Organizer::class, 'organizer_id');
    // }

    public function updater()
    {
        return $this->belongsTo(Admin::class, 'updated_by');
    }

    public function registrations()
    {
        return $this->hasMany(EventRegistration::class);
    }

    // Accessors
    protected function formattedPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->price > 0 
                ? $this->currency . ' ' . number_format($this->price, 2)
                : 'Free',
        );
    }

    protected function isUpcoming(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->start_date > now(),
        );
    }

    protected function isOngoing(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->start_date <= now() && $this->end_date >= now(),
        );
    }

    protected function isPast(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->end_date < now(),
        );
    }

    protected function registrationPercentage(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->capacity) return 0;
                return round(($this->registered_count / $this->capacity) * 100, 2);
            },
        );
    }

    protected function durationHours(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->start_date || !$this->end_date) return 0;
                return round($this->start_date->diffInHours($this->end_date), 1);
            },
        );
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
    }

    public function scopeOngoing($query)
    {
        return $query->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
    }

    public function scopePast($query)
    {
        return $query->where('end_date', '<', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('venue', 'like', "%{$search}%");
        });
    }

    // Business Logic
    public function incrementRegistrationCount()
    {
        $this->increment('registered_count');
    }

    public function decrementRegistrationCount()
    {
        $this->decrement('registered_count');
    }

    public function hasCapacity()
    {
        return !$this->capacity || $this->registered_count < $this->capacity;
    }

    public function generateSlug()
    {
        $slug = Str::slug($this->title);
        $count = Event::where('slug', 'like', $slug . '%')->count();
        
        if ($count > 0) {
            $slug = $slug . '-' . ($count + 1);
        }
        
        return $slug;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            if (empty($event->slug)) {
                $event->slug = $event->generateSlug();
            }
            
            if (auth()->check() && auth()->guard('admin')->check()) {
                $event->created_by = auth()->guard('admin')->id();
            }
        });

        static::updating(function ($event) {
            if (auth()->check() && auth()->guard('admin')->check()) {
                $event->updated_by = auth()->guard('admin')->id();
            }
        });
    }
}