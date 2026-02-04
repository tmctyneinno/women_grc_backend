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
    public function store(Request $request)
    {
        // dd($request->all());
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
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        
        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            $path = $request->file('featured_image')->store('events/featured', 'public');
            $data['featured_image'] = $path;
        }
        
        // Auto-generate slug if not provided
        if (empty($data['slug'])) {
            $slug = Str::slug($data['title']);
            $count = Event::where('slug', 'like', $slug . '%')->count();
            $data['slug'] = $count > 0 ? $slug . '-' . ($count + 1) : $slug;
        }

        // Set created_by
        $data['created_by'] = auth()->guard('admin')->id();

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
            'start_date' => 'required|date',
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
}