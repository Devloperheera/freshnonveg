@php
    /** @var \App\Models\User|null $user */
    $user = $user ?? null;
@endphp
@if($user)
    <a href="{{ route('admin.users.show', $user, false) }}" class="adm-user-cell text-decoration-none">
        <div class="adm-user-cell__av">
            @if($user->profile_image)
                <img src="{{ $user->profile_image }}" alt="">
            @else
                <span>{{ strtoupper(substr($user->name, 0, 1)) }}</span>
            @endif
        </div>
        <div class="adm-user-cell__body min-w-0">
            <span class="adm-user-cell__name">{{ $user->name }}</span>
            <div class="adm-user-cell__meta">
                @if($user->phone)<span><i class="bi bi-telephone"></i> {{ $user->phone }}</span>@endif
                @if($user->email)<span><i class="bi bi-envelope"></i> {{ $user->email }}</span>@endif
            </div>
            <div class="adm-user-cell__meta text-muted">ID #{{ $user->id }}</div>
        </div>
    </a>
@else
    <span class="text-muted">—</span>
@endif
