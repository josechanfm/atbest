<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Member;
use App\Models\OrganizationJoinRequest;
use Illuminate\Support\Facades\DB;

class JoinRequestController extends Controller
{
    /**
     * Display a listing of all organization join requests.
     */
    public function index(Request $request)
    {
        $query = OrganizationJoinRequest::with(['user', 'organization'])
            ->orderBy('created_at', 'desc');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('per_page', 15);
        $requests = $query->paginate($perPage)->withQueryString();

        return Inertia::render('Admin/JoinRequests', [
            'requests' => $requests,
            'filters' => $request->only(['search', 'status', 'per_page']),
            'status' => session('status'),
        ]);
    }

    /**
     * Approve a join request: create a member record in the organization.
     */
    public function approve(OrganizationJoinRequest $joinRequest)
    {
        DB::transaction(function () use ($joinRequest) {
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
        $joinRequest->update(['status' => 'rejected']);

        return back()->with('status', 'join_request.rejected');
    }
}
