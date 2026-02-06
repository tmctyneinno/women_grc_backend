<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Speaker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventSpeakerController extends Controller
{
    public function index(Event $event)
    {
        // Eager load the speakers relationship
        $event->load('speakers');
        $speakers = $event->speakers;
        
        return view('admin.events.speakers.index', compact('event', 'speakers'));
    }

    public function create(Event $event)
    {
        return view('admin.events.speakers.create', compact('event'));
    }

    public function store(Request $request, Event $event)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'brief' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'order' => 'nullable|integer'
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('speakers', 'public');
            $validated['image'] = basename($path);
        }

        $event->speakers()->create($validated);

        // Update event to indicate it has speakers
        $event->update(['has_speakers' => true]);

        return redirect()->route('admin.events.speakers.index', $event)
            ->with('success', 'Speaker added successfully.');
    }

    public function edit(Event $event, Speaker $speaker)
    {
        return view('admin.events.speakers.edit', compact('event', 'speaker'));
    }

    public function update(Request $request, Event $event, Speaker $speaker)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'brief' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'order' => 'nullable|integer'
        ]);

        if ($request->hasFile('image')) {
            // Delete old image
            if ($speaker->image) {
                Storage::disk('public')->delete('speakers/' . $speaker->image);
            }
            
            $path = $request->file('image')->store('speakers', 'public');
            $validated['image'] = basename($path);
        }

        $speaker->update($validated);

        return redirect()->route('admin.events.speakers.index', $event)
            ->with('success', 'Speaker updated successfully.');
    }

    public function destroy(Event $event, Speaker $speaker)
    {
        // Delete image if exists
        if ($speaker->image) {
            Storage::disk('public')->delete('speakers/' . $speaker->image);
        }

        $speaker->delete();

        // If no speakers left, update event
        if ($event->speakers()->count() === 0) {
            $event->update(['has_speakers' => false]);
        }

        return redirect()->route('admin.events.speakers.index', $event)
            ->with('success', 'Speaker deleted successfully.');
    }
}