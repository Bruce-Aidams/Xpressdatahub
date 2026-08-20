@extends('layouts.admin')
@section('page-title', 'Manual Top-up Requests')
@section('page-description', 'Review and approve manual MTN MoMo top-ups')
@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-black text-slate-800">Manual Top-ups</h1>
        <p class="text-sm text-slate-400 mt-1">Review and approve manual MTN MoMo top-ups</p>
    </div>

    <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                        <th class="px-6 py-4">Agent</th>
                        <th class="px-6 py-4">Amount</th>
                        <th class="px-6 py-4">Sender Name</th>
                        <th class="px-6 py-4">Date</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-sm">
                    @forelse($topups as $topup)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-800">{{ $topup->agent->username }}</div>
                                <div class="text-[11px] text-slate-500">{{ $topup->agent->phone }}</div>
                            </td>
                            <td class="px-6 py-4 font-black text-slate-800">GH&#8373;{{ number_format($topup->amount, 2) }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded text-xs font-semibold">{{ $topup->sender_name ?? 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-4 text-[11px] text-slate-500">{{ $topup->created_at->format('d M Y, h:i A') }}</td>
                            <td class="px-6 py-4 text-center">
                                @if($topup->status === 'pending')
                                    <span class="px-2 py-1 bg-amber-50 text-amber-600 border border-amber-200 rounded-lg text-[10px] font-bold uppercase tracking-wider">Pending</span>
                                @elseif($topup->status === 'verified')
                                    <span class="px-2 py-1 bg-emerald-50 text-emerald-600 border border-emerald-200 rounded-lg text-[10px] font-bold uppercase tracking-wider">Verified</span>
                                @else
                                    <span class="px-2 py-1 bg-red-50 text-red-600 border border-red-200 rounded-lg text-[10px] font-bold uppercase tracking-wider">Failed</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                @if($topup->status === 'pending')
                                    <form method="POST" action="{{ route('admin.manual-topups.approve', $topup) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="p-2 bg-emerald-50 text-emerald-600 hover:bg-emerald-500 hover:text-white rounded-lg transition" title="Approve">
                                            <x-heroicon-o-check-circle class="w-5 h-5" />
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.manual-topups.reject', $topup) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="p-2 bg-red-50 text-red-600 hover:bg-red-500 hover:text-white rounded-lg transition" title="Reject">
                                            <x-heroicon-o-x-circle class="w-5 h-5" />
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                    <x-heroicon-o-inbox class="w-6 h-6 text-slate-400" />
                                </div>
                                <p class="text-sm font-bold text-slate-800">No manual top-ups found</p>
                                <p class="text-xs text-slate-500 mt-1">Pending manual MoMo top-ups will appear here.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($topups->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $topups->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
