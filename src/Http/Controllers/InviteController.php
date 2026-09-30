<?php
namespace Oxalis\Http\Controllers;

use Oxalis\Models\Invite;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class InviteController extends Controller
{
    public function index()
    {
        $invites = Invite::latest()->paginate(30);
        return view('oxalis::admin.invites', compact('invites'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'note'          => 'nullable|string|max:100',
            'max_uses'      => 'required|integer|min:1|max:1000',
            'expires_days'  => 'nullable|integer|min:1|max:365',
        ]);

        Invite::generate(
            note:         $data['note'] ?? null,
            maxUses:      (int) $data['max_uses'],
            expiresInDays: isset($data['expires_days']) ? (int) $data['expires_days'] : null,
        );

        return back()->with('admin_success', 'Invite code generated.');
    }

    public function destroy(Request $request)
    {
        $request->validate(['invite_id' => 'required']);
        Invite::find($request->invite_id)?->delete();
        return back()->with('admin_success', 'Invite deleted.');
    }
}
