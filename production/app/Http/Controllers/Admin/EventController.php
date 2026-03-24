<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventController extends Controller
{
    /**
     * Display a listing of events.
     */
    public function index()
    {
        $events = Event::latest()->paginate(10);
        return view('admin.events.index', compact('events'));
    }

    /**
     * Show the form for creating a new event.
     */
    public function create()
    {
       return view('admin.events.create')->withErrors(session('errors'));
    }

    /**
     * Store a newly created event in storage.
     */
    // public function store(Request $request)
    // {
    //     // dd($request->all());
    //     $validator = Validator::make($request->all(), [
    //         'title' => 'required|string|max:255',
    //         'description' => 'required|string',
    //         'short_description' => 'nullable|string|max:500',
    //         'start_date' => 'required|date|after_or_equal:today',
    //         'end_date' => 'required|date|after:start_date',
    //         'start_time' => 'required|date_format:H:i',
    //         'end_time' => 'required|date_format:H:i',
    //         'venue' => 'required|string|max:255',
    //         'meeting_link' => 'required|string',
    //         'status' => 'required|in:draft,published,cancelled,completed',
    //         'type' => 'required|in:conference,workshop,seminar,meeting,networking,other',
    //         'visibility' => 'required|in:public,private,members_only',
    //         'capacity' => 'nullable|integer|min:1',
    //         'price' => 'nullable|numeric|min:0',
    //         'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
    //     ]);

    //     if ($validator->fails()) {
    //         return redirect()->back()
    //             ->withErrors($validator)
    //             ->withInput();
    //     }

    //     $data = $request->all();
        
    //     // Handle featured image upload
    //     if ($request->hasFile('featured_image')) {
    //         $path = $request->file('featured_image')->store('events/featured', 'public');
    //         $data['featured_image'] = $path;
    //     } 
        
    //     // Auto-generate slug if not provided
    //     if (empty($data['slug'])) {
    //         $slug = Str::slug($data['title']);
    //         $count = Event::where('slug', 'like', $slug . '%')->count();
    //         $data['slug'] = $count > 0 ? $slug . '-' . ($count + 1) : $slug;
    //     }

    //     // Set created_by
    //     $data['created_by'] = auth()->guard('admin')->id();

    //     Event::create($data);

    //     return redirect()->route('admin.events.index')
    //         ->with('success', 'Event created successfully.');
    // }





    public function store(Request $request)
        {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'short_description' => 'nullable|string|max:500',
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after_or_equal:start_date',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i',
                'venue' => 'required|string|max:255',
                'meeting_link' => 'nullable|string|url',
                'type' => 'required|in:conference,workshop,seminar,meeting,networking,other',
                'visibility' => 'required|in:public,private,members_only',
                'capacity' => 'nullable|integer|min:1',
                'price' => 'nullable|numeric|min:0',
                'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // ✅ Whitelist safe fields only
            $data = $request->only([
                'title','description','short_description',
                'start_date','end_date','start_time','end_time',
                'venue','meeting_link','type','visibility',
                'capacity','price'
            ]);

            // ✅ Featured image upload
            if ($request->hasFile('featured_image')) {
                $data['featured_image'] = $request->file('featured_image')
                    ->store('events/featured', 'public');
            }

            // ✅ Slug (keep here OR model, not both)
            $slug = Str::slug($data['title']);
            $count = Event::where('slug', 'like', $slug . '%')->count();
            $data['slug'] = $count > 0 ? $slug . '-' . ($count + 1) : $slug;

            // ✅ Admin owner
            $data['created_by'] = auth()->guard('admin')->id();

            // ✅ Action buttons logic
            $data['status'] = $request->action === 'publish' ? 'published' : 'draft';

            // ✅ Online flag (smart default)
            $data['is_online'] = !empty($data['meeting_link']);

            // ✅ Same-day time validation
            if ($data['start_date'] === $data['end_date'] && $data['end_time'] <= $data['start_time']) {
                return back()
                    ->withErrors(['end_time' => 'End time must be after start time for same-day events'])
                    ->withInput();
            }

            Event::create($data);

            return redirect()->route('admin.events.index')
                ->with('success', 'Event created successfully.');
        }

    /**
     * Display the specified event.
     */
    public function show(Event $event)
    {
        return view('admin.events.show', compact('event'));
    }

    /**
     * Show the form for editing the specified event.
     */
    public function edit($id)
    {
        $event = Event::findOrFail($id);
        return view('admin.events.edit', compact('event'));
    }

    /**
     * Update the specified event in storage.
     */
    public function update(Request $request, $id)
    {
        // Check if it's the correct event
        $event = Event::findOrFail($id);
        

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'venue' => 'required|string|max:255',
            'meeting_link' => 'required|string',
            'status' => 'required|in:draft,published,cancelled,completed',
            'type' => 'required|in:conference,workshop,seminar,meeting,networking,other',
            'visibility' => 'required|in:public,private,members_only',
            'capacity' => 'nullable|integer|min:1',
            'price' => 'nullable|numeric|min:0',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        if ($validator->fails()) {
            \Log::error('Validation failed:', $validator->errors()->toArray());
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Prepare update data
        $updateData = $request->only([
            'title',
            'description', 
            'short_description',
            'start_date',
            'end_date',
            'start_time',
            'end_time',
            'venue',
            'meeting_link',
            'status',
            'type',
            'visibility',
            'capacity',
            'price',
        ]);

        \Log::info('Data to update:', $updateData);

        // Update the event
        $event->fill($updateData);

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            \Log::info('New featured image uploaded');
            
            // Delete old image if exists
            if ($event->featured_image) {
                \Log::info('Deleting old image:', ['path' => $event->featured_image]);
                Storage::disk('public')->delete($event->featured_image);
            }
            
            // Store new image
            $path = $request->file('featured_image')->store('events/featured', 'public');
            $event->featured_image = $path; 
            \Log::info('New image saved:', ['path' => $path]);
        }

        // Handle remove_image checkbox
        if ($request->has('remove_image') && $request->remove_image == '1') {
            \Log::info('Removing current image as requested');
            if ($event->featured_image) {
                Storage::disk('public')->delete($event->featured_image);
            }
            $event->featured_image = null;
        }

        // Set updated_by
        $event->updated_by = auth()->guard('admin')->id();

        // Save the event
        try {
            $event->save();
            
            \Log::info('=== EVENT SAVED SUCCESSFULLY ===');
            \Log::info('Updated event data:', [
                'id' => $event->id,
                'title' => $event->title,
                'meeting_link' => $event->meeting_link,
                'is_online' => $event->is_online,
                'updated_at' => $event->updated_at,
            ]);
            
            // Refresh from database
            $event->refresh();
            
            \Log::info('After refresh:', [
                'meeting_link' => $event->meeting_link,
                'is_online' => $event->is_online,
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error saving event:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Error updating event: ' . $e->getMessage())
                ->withInput();
        }

        // Check what action was requested
        $action = $request->input('action', 'update');
        
        if ($action === 'draft') {
            return redirect()->route('admin.events.index')
                ->with('success', 'Event saved as draft successfully.');
        }

        return redirect()->route('admin.events.index')
            ->with('success', 'Event updated successfully.');
    }

    /**
     * Remove the specified event from storage.
     */
    public function destroy(Event $event)
    {
        // Delete featured image if exists
        if ($event->featured_image) {
            Storage::disk('public')->delete($event->featured_image);
        }
        
        // Delete gallery images if exist
        if ($event->gallery_images) {
            foreach ($event->gallery_images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $event->delete();

        return redirect()->route('admin.events.index')
            ->with('success', 'Event deleted successfully.');
    }

    /**
     * Update the status of an event.
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $event = Event::findOrFail($id);

            $validated = $request->validate([
                'status' => 'required|in:draft,published,cancelled,completed'
            ]);

            $event->update([
                'status' => $validated['status'],
                'updated_by' => auth()->guard('admin')->id()
            ]);

            return redirect()->back()
                ->with('success', 'Event status updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update event status: ' . $e->getMessage());
        }
    }

    /**
     * Remove a gallery image from an event.
     */
    public function removeGalleryImage(Request $request, $id)
    {
        try {
            $event = Event::findOrFail($id);

            $validated = $request->validate([
                'image_index' => 'required|integer|min:0'
            ]);

            if ($event->gallery_images) {
                $images = $event->gallery_images;
                
                if (isset($images[$validated['image_index']])) {
                    // Delete the file from storage
                    Storage::disk('public')->delete($images[$validated['image_index']]);
                    
                    // Remove from array
                    array_splice($images, $validated['image_index'], 1);
                    
                    // Update event
                    $event->update([
                        'gallery_images' => array_values($images),
                        'updated_by' => auth()->guard('admin')->id()
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Gallery image removed successfully.'
                    ]);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Image not found.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove image: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display featured events.
     */
    public function featured()
    {
        try {
            $events = Event::where('is_featured', true)
                ->latest()
                ->paginate(10);

            return view('admin.events.index', compact('events'))
                ->with('title', 'Featured Events');
        } catch (\Exception $e) {
            return redirect()->route('admin.events.index')
                ->with('error', 'Failed to load featured events: ' . $e->getMessage());
        }
    }

    /**
     * Display upcoming events.
     */
    public function upcoming()
    {
        try {
            $events = Event::where('start_date', '>', now())
                ->orderBy('start_date', 'asc')
                ->paginate(10);

            return view('admin.events.index', compact('events'))
                ->with('title', 'Upcoming Events');
        } catch (\Exception $e) {
            return redirect()->route('admin.events.index')
                ->with('error', 'Failed to load upcoming events: ' . $e->getMessage());
        }
    }

    /**
     * Display calendar view of events.
     */
    public function calendar()
    {
        try {
            $events = Event::orderBy('start_date', 'asc')
                ->get()
                ->map(function ($event) {
                    return [
                        'id' => $event->id,
                        'title' => $event->title,
                        'start' => $event->start_date->toIso8601String(),
                        'end' => $event->end_date->toIso8601String(),
                        'status' => $event->status,
                        'color' => $this->getStatusColor($event->status)
                    ];
                });

            return view('admin.events.calendar', compact('events'));
        } catch (\Exception $e) {
            return redirect()->route('admin.events.index')
                ->with('error', 'Failed to load calendar: ' . $e->getMessage());
        }
    }

    /**
     * Get color for event status.
     */
    private function getStatusColor($status)
    {
        $colors = [
            'draft' => '#gray',
            'published' => '#green',
            'cancelled' => '#red',
            'completed' => '#blue'
        ];

        return $colors[$status] ?? '#gray';
    }



    public function bookings(Event $event)
    {
        $bookings = $event->bookings()
            ->with('user')
            ->latest()
            ->paginate(20);

        return view('admin.events.bookings', compact('event', 'bookings'));
    }
}