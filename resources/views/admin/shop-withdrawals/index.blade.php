�@extends('layouts.admin')
@section('page-title', 'Shop Withdrawals')
@section('page-description', 'Process shop withdrawal requests')
@section('content')
<div class="bg-white border border-slate-100 shadow-sm rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-slate-100">
                <th class="text-left px-5 py-3 text-xs text-slate-400 font-medium">ID</th>
                <th class="text-left px-5 py-3 text-xs text-slate-400 font-medium">Shop</th>
                <th class="text-left px-5 py-3 text-xs text-slate-400 font-medium">Amount</th>
                <th class="text-left px-5 py-3 text-xs text-slate-400 font-medium">Status</th>
                <th class="text-left px-5 py-3 text-xs text-slate-400 font-medium">Date</th>
                <th class="text-left px-5 py-3 text-xs text-slate-400 font-medium">Actions</th>
            </tr></thead>
            <tbody>
                @forelse($withdrawals as $w)
                    <tr class="border-b border-slate-100 hover:bg-blue-50/20">
                        <td class="px-5 py-3 text-slate-800">#{{ $w->id }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ $w->shop->name ?? 'N/A' }}</td>
                        <td class="px-5 py-3 text-slate-600">GH&#8373;{{ number_format($w->amount, 2) }}</td>
                        <td class="px-5 py-3"><x-status-badge :status="$w->status" /></td>
                        <td class="px-5 py-3 text-slate-400 text-xs">{{ $w->created_at?->format('M d, Y H:i') ?? 'N/A' }}</td>
                        <td class="px-5 py-3 flex gap-2">
                            @if($w->status === 'pending')
                                <form method="POST" action="{{ route('admin.shop-withdrawals.approve', $w->id) }}" class="inline">@csrf
                                    <button type="submit" class="text-emerald-500 hover:text-emerald-600 text-xs"><x-heroicon-o-check class="w-4 h-4" />Approve</button>
                                </form>
                                <form method="POST" action="{{ route('admin.shop-withdrawals.reject', $w->id) }}" class="inline">@csrf
                                    <button type="submit" class="text-red-500 hover:text-red-600 text-xs"><x-heroicon-o-x-mark class="w-4 h-4" />Reject</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-slate-500 text-sm">No withdrawal requests</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-slate-100">{{ $withdrawals->links('pagination::tailwind') }}</div>
</div>
@endsection
