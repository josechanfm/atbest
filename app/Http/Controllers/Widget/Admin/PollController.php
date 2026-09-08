<?php

namespace App\Http\Controllers\Widget\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Poll;
use App\Models\PollResponse;
use Inertia\Inertia;

class PollController extends Controller
{
    /**
     * Display a listing of polls.
     */
    public function index()
    {
        $polls = Poll::withCount('responses')->latest()->get();
        //dd($polls);
        return Inertia::render('Widget/Admin/Polls', [
            'polls'   => $polls,
            'configs' => 'widget configs',
        ]);
    }

    /**
     * Store a newly created poll in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'          => 'required|in:radio,checkbox',
            'question'      => 'required|string',
            'image'         => 'nullable|string',
            'option_a'      => 'required|string',
            'option_b'      => 'required|string',
            'option_c'      => 'nullable|string',
            'option_d'      => 'nullable|string',
            'option_e'      => 'nullable|string',
            'answer'        => $request->type === 'checkbox' ? 'required|array|min:1' : 'required|string|in:A,B,C,D,E',
            'answer.*'      => 'required|string|in:A,B,C,D,E',
            'reference'     => 'nullable|string',
            'status'        => 'boolean',
            'session_token' => 'nullable|string',
        ]);

        // Normalize answer structure if type is checkbox
        if ($validated['type'] === 'checkbox' && is_array($validated['answer'])) {
            $validated['answer'] = array_values(array_unique($validated['answer']));
        }

        Poll::create($validated);

        return redirect()->back()->with('success', 'Poll created successfully!');
    }

    /**
     * Display the specified poll details & responses.
     */
    public function show(Poll $poll)
    {
        $poll->load(['responses' => function ($query) {
            $query->latest();
        }]);

        return Inertia::render('Widget/Admin/PollDesign', [
            'poll' => $poll,
        ]);
    }

    /**
     * Update the specified poll in storage.
     */
    public function update(Request $request, Poll $poll)
    {
        $validated = $request->validate([
            'type'          => 'required|in:radio,checkbox',
            'question'      => 'required|string',
            'image'         => 'nullable|string',
            'option_a'      => 'required|string',
            'option_b'      => 'required|string',
            'option_c'      => 'nullable|string',
            'option_d'      => 'nullable|string',
            'option_e'      => 'nullable|string',
            'answer'        => $request->type === 'checkbox' ? 'required|array|min:1' : 'required|string|in:A,B,C,D,E',
            'answer.*'      => 'required|string|in:A,B,C,D,E',
            'reference'     => 'nullable|string',
            'status'        => 'boolean',
            'session_token' => 'nullable|string',
        ]);

        if ($validated['type'] === 'checkbox' && is_array($validated['answer'])) {
            $validated['answer'] = array_values(array_unique($validated['answer']));
        }

        $poll->update($validated);

        return redirect()->back()->with('success', 'Poll updated successfully!');
    }

    /**
     * Remove the specified poll from storage.
     */
    public function destroy(Poll $poll)
    {
        // Delete all associated response records first
        $poll->responses()->delete();
        $poll->delete();

        return redirect()->back()->with('success', 'Poll and its responses deleted successfully!');
    }

    /**
     * Clear all vote responses for a specific poll.
     */
    public function responseClear(Poll $poll)
    {
        PollResponse::whereBelongsTo($poll)->delete();

        return redirect()->back()->with('success', 'All responses for this poll have been cleared!');
    }

    /**
     * Fetch raw json responses for export or charting.
     */
    public function responseAll(Poll $poll)
    {
        return response()->json($poll->responses);
    }
}