<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Member;
use App\Models\OrganizationJoinRequest;
use Illuminate\Support\Facades\DB;

class JoinRequestController extends Controller
{
    /**
     * Display a listing of join requests for the current organization.
     */
    public function index(Request $request)
    {
        $organization = session('organization');

        if (empty($organization)) {
            $organization = auth()->user()->member?->organization;
            if ($organization) {
                session(['organization' => $organization]);
            }
        }

        $query = OrganizationJoinRequest::where('organization_id', $organization->id)
            ->with(['user', 'organization'])
            ->orderBy('created_at', 'desc');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $perPage = $request->input('per_page', 15);
        $requests = $query->paginate($perPage)->withQueryString();

        return Inertia::render('Organizer/JoinRequests', [
            'requests' => $requests,
            'filters' => $request->only(['status', 'per_page']),
            'status' => session('status'),
        ]);
    }

    /**
     * Approve a join request: create a member record in the organization.
     */
    public function approve(OrganizationJoinRequest $joinRequest)
    {
        if ($response = $this->authorizeOrganization($joinRequest)) {
            return $response;
        }

        DB::transaction(function () use ($joinRequest) {
            // Avoid creating a duplicate member if the user is already a member.
            $exists = Member::where('user_id', $joinRequest->user_id)
                ->where('organization_id', $joinRequest->organization_id)
                ->exists();

            if (!$exists) {
                Member::create([
                    'user_id' => $joinRequest->user_id,
                    'organization_id' => $joinRequest->organization_id,
                    'display_name' => $joinRequest->name,
                    'email' => $joinRequest->email,
                    'mobile' => $joinRequest->phone,
                    'is_default' => false,
                ]);
            }

            $joinRequest->update(['status' => 'approved']);
        });

        return back()->with('status', 'join_request.approved');
    }

    /**
     * Reject a join request.
     */
    public function reject(OrganizationJoinRequest $joinRequest)
    {
        if ($response = $this->authorizeOrganization($joinRequest)) {
            return $response;
        }

        $joinRequest->update(['status' => 'rejected']);

        return back()->with('status', 'join_request.rejected');
    }

    /**
     * Ensure the request belongs to the current organization.
     *
     * Returns a Response when access is denied, or null when authorized.
     */
    private function authorizeOrganization(OrganizationJoinRequest $joinRequest)
    {
        $organization = session('organization');

        if (!$organization || $joinRequest->organization_id !== $organization->id) {
            if (request()->wantsJson() || request()->header('X-Inertia')) {
                return response()->json([
                    'success' => false,
                    'message' => 'join_request.forbidden',
                ], 403);
            }

            return redirect()->route('organizer.joinRequests.index')
                ->withErrors(['message' => 'join_request.forbidden']);
        }

        return null;
    }
}
