<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $events = Event::where('status', 'published')
                      ->orderBy('start_date', 'asc')
                      ->paginate(10);
        
        // Format image URLs
        $events->transform(function ($event) {
            if ($event->image) {
                $event->image = url('storage/' . $event->image);
            }
            return $event;
        });
        
        return response()->json([
            'success' => true,
            'data' => $events,
            'message' => 'Events retrieved successfully.'
        ]);
    }

    /**
     * Display the specified resource.
     */
   public function show($identifier)
{
    try {
        \Log::info("Fetching event: {$identifier}");
        
        // $event = Event::with(['speakers' => function($query) {
        //     $query->orderBy('order', 'asc');
        // }])->where('slug', $identifier)
        //    ->where('status', 'published')
        //    ->first();
        $event = Event::with('speakers')->where('slug', $identifier)->first();
dd($event->speakers);
        
        if (!$event) {
            \Log::warning("Event not found: {$identifier}");
            return response()->json([
                'success' => false,
                'message' => 'Event not found.'
            ], 404);
        }

        \Log::info("Event found: ID {$event->id}, Title: {$event->title}");
        
        // SAFE CHECK: Ensure speakers is never null
        $speakers = [];
        
        // Check if speakers relation exists and is loaded
        if (method_exists($event, 'speakers')) {
            if ($event->relationLoaded('speakers')) {
                // Speakers were eager-loaded
                \Log::info("Speakers eager-loaded: " . ($event->speakers ? 'Yes' : 'No'));
                
                if ($event->speakers) {
                    \Log::info("Speakers count: " . $event->speakers->count());
                    if ($event->speakers->count() > 0) {
                        \Log::info("Speaker names: " . $event->speakers->pluck('name')->implode(', '));
                    }
                    
                    // Format speakers
                    $speakers = $event->speakers->map(function($speaker) {
                        return [
                            'id' => $speaker->id,
                            'name' => $speaker->name,
                            'title' => $speaker->title,
                            'brief' => $speaker->brief,
                            'avatar' => $speaker->image ? asset('storage/speakers/' . $speaker->image) : null,
                            'image_url' => $speaker->image ? asset('storage/speakers/' . $speaker->image) : null,
                            'order' => $speaker->order, 
                        ];
                    })->toArray();
                }
            } else {
                // Speakers not eager-loaded, load them explicitly
                \Log::warning("Speakers were not eager-loaded, loading manually");
                $loadedSpeakers = $event->speakers()->orderBy('order', 'asc')->get();
                if ($loadedSpeakers) {
                    $speakers = $loadedSpeakers->map(function($speaker) {
                        return [
                            'id' => $speaker->id,
                            'name' => $speaker->name,
                            'title' => $speaker->title,
                            'brief' => $speaker->brief,
                            'avatar' => $speaker->image ? asset('storage/speakers/' . $speaker->image) : null,
                            'image_url' => $speaker->image ? asset('storage/speakers/' . $speaker->image) : null,
                            'order' => $speaker->order, 
                        ];
                    })->toArray();
                }
            }
        } else {
            \Log::warning("Event model does not have speakers() method - relationship missing");
        }
        
        // ALTERNATIVE SIMPLER VERSION:
        // If you want a simpler approach, use this instead of the above:
        /*
        $speakers = [];
        if ($event->relationLoaded('speakers') && $event->speakers) {
            $speakers = $event->speakers->map(function($speaker) {
                return [
                    'id' => $speaker->id,
                    'name' => $speaker->name,
                    'title' => $speaker->title,
                    'brief' => $speaker->brief,
                    'avatar' => $speaker->image ? asset('storage/speakers/' . $speaker->image) : null,
                    'image_url' => $speaker->image ? asset('storage/speakers/' . $speaker->image) : null,
                    'order' => $speaker->order, 
                ];
            })->toArray();
        }
        */
        
        // Format the response data properly
        $eventData = [
            'id' => $event->id,
            'title' => $event->title,
            'slug' => $event->slug,
            'description' => $event->description,
            'short_description' => $event->short_description,
            'featured_image' => $event->featured_image ? asset('storage/' . $event->featured_image) : null,
            'gallery_images' => $event->gallery_images,
            'start_date' => $event->start_date,
            'end_date' => $event->end_date,
            'venue' => $event->venue,
            'address' => $event->address,
            'city' => $event->city,
            'state' => $event->state,
            'country' => $event->country,
            'status' => $event->status,
            'speakers' => $speakers, // This will always be an array (empty or with data)
            'type' => $event->type,
            'capacity' => $event->capacity,
            'registered_count' => $event->registered_count,
            'price' => $event->price,
            'currency' => $event->currency,
            'formatted_price' => $event->formatted_price,
            'is_featured' => (bool)$event->is_featured,
            'is_online' => (bool)$event->is_online,
            'meeting_link' => $event->meeting_link,
            'sponsors' => $event->sponsors,
            'tags' => $event->tags,
            'is_upcoming' => (bool)$event->is_upcoming,
            'is_ongoing' => (bool)$event->is_ongoing,
            'is_past' => (bool)$event->is_past,
            'registration_percentage' => $event->registration_percentage,
            'duration_hours' => $event->duration_hours,
            'has_capacity' => $event->hasCapacity(),
            'created_at' => $event->created_at,
            'updated_at' => $event->updated_at,
        ];
        
        return response()->json([
            'success' => true,
            'data' => $eventData,
            'message' => 'Event retrieved successfully.'
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Event API Error: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'message' => 'Server error',
            'error' => env('APP_DEBUG') ? $e->getMessage() : 'Internal server error'
        ], 500);
    }
}

    /**
     * Get featured events.
     */
    public function featured()
    {
        // $events = Event::with([ 'category'])
        $events = Event::where('status', 'published')
                      ->where('is_featured', true)
                      ->orderBy('start_date', 'asc')
                      ->take(6)
                      ->get();
        // Format image URLs
        $events->transform(function ($event) {
            if ($event->image) {
                $event->image = url('storage/' . $event->image);
            }
            return $event;
        });
        
        return response()->json([
            'success' => true,
            'data' => $events,
            'message' => 'Featured events retrieved successfully.'
        ]);
    }

    /**
     * Get upcoming events.
     */
    public function upcoming()
    {
        $events = Event::where('status', 'published')
                      ->where('start_date', '>=', now())
                      ->orderBy('start_date', 'asc')
                      ->take(10)
                      ->get();
        
        return response()->json([
            'success' => true,
            'data' => $events,
            'message' => 'Upcoming events retrieved successfully.'
        ]);
    }
}