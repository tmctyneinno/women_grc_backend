<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\EventBooking;
use Illuminate\Support\Facades\Auth;


class EventController extends Controller
{
    private function toPublicStorageUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/storage/')) {
            return asset(ltrim($path, '/'));
        }

        $normalized = ltrim($path, '/');
        if (str_starts_with($normalized, 'storage/')) {
            return asset($normalized);
        }

        return asset('storage/' . $normalized);
    }

    /**
     * Display a listing of the resource.
     */






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
        
        // Get event WITHOUT eager loading (since it's not working)
        $event = Event::where('slug', $identifier)
                     ->where('status', 'published')
                     ->first();
        
        if (!$event) {
            \Log::warning("Event not found: {$identifier}");
            return response()->json([
                'success' => false,
                'message' => 'Event not found.'
            ], 404);
        }

        \Log::info("Event found: ID {$event->id}, Title: {$event->title}");
        
        // MANUALLY load speakers (since eager loading fails)
        $speakers = $event->speakers()->orderBy('order', 'asc')->get();
        \Log::info("Manually loaded speakers count: " . $speakers->count());
        
        // Format speakers
        $formattedSpeakers = $speakers->map(function($speaker) {
            
            return [
                'id' => $speaker->id,
                'name' => $speaker->name,
                'title' => $speaker->title,
                'brief' => $speaker->brief,
                'avatar' => $speaker->image ? asset('storage/' . $speaker->image) : null,
                'image_url' => $speaker->image ? asset('storage/' . $speaker->image) : null,
                'order' => $speaker->order, 
            ];
        })->toArray();
        
        // Format the response data
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
            'speakers' => $formattedSpeakers, // Use formatted speakers
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
















































































//     public function index()
//         {
//             $events = Event::with('speakers')
//                 ->orderBy('start_date', 'asc')
//                 ->withCount(['confirmedBookings as registered_count'])
//                 ->paginate(4);

//             $events->getCollection()->transform(function ($event) {

//                 // Event image
//                 $event->featured_image = $this->toPublicStorageUrl($event->featured_image ?? null);
//                 $event->image = $event->featured_image;

//                 // Always ensure speakers is a collection
//                 $speakers = $event->speakers ?? collect();

//                 $event->speakers = $speakers->map(function ($speaker) {
//                     $speaker->image = $this->toPublicStorageUrl($speaker->image ?? null);
//                     return $speaker;
//                 });

//                 return $event;
//             });

//             return ApiResponse::success($events, 'Events retrieved successfully.');
//         }

//     /**
//      * Display the specified resource.
//      */
  
//     public function show($identifier)
// {
//     try {
//         \Log::info("Fetching event: {$identifier}");
        
//         // Get event WITHOUT eager loading (since it's not working)
//         $event = Event::where('slug', $identifier)
//                      ->where('status', 'published')
//                      ->first();
        
//         if (!$event) {
//             \Log::warning("Event not found: {$identifier}");
//             // return response()->json([
//             //     'success' => false,
//             //     'message' => 'Event not found.'
//             // ], 404);
//             return ApiResponse::error('Event not found.', [], 404);
//         }

//         \Log::info("Event found: ID {$event->id}, Title: {$event->title}");
        
//         // MANUALLY load speakers (since eager loading fails)
//         $speakers = $event->speakers()->orderBy('order', 'asc')->get();
//         \Log::info("Manually loaded speakers count: " . $speakers->count());
        
//         // Format speakers
//         $formattedSpeakers = $speakers->map(function($speaker) {
            
//             // return [
//             //     'id' => $speaker->id,
//             //     'name' => $speaker->name,
//             //     'title' => $speaker->title,
//             //     'brief' => $speaker->brief,
//             //     'avatar' => $speaker->image ? asset('storage/' . $speaker->image) : null,
//             //     'image_url' => $speaker->image ? asset('storage/' . $speaker->image) : null,
//             //     'order' => $speaker->order, 
//             // ];

//             return ApiResponse::transformSpeaker($speaker);
//         })->toArray();
        
//         // Format the response data
//         $eventData = [
//             'id' => $event->id,
//             'title' => $event->title,
//             'slug' => $event->slug,
//             'description' => $event->description,
//             'short_description' => $event->short_description,
//             'featured_image' => $this->toPublicStorageUrl($event->featured_image),
//             'gallery_images' => $event->gallery_images,
//             'start_date' => $event->start_date,
//             'end_date' => $event->end_date,
//             'venue' => $event->venue,
//             'address' => $event->address,
//             'city' => $event->city,
//             'state' => $event->state,
//             'country' => $event->country,
//             'status' => $event->status,
//             'speakers' => $formattedSpeakers, // Use formatted speakers
//             'type' => $event->type,
//             'capacity' => $event->capacity,
//             'registered_count' => $event->registered_count,
//             'price' => $event->price,
//             'currency' => $event->currency,
//             'formatted_price' => $event->formatted_price,
//             'is_featured' => (bool)$event->is_featured,
//             'is_online' => (bool)$event->is_online,
//             'meeting_link' => $event->meeting_link,
//             'sponsors' => $event->sponsors,
//             'tags' => $event->tags,
//             'is_upcoming' => (bool)$event->is_upcoming,
//             'is_ongoing' => (bool)$event->is_ongoing,
//             'is_past' => (bool)$event->is_past,
//             'registration_percentage' => $event->registration_percentage,
//             'duration_hours' => $event->duration_hours,
//             'has_capacity' => $event->hasCapacity(),
//             'created_at' => $event->created_at,
//             'updated_at' => $event->updated_at,
//         ];
        
