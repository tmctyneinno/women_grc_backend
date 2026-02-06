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
        // $event = Event::where('slug', $identifier)
        //     ->where('status', 'published')
        //     ->first();
        $event = Event::with(['speakers' => function($query) {
            $query->orderBy('order', 'asc');
        }])->where('slug', $slug)
        ->where('status', 'published')
        ->firstOrFail();
        
        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found.'
            ], 404);
        }
        // Format the response data properly
        $eventData = [
            'id' => $event->id,
            'title' => $event->title,
            'slug' => $event->slug,
            'description' => $event->description,
            'short_description' => $event->short_description,
            'featured_image' => $event->featured_image,
            'gallery_images' => $event->gallery_images,
            'start_date' => $event->start_date,
            'end_date' => $event->end_date,
            'venue' => $event->venue,
            'address' => $event->address,
            'city' => $event->city,
            'state' => $event->state,
            'country' => $event->country,
            'status' => $event->status,
            'speakers' => $event->speakers->map(function($speaker) {
                return [
                    'id' => $speaker->id,
                    'name' => $speaker->name,
                    'title' => $speaker->title,
                    'brief' => $speaker->brief,
                    'image_url' => $speaker->image ? asset('storage/public/speakers/' . $speaker->image) : null,
                    'avatar' => $speaker->image ? asset('storage/speakers/' . $speaker->image) : null,
                    // 'image_url' => $speaker->image ? asset('storage/speakers/' . $speaker->image) : null,
                    'order' => $speaker->order, 
                ];
            }),
            'type' => $event->type,
            'capacity' => $event->capacity,
            'registered_count' => $event->registered_count,
            'price' => $event->price,
            'currency' => $event->currency,
            'formatted_price' => $event->formatted_price,
            'is_featured' => $event->is_featured,
            'is_online' => $event->is_online,
            'meeting_link' => $event->meeting_link,
            'speakers' => $event->speakers,
            'sponsors' => $event->sponsors,
            'tags' => $event->tags,
            'is_upcoming' => $event->is_upcoming,
            'is_ongoing' => $event->is_ongoing,
            'is_past' => $event->is_past,
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