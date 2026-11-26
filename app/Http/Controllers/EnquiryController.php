<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class EnquiryController extends Controller
{
    /**
     * Store a newly submitted enquiry.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:191'],
            'phone' => ['nullable', 'string', 'max:20'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ];

        // Only require the reCAPTCHA token when a secret key is configured.
        if (config('services.recaptcha.secret_key')) {
            $rules['g-recaptcha-response'] = ['required', 'string'];
        }

        $validated = $request->validate($rules);

        $this->verifyRecaptcha($request->input('g-recaptcha-response'));

        Enquiry::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'subject' => $validated['subject'] ?? null,
            'message' => $validated['message'],
            'ip_address' => $request->ip(),
            'status' => 'new',
        ]);

        // Pass a translation key; the frontend translates it via $t().
        return back()->with('status', 'enquiry.success');
    }

    /**
     * Verify the reCAPTCHA token with Google.
     *
     * @param  string|null  $token
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function verifyRecaptcha(?string $token)
    {
        $secret = config('services.recaptcha.secret_key');

        // If no secret key is configured, skip verification (dev convenience).
        if (empty($secret)) {
            return;
        }

        if (empty($token)) {
            throw ValidationException::withMessages([
                'g-recaptcha-response' => ['enquiry.recaptcha_failed'],
            ]);
        }

        $response = Http::asForm()->post(
            'https://www.google.com/recaptcha/api/siteverify',
            [
                'secret' => $secret,
                'response' => $token,
                'remoteip' => request()->ip(),
            ]
        );

        $result = $response->json();

        if (empty($result['success'])) {
            throw ValidationException::withMessages([
                'g-recaptcha-response' => ['enquiry.recaptcha_failed'],
            ]);
        }
    }
}