//         // return response()->json([
//         //     'success' => true,
//         //     'data' => $eventData,
//         //     'message' => 'Event retrieved successfully.'
//         // ]);

//         return ApiResponse::success($eventData, 'Event retrieved successfully.');
        
//     } catch (\Exception $e) {
//         \Log::error('Event API Error: ' . $e->getMessage());
//         \Log::error('Stack trace: ' . $e->getTraceAsString());
        
//         return ApiResponse::error('Server error', [], 500);
//     }

// }

//     /**
//      * Get featured events.
//      */
//     public function featured()
//     {
//         // $events = Event::with([ 'category'])
//         $events = Event::where('status', 'published')
//                       ->where('is_featured', true)
//                       ->orderBy('start_date', 'asc')
//                       ->take(6)
//                       ->get();
//         // Format image URLs
//         $events->transform(function ($event) {
//             $event->featured_image = $this->toPublicStorageUrl($event->featured_image ?? null);
//             $event->image = $event->featured_image;
//             return $event;
//         });
        
//         return ApiResponse::success($events, 'Featured events retrieved successfully.');
//     }

//     /**
//      * Get upcoming events.
//      */
//     public function upcoming()
//     {
//         $events = Event::where('status', 'published')
//                       ->where('start_date', '>=', now())
//                       ->orderBy('start_date', 'asc')
//                       ->take(10)
//                       ->get();

//         $events->transform(function ($event) {
//             $event->featured_image = $this->toPublicStorageUrl($event->featured_image ?? null);
//             $event->image = $event->featured_image;
//             return $event;
//         });
        
//         return ApiResponse::success($events, 'Upcoming events retrieved successfully.');
//     }

    public function myBookings()
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return ApiResponse::error('Unauthenticated.', [], 401);
        }

        $bookings = EventBooking::query()
            ->with([
                'event' => function ($query) {
                    $query->with('speakers')->withCount(['confirmedBookings as registered_count']);
                }
            ])
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(function ($booking) {
                $event = $booking->event;
                if (!$event) {
                    return null;
                }

                $event->featured_image = $this->toPublicStorageUrl($event->featured_image ?? null);
                $event->image = $event->featured_image;
                $event->speakers = ($event->speakers ?? collect())->map(function ($speaker) {
                    $speaker->image = $this->toPublicStorageUrl($speaker->image ?? null);
                    return $speaker;
                });

                return [
                    'booking_id' => $booking->id,
                    'status' => $booking->status,
                    'booked_at' => $booking->created_at,
                    'event' => $event,
                ];
            })
            ->filter()
            ->values();

        return ApiResponse::success($bookings, 'My booked events fetched successfully.');
    }





    public function book(Request $request, Event $event)
        {
            $user = Auth::guard('sanctum')->user();

            if (!$user) {
                return ApiResponse::error('You must be logged in to book an event', 401);
            }

            // 1. Check if event is published
            if ($event->isPast) {
                return ApiResponse::error('This event is not available for booking', 403);
            }

            // 2. Check capacity
            $confirmedCount = $event->confirmedBookings()->count();

            if ($event->capacity !== null && $confirmedCount >= $event->capacity) {
                return ApiResponse::error('This event is fully booked', 400);
            }

            // 3. Prevent duplicate booking
            $existing = EventBooking::where('event_id', $event->id)
                ->where('user_id', $user->id)
                ->first();

            if ($existing) {
                return ApiResponse::error('You have already booked this event', 409);
            }

            // 4. If FREE event → auto confirm
            if ($event->price == 0) {

                $booking = EventBooking::create([
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'status' => 'confirmed',
                ]);

                return ApiResponse::success([
                    'booking' => $booking,
                    'event' => $event,
                ], 'Event booked successfully 🎉');
            }

            // 5. If PAID event → return payment intent
            $booking = EventBooking::create([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'status' => 'pending',
            ]);

            return ApiResponse::success([
                'booking_id' => $booking->id,
                'price' => $event->price,
                'currency' => 'NGN',
                'payment_required' => true,
            ], 'Payment required to complete booking');
        }
}
