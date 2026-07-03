@extends('layouts.admin')
@section('page-title', 'Send Notification')
@section('page-description', 'Broadcast a message to your agent network')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-black text-slate-800">Send Notification</h1>
    <p class="text-sm text-slate-400 mt-1">Compose and broadcast a message to your agent network</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left Column: Form --}}
    <div class="lg:col-span-2">
        <div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm overflow-hidden">

            {{-- Section Header --}}
            <div class="px-6 py-5 border-b border-slate-100/80">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#2563EB]/10 flex items-center justify-center">
                        <x-heroicon-o-paper-airplane class="w-5 h-5 text-[#2563EB]" />
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-slate-800">New Notification</h3>
                        <p class="text-xs text-slate-400">Fill in the details below to send</p>
                    </div>
                </div>
            </div>

            {{-- Form Body --}}
            <form method="POST" action="{{ route('admin.notifications.send') }}" class="p-6 space-y-5">
                @csrf

                {{-- Title --}}
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Title *</label>
                    <input type="text" name="title" required value="{{ old('title') }}"
                           placeholder="e.g. Scheduled Maintenance Notice"
                           id="inputTitle"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:border-[#2563EB] focus:bg-white transition @error('title') border-red-400 @enderror">
                    @error('title')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                {{-- Message --}}
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Message *</label>
                    <textarea name="message" required rows="5"
                              placeholder="Write your notification message here..."
                              id="inputMessage"
                              class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:border-[#2563EB] focus:bg-white transition resize-none @error('message') border-red-400 @enderror">{{ old('message') }}</textarea>
                    @error('message')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                {{-- Target & Priority --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Target Audience</label>
                        <select name="recipient_type"
                                id="inputTarget"
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:border-[#2563EB] transition">
                            <option value="all">All Users</option>
                            <option value="agents">Agents Only</option>
                            <option value="super_agents">Super Agents</option>
                            <option value="dealers">Dealers</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Priority Level</label>
                        <select name="priority"
                                id="inputPriority"
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:border-[#2563EB] transition">
                            <option value="normal">Normal</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="pt-2 flex items-center gap-3">
                    <button type="submit"
                            class="flex-1 py-2.5 bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-sm font-bold rounded-xl px-6 transition shadow-md shadow-blue-500/10 flex items-center justify-center gap-2">
                        <x-heroicon-o-paper-airplane class="w-4 h-4" /> Send Notification
                    </button>
                    <a href="{{ route('admin.dashboard') }}"
                       class="px-5 py-2.5 border border-slate-200 text-slate-600 text-sm font-bold rounded-xl hover:bg-slate-50 transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Right Column: Tips + Preview --}}
    <div class="lg:col-span-1 space-y-6">

        {{-- Tips Card --}}
        <div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm">
            <div class="px-5 py-4 border-b border-slate-100/80">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
                        <x-heroicon-o-bell class="w-4 h-4 text-amber-500" />
                    </div>
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-400">Tips</h4>
                </div>
            </div>
            <div class="p-5">
                <ul class="space-y-3">
                    <li class="flex items-start gap-2.5 text-xs text-slate-500 leading-relaxed">
                        <x-heroicon-o-check-circle class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5" />
                        <span>Keep titles short and actionable — ideally under 50 characters.</span>
                    </li>
                    <li class="flex items-start gap-2.5 text-xs text-slate-500 leading-relaxed">
                        <x-heroicon-o-check-circle class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5" />
                        <span>Use <strong class="text-slate-700">Urgent</strong> priority sparingly — reserve it for critical system alerts.</span>
                    </li>
                    <li class="flex items-start gap-2.5 text-xs text-slate-500 leading-relaxed">
                        <x-heroicon-o-check-circle class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5" />
                        <span>Target specific roles to avoid spamming all users for role-specific updates.</span>
                    </li>
                    <li class="flex items-start gap-2.5 text-xs text-slate-500 leading-relaxed">
                        <x-heroicon-o-check-circle class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5" />
                        <span>Messages support plain text. Keep them concise for best readability.</span>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Preview Card --}}
        <div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm">
            <div class="px-5 py-4 border-b border-slate-100/80">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                        <x-heroicon-o-eye class="w-4 h-4 text-blue-500" />
                    </div>
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-400">Live Preview</h4>
                </div>
            </div>
            <div class="p-5">
                <div class="flex items-start gap-3 p-3.5 bg-slate-50 rounded-xl border border-slate-100">
                    <div class="w-9 h-9 rounded-full bg-[#2563EB] flex items-center justify-center text-white shrink-0 shadow-sm shadow-blue-200">
                        <x-heroicon-o-bell class="w-5 h-5" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-slate-800 truncate" id="previewTitle">Notification Title</p>
                        <p class="text-xs text-slate-500 mt-1 line-clamp-2" id="previewMsg">Your message will appear here as you type...</p>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-600" id="previewPriority">Normal</span>
                            <span class="text-[10px] text-slate-400" id="previewTarget">All Users</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const inputTitle = document.getElementById('inputTitle');
    const inputMessage = document.getElementById('inputMessage');
    const inputPriority = document.getElementById('inputPriority');
    const inputTarget = document.getElementById('inputTarget');
    const previewTitle = document.getElementById('previewTitle');
    const previewMsg = document.getElementById('previewMsg');
    const previewPriority = document.getElementById('previewPriority');
    const previewTarget = document.getElementById('previewTarget');

    const priorityMap = {
        normal: { label: 'Normal', class: 'bg-emerald-50 text-emerald-600' },
        high: { label: 'High', class: 'bg-amber-50 text-amber-600' },
        urgent: { label: 'Urgent', class: 'bg-red-50 text-red-600' }
    };

    const targetLabels = {
        all: 'All Users',
        agents: 'Agents Only',
        super_agents: 'Super Agents',
        dealers: 'Dealers'
    };

    inputTitle.addEventListener('input', function() {
        previewTitle.textContent = this.value || 'Notification Title';
    });

    inputMessage.addEventListener('input', function() {
        previewMsg.textContent = this.value || 'Your message will appear here as you type...';
    });

    inputPriority.addEventListener('change', function() {
        const p = priorityMap[this.value];
        previewPriority.textContent = p.label;
        previewPriority.className = 'inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider ' + p.class;
    });

    inputTarget.addEventListener('change', function() {
        previewTarget.textContent = targetLabels[this.value] || 'All Users';
    });
</script>
@endpush
@endsection
