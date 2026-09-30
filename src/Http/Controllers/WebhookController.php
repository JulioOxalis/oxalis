<?php
namespace Oxalis\Http\Controllers;

use Oxalis\Models\Webhook;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class WebhookController extends Controller
{
    public function index()
    {
        $webhooks = Webhook::latest()->get();
        $availableEvents = ['login', 'register', 'logout', 'password_reset', 'email_change', 'account_deleted', 'totp_enabled', '*'];
        return view('oxalis::admin.webhooks', compact('webhooks', 'availableEvents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'url'    => 'required|url|max:500',
            'events' => 'required|array|min:1',
            'note'   => 'nullable|string|max:100',
        ]);

        Webhook::create([
            'url'    => $data['url'],
            'events' => $data['events'],
            'note'   => $data['note'] ?? null,
            'secret' => Webhook::generateSecret(),
            'active' => true,
        ]);

        return back()->with('admin_success', 'Webhook created.');
    }

    public function toggle(Request $request)
    {
        $request->validate(['webhook_id' => 'required']);
        $hook = Webhook::findOrFail($request->webhook_id);
        $hook->update(['active' => !$hook->active, 'failures' => 0]);
        return back()->with('admin_success', $hook->active ? 'Webhook enabled.' : 'Webhook disabled.');
    }

    public function destroy(Request $request)
    {
        $request->validate(['webhook_id' => 'required']);
        Webhook::find($request->webhook_id)?->delete();
        return back()->with('admin_success', 'Webhook deleted.');
    }
}
