@extends('layouts.user')
@section('title', 'Notifications')
@section('page-title', 'Notifications')
@section('page-description', 'Messages from the admin team')
@section('content')

<div class="max-w-3xl mx-auto">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-sm text-slate-500">
                @if($unreadCount > 0)
                    <span class="font-bold text-[#EA580C]">{{ $unreadCount }}</span> unread notification{{ $unreadCount !== 1 ? 's' : '' }}
                @else
                    All caught up
                @endif
            </p>
        </div>
        @if($unreadCount > 0)
        <form method="POST" action="{{ route('user.notifications.mark-all-read') }}">
            @csrf
            <button type="submit" class="text-xs font-bold text-[#EA580C] hover:text-[#C2410C] transition">
                Mark all as read
            </button>
        </form>
        @endif
    </div>

    {{-- Notifications List --}}
    <div class="space-y-3">
        @forelse($notifications as $notification)
            @php
                $isRead = !empty($notification['read_at']);
                $priority = $notification['priority'] ?? 'normal';
                $priorityColors = [
                    'urgent' => 'border-l-red-500 bg-red-50/30',
                    'high' => 'border-l-amber-500 bg-amber-50/30',
                    'normal' => 'border-l-[#EA580C] bg-white',
                    'low' => 'border-l-slate-300 bg-white',
                ];
                $priorityBadges = [
                    'urgent' => 'bg-red-100 text-red-600',
                    'high' => 'bg-amber-100 text-amber-600',
                    'normal' => 'bg-emerald-100 text-emerald-600',
                    'low' => 'bg-slate-100 text-slate-500',
                ];
            @endphp
            <div class="border border-slate-100/80 rounded-2xl {{ $priorityColors[$priority] ?? $priorityColors['normal'] }} {{ $isRead ? 'opacity-70' : '' }} shadow-sm overflow-hidden border-l-4 transition-all duration-200 hover:shadow-md">
                <div class="p-4 sm:p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3 min-w-0 flex-1">
                            <div class="w-9 h-9 rounded-full {{ $isRead ? 'bg-slate-100' : 'bg-[#EA580C]/10' }} flex items-center justify-center shrink-0">
                                <x-heroicon-o-bell class="w-4 h-4 {{ $isRead ? 'text-slate-400' : 'text-[#EA580C]' }}" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-sm font-bold text-slate-800 truncate">{{ $notification['title'] ?? 'Notification' }}</h3>
                                    <span class="inline-flex px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider {{ $priorityBadges[$priority] ?? $priorityBadges['normal'] }}">
                                        {{ ucfirst($priority) }}
                                    </span>
                                    @if(!$isRead)
                                        <span class="w-2 h-2 rounded-full bg-[#EA580C] shrink-0"></span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">{{ $notification['message'] ?? '' }}</p>
                                <div class="flex items-center gap-3 mt-2.5">
                                    <span class="text-[10px] text-slate-400 font-medium">{{ \Carbon\Carbon::parse($notification['created_at'] ?? now())->diffForHumans() }}</span>
                                    @if(!$isRead)
                                    <a href="{{ route('user.notifications.mark-read', $notification['id']) }}" class="text-[10px] font-bold text-[#EA580C] hover:text-[#C2410C] transition">
                                        Mark as read
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm p-12 text-center">
                <div class="w-14 h-14 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
                    <x-heroicon-o-bell-slash class="w-7 h-7 text-slate-300" />
                </div>
                <p class="text-sm font-bold text-slate-500">No notifications yet</p>
                <p class="text-xs text-slate-400 mt-1">You'll see messages from the admin team here</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
