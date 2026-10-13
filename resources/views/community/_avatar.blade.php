@php
    $size = $size ?? 44; // ขนาดเริ่มต้น 44px ถ้าไม่ได้ส่งมา
@endphp

@if ($user->profile?->avatar)
    <img src="{{ asset('storage/'.$user->profile->avatar) }}" alt="{{ $user->name }}" title="{{ $user->name }}"
         class="avatar" style="width: {{ $size }}px; height: {{ $size }}px;">
@else
    <span class="avatar avatar-fallback" title="{{ $user->name }}"
          style="width: {{ $size }}px; height: {{ $size }}px; font-size: {{ round($size * 0.42) }}px;">
        {{ mb_substr($user->name, 0, 1) }}
    </span>
@endif
