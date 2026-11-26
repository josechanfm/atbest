<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\OrganizationJoinRequest;

class JoinRequestController extends Controller
{
    /**
     * Store a new organization join request.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'reason' => 'nullable|string',
        ]);

        $user = auth()->user();

        // Prevent duplicate pending requests for the same organization.
        $existing = OrganizationJoinRequest::where('user_id', $user->id)
            ->where('organization_id', $validated['organization_id'])
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return back()->withErrors([
                'organization_id' => 'join_request.already_pending',
            ])->withInput();
        }

        OrganizationJoinRequest::create([
            'user_id' => $user->id,
            'organization_id' => $validated['organization_id'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'reason' => $validated['reason'] ?? null,
            'status' => 'pending',
        ]);

        return back()->with('status', 'join_request.submitted');
    }
}
