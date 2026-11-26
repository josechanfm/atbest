<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EnquiryController extends Controller
{
    /**
     * Display a listing of enquiries.
     */
    public function index(Request $request)
    {
        $query = Enquiry::orderBy(
            $request->input('sort_field', 'created_at'),
            $request->input('sort_direction', 'desc')
        );

        // Search across name, email, phone, subject, message
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $perPage = $request->input('per_page', 15);
        $enquiries = $query->paginate($perPage)->withQueryString();

        return Inertia::render('Admin/Enquiries', [
            'enquiries' => $enquiries,
            'filters'   => $request->only(['search', 'status', 'sort_field', 'sort_direction', 'per_page']),
        ]);
    }

    /**
     * Mark an enquiry as read.
     */
    public function markRead(Enquiry $enquiry)
    {
        $enquiry->update(['status' => 'read']);
        return redirect()->back()->with('success', 'Marked as read.');
    }

    /**
     * Remove the specified enquiry.
     */
    public function destroy(Enquiry $enquiry)
    {
        $enquiry->delete();
        return redirect()->back()->with('success', 'Enquiry deleted.');
    }
}
