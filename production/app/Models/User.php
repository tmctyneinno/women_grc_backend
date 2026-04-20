<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Laravel\Sanctum\HasApiTokens; 
use App\Notifications\CustomVerifyEmail;
use App\Notifications\CustomResetPassword;
use Illuminate\Support\Facades\Storage;

 

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'linkedin_profile',
        'google_id',
        'is_google_account',
        'email_verified_at',
        'status',
        'locked_until',
        'failed_login_attempts',
        'is_verified',
        'profile_picture',
        'phone_number',
        'job_title',
        'company',
        'last_login_at',
        'last_login_ip',
        'timezone_id',
        'preferences'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google_id', // Hide sensitive OAuth IDs
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_google_account' => 'boolean',
            'is_verified' => 'boolean',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'preferences' => 'array',
            'locked_until' => 'datetime',
        ];
    }

    /**
     * Interact with the linkedin profile URL.
     */
    protected function linkedinProfile(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value,
            set: fn ($value) => $this->formatLinkedInUrl($value),
        );
    }

    /**
     * Format LinkedIn URL to ensure consistency.
     */
    private function formatLinkedInUrl(string $url): string
    {
        $url = trim($url);
        
        if (empty($url)) {
            return $url;
        }
        
        // Remove any existing https:// or http://
        $url = preg_replace('#^https?://#', '', $url);
        
        // Ensure it starts with linkedin.com/in/
        if (!str_contains($url, 'linkedin.com/in/')) {
            if (str_contains($url, 'linkedin.com/')) {
                $url = str_replace('linkedin.com/', 'linkedin.com/in/', $url);
            } else {
                $url = 'linkedin.com/in/' . ltrim($url, '/');
            }
        }
        
        return 'https://' . $url;
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new CustomVerifyEmail());
    }


    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPassword($token));
    }


    public function getProfilePictureUrlAttribute()
    {
        return $this->profile_picture
            ? Storage::url($this->profile_picture)
            : null;
    }

    public function eventBookings()
    {
        return $this->hasMany(EventBooking::class);
    }

    public function courseEnrollments()
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function quizAttempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function learningPoints()
    {
        return $this->hasMany(LearningPoint::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function coursePurchases()
    {
        return $this->hasMany(CoursePurchase::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function memberships()
    {
        return $this->hasMany(UserMembership::class);
    }


    public function forumMemberships()
    {
        return $this->hasMany(ForumMembership::class);
    }

    public function forumInvitations()
    {
        return $this->hasMany(ForumInvitation::class, 'invited_user_id');
    }

    public function forumThreads()
    {
        return $this->hasMany(ForumThread::class);
    }

    public function forumPosts()
    {
        return $this->hasMany(ForumPost::class);
    }

    public function forumNotifications()
    {
        return $this->hasMany(ForumNotification::class);
    }

    public function mentorProfile()
    {
        return $this->hasOne(Mentor::class);
    }

    public function mentorshipApplications()
    {
        return $this->hasMany(MentorshipApplication::class, 'mentee_id');
    }

    public function mentorshipsAsMentee()
    {
        return $this->hasMany(Mentorship::class, 'mentee_id');
    }

    public function mentorApplications()
    {
        return $this->hasMany(MentorApplication::class);
    }

}
