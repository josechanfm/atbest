<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmsMessage;
use App\Models\Organization;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SmsMessageController extends Controller
{
    /**
     * Display a listing of SMS messages.
     */
    public function index(Request $request)
    {
        $query = SmsMessage::with(['organization', 'user'])
            ->orderBy($request->input('sort_field', 'created_at'), $request->input('sort_direction', 'desc'));

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('mobile', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Filter by organization
        if ($orgId = $request->input('organization_id')) {
            $query->where('organization_id', $orgId);
        }

        $perPage = $request->input('per_page', 15);
        $smsMessages = $query->paginate($perPage)->withQueryString();

        $organizations = Organization::select('id', 'abbr_en', 'name_zh')->where('status',true)->get();
        return Inertia::render('Admin/SmsMessages', [
            'smsMessages'   => $smsMessages,
            'filters'       => $request->only(['search', 'status', 'organization_id', 'sort_field', 'sort_direction', 'per_page']),
            'organizations' => $organizations,
        ]);
    }

    /**
     * Store a newly created SMS message and send it.
     */
    public function store(Request $request, SmsService $smsService)
    {
        $validated = $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'mobile'          => 'required|string|max:20',
            'content'         => 'required|string|max:500',
        ]);
        // Attempt to send the SMS
        $result = $smsService->send(
            $validated['mobile'],
            $validated['content']
        );

        // Create the SMS record
        $smsMessage = new SmsMessage();
        $smsMessage->organization_id = $validated['organization_id'];
        $smsMessage->user_id = auth()->id();
        $smsMessage->mobile = $validated['mobile'];
        $smsMessage->content = $validated['content'];
        $smsMessage->status = $result['success'] ? 'sent' : 'failed';
        $smsMessage->response = $result['response'] ?? null;
        $smsMessage->sent_at = $result['success'] ? now() : null;
        $smsMessage->save();

        if ($result['success']) {
            return redirect()->route('admin.smsMessages.index')
                ->with('success', 'SMS sent successfully.');
        }

        return redirect()->back()
            ->with('error', 'SMS failed: ' . ($result['error'] ?? 'Unknown error'));
    }

    /**
     * Remove the specified SMS message.
     */
    public function destroy(SmsMessage $smsMessage)
    {
        $smsMessage->delete();
        return redirect()->back()->with('success', 'SMS message deleted.');
    }

    /**
     * Bulk delete SMS messages.
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate(['ids' => 'required|array']);
        SmsMessage::whereIn('id', $request->ids)->delete();
        return redirect()->back()->with('success', 'Selected messages deleted.');
    }
}