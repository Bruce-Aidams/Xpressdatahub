@extends('layouts.admin')
@section('page-title', 'User Activity Logs')
@section('page-description', 'View user login and activity history')
@section('content')
<div class="bg-white border border-slate-100 shadow-sm rounded-2xl overflow-hidden">
    <div class="p-5 border-b border-slate-100">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search user..." class="flex-1 min-w-[200px] px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:border-[#2563EB] outline-none">
            <button type="submit" class="px-4 py-2 bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-sm font-medium rounded-xl transition"><x-heroicon-o-magnifying-glass class="w-5 h-5" /></button>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-slate-100">
                <th class="text-left px-5 py-3 text-xs text-slate-400 font-medium">User</th>
                <th class="text-left px-5 py-3 text-xs text-slate-400 font-medium">IP Address</th>
                <th class="text-left px-5 py-3 text-xs text-slate-400 font-medium">Device</th>
                <th class="text-left px-5 py-3 text-xs text-slate-400 font-medium">Action</th>
                <th class="text-left px-5 py-3 text-xs text-slate-400 font-medium">Time</th>
            </tr></thead>
            <tbody>
                @forelse($activities as $activity)
                    <tr class="border-b border-slate-100 hover:bg-blue-50/20">
                        <td class="px-5 py-3 text-slate-600">{{ $activity->agent->username ?? 'N/A' }}</td>
                        <td class="px-5 py-3 text-slate-600 font-mono text-xs">{{ $activity->ip_address ?? 'N/A' }}</td>
                        <td class="px-5 py-3 text-slate-400 text-xs">{{ $activity->user_agent ?? 'N/A' }}</td>
                        <td class="px-5 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-600">{{ $activity->action ?? 'login' }}</span></td>
                        <td class="px-5 py-3 text-slate-400 text-xs">{{ $activity->created_at?->format('M d, Y H:i') ?? 'N/A' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-slate-500 text-sm">No activity logs found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-slate-100">{{ $activities->links('pagination::tailwind') }}</div>
</div>
@endsection
