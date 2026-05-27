@extends('oxalis::layouts.oxalis')
@section('title','Approve Login')
@section('content')

<div class="text-center mb-4">
  <div class="d-inline-flex align-items-center justify-content-center rounded-3 mb-3"
       style="width:56px;height:56px;background:var(--ox-sf);color:var(--ox);font-size:1.5rem">
    <i class="bi bi-qr-code-scan"></i>
  </div>
  <h5 class="fw-bold mb-1">Approve desktop sign-in?</h5>
  <p class="text-secondary small mb-0">Someone is trying to sign in as <strong>{{ auth()->user()->email }}</strong> on another device.</p>
</div>

<div class="d-flex gap-2 flex-column mb-3">
  <form action="{{ route('oxalis.qr.approve', $token) }}" method="POST">
    @csrf
    <button type="submit" class="btn btn-ox w-100">Yes, approve this sign-in</button>
  </form>
  <a href="{{ config('oxalis.routes.home','/dashboard') }}" class="btn btn-ox-out w-100 text-center">No, cancel</a>
</div>

<p class="text-secondary text-center" style="font-size:.73rem">If you didn't initiate this, your account is safe — just tap Cancel.</p>

@endsection
