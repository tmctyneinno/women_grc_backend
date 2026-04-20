<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ForumBannedWord;
use App\Services\AdminActivityService;
use Illuminate\Http\Request;

class ForumBannedWordController extends Controller
{
    public function index()
    {
        $words = ForumBannedWord::query()
            ->orderBy('word')
            ->paginate(50)
            ->withQueryString();

        return view('admin.forums.banned_words.index', compact('words'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'word' => 'required|string|max:120',
        ]);

        $word = strtolower(trim($data['word']));
        if ($word === '') {
            return back()->withErrors(['word' => 'Word cannot be empty.'])->withInput();
        }

        $record = ForumBannedWord::firstOrCreate(
            ['word' => $word],
            ['is_active' => true, 'created_by' => auth('admin')->id()]
        );

        if (!$record->is_active) {
            $record->update(['is_active' => true]);
        }

        AdminActivityService::log(auth('admin')->user(), 'forum_banned_word_add', $record, [], 'Added banned word');

        return back()->with('success', 'Banned word saved.');
    }

    public function toggle(ForumBannedWord $word)
    {
        $word->update(['is_active' => !$word->is_active]);
        AdminActivityService::log(auth('admin')->user(), 'forum_banned_word_toggle', $word, ['is_active' => $word->is_active], 'Toggled banned word');

        return back()->with('success', 'Banned word updated.');
    }

    public function destroy(ForumBannedWord $word)
    {
        $word->delete();
        AdminActivityService::log(auth('admin')->user(), 'forum_banned_word_delete', $word, [], 'Deleted banned word');

        return back()->with('success', 'Banned word removed.');
    }
}
