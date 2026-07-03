@extends('layouts.admin')

@section('page-title', 'Banner Notifications')
@section('page-description', 'Manage scrolling banner notifications')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-black text-slate-800">Banner Notifications</h1>
    <p class="text-sm text-slate-400 mt-1">Create and manage scrolling banners displayed across the site</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Add Banner Form --}}
    <div class="lg:col-span-1">
        <div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100/80">
                <div class="flex items-center gap-2.5">
                    <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-[#2563EB]/10">
                        <x-heroicon-o-megaphone class="w-5 h-5 text-[#2563EB]" />
                    </div>
                    <h3 class="text-sm font-bold text-slate-800">Add Banner</h3>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.banners.store') }}" class="p-6 space-y-5">
                @csrf

                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1.5">Title</label>
                    <input
                        type="text"
                        name="title"
                        class="w-full px-3.5 py-2.5 bg-slate-50/80 border border-slate-200/80 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition"
                        placeholder="e.g. System Maintenance Notice"
                    >
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1.5">Message *</label>
                    <textarea
                        name="message"
                        required
                        rows="3"
                        class="w-full px-3.5 py-2.5 bg-slate-50/80 border border-slate-200/80 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition"
                        placeholder="Enter banner message..."
                    ></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1.5">Background</label>
                        <div class="flex items-center gap-2 mb-2">
                            <input type="color" name="background_color" id="bgColorPicker" value="#1e40af" class="w-10 h-10 rounded-lg border border-slate-200/80 cursor-pointer">
                            <input type="text" name="background_color_hex" id="bgColorHex" value="#1e40af" class="flex-1 px-3 py-2 bg-slate-50/80 border border-slate-200/80 rounded-xl text-xs text-slate-600 font-mono focus:border-[#2563EB] outline-none transition">
                        </div>
                        <div class="flex flex-wrap gap-1.5">
                            <button type="button" onclick="setBgColor('#1e40af')" class="w-6 h-6 rounded-full bg-[#1e40af] border-2 border-white shadow ring-1 ring-slate-200 hover:scale-110 transition" title="Blue"></button>
                            <button type="button" onclick="setBgColor('#166534')" class="w-6 h-6 rounded-full bg-[#166534] border-2 border-white shadow ring-1 ring-slate-200 hover:scale-110 transition" title="Green"></button>
                            <button type="button" onclick="setBgColor('#7c2d12')" class="w-6 h-6 rounded-full bg-[#7c2d12] border-2 border-white shadow ring-1 ring-slate-200 hover:scale-110 transition" title="Orange"></button>
                            <button type="button" onclick="setBgColor('#581c87')" class="w-6 h-6 rounded-full bg-[#581c87] border-2 border-white shadow ring-1 ring-slate-200 hover:scale-110 transition" title="Purple"></button>
                            <button type="button" onclick="setBgColor('#991b1b')" class="w-6 h-6 rounded-full bg-[#991b1b] border-2 border-white shadow ring-1 ring-slate-200 hover:scale-110 transition" title="Red"></button>
                            <button type="button" onclick="setBgColor('#1e293b')" class="w-6 h-6 rounded-full bg-[#1e293b] border-2 border-white shadow ring-1 ring-slate-200 hover:scale-110 transition" title="Dark"></button>
                            <button type="button" onclick="setBgColor('#0f766e')" class="w-6 h-6 rounded-full bg-[#0f766e] border-2 border-white shadow ring-1 ring-slate-200 hover:scale-110 transition" title="Teal"></button>
                            <button type="button" onclick="setBgColor('#be185d')" class="w-6 h-6 rounded-full bg-[#be185d] border-2 border-white shadow ring-1 ring-slate-200 hover:scale-110 transition" title="Pink"></button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1.5">Text Color</label>
                        <div class="flex items-center gap-2 mb-2">
                            <input type="color" name="text_color" id="txtColorPicker" value="#ffffff" class="w-10 h-10 rounded-lg border border-slate-200/80 cursor-pointer">
                            <input type="text" name="text_color_hex" id="txtColorHex" value="#ffffff" class="flex-1 px-3 py-2 bg-slate-50/80 border border-slate-200/80 rounded-xl text-xs text-slate-600 font-mono focus:border-[#2563EB] outline-none transition">
                        </div>
                        <div class="flex flex-wrap gap-1.5">
                            <button type="button" onclick="setTxtColor('#ffffff')" class="w-6 h-6 rounded-full bg-[#ffffff] border-2 border-white shadow ring-1 ring-slate-200 hover:scale-110 transition" title="White"></button>
                            <button type="button" onclick="setTxtColor('#f8fafc')" class="w-6 h-6 rounded-full bg-[#f8fafc] border-2 border-slate-200 shadow ring-1 ring-slate-200 hover:scale-110 transition" title="Light"></button>
                            <button type="button" onclick="setTxtColor('#0f172a')" class="w-6 h-6 rounded-full bg-[#0f172a] border-2 border-white shadow ring-1 ring-slate-200 hover:scale-110 transition" title="Black"></button>
                            <button type="button" onclick="setTxtColor('#fbbf24')" class="w-6 h-6 rounded-full bg-[#fbbf24] border-2 border-white shadow ring-1 ring-slate-200 hover:scale-110 transition" title="Yellow"></button>
                            <button type="button" onclick="setTxtColor('#34d399')" class="w-6 h-6 rounded-full bg-[#34d399] border-2 border-white shadow ring-1 ring-slate-200 hover:scale-110 transition" title="Emerald"></button>
                            <button type="button" onclick="setTxtColor('#60a5fa')" class="w-6 h-6 rounded-full bg-[#60a5fa] border-2 border-white shadow ring-1 ring-slate-200 hover:scale-110 transition" title="Blue"></button>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1.5">Speed</label>
                    <div class="flex items-center gap-3">
                        <input type="range" name="speed" min="1" max="100" value="50" class="flex-1 h-1.5 bg-slate-200 rounded-full appearance-none cursor-pointer accent-[#2563EB]">
                        <span class="text-xs text-slate-400 font-medium w-8 text-right">50</span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1.5">Status</label>
                    <div class="flex items-center gap-3">
                        <button type="button" role="switch" aria-checked="true" id="statusToggle" class="relative inline-flex h-6 w-11 items-center rounded-full bg-[#2563EB] transition-colors focus:outline-none focus:ring-2 focus:ring-[#2563EB]/30 focus:ring-offset-2">
                            <span class="inline-block h-4 w-4 transform translate-x-6 rounded-full bg-white shadow-sm transition-transform"></span>
                        </button>
                        <input type="hidden" name="is_enabled" value="1" id="statusInput">
                        <span class="text-sm text-slate-600 font-medium" id="statusLabel">Active</span>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-sm font-bold rounded-xl px-6 py-2.5 transition inline-flex items-center justify-center gap-2">
                        <x-heroicon-o-plus class="w-4 h-4" />
                        Add Banner
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Banner List --}}
    <div class="lg:col-span-2">
        <div class="bg-white border border-slate-100/80 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100/80 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-blue-500/10">
                        <x-heroicon-o-bars-3 class="w-5 h-5 text-blue-500" />
                    </div>
                    <h3 class="text-sm font-bold text-slate-800">Active Banners</h3>
                </div>
                <span class="inline-flex items-center justify-center min-w-[1.75rem] h-7 px-2.5 text-xs font-bold text-slate-600 bg-slate-100 rounded-full">{{ $banners->count() }}</span>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($banners as $banner)
                    @php
                        $bannerData = is_array($banner->data) ? $banner->data : (json_decode($banner->data, true) ?? []);
                        $bgColor = $bannerData['background_color'] ?? '#1e40af';
                        $textColor = $bannerData['text_color'] ?? '#ffffff';
                        $speed = $bannerData['speed'] ?? 50;
                    @endphp
                    <div class="px-6 py-4 flex items-center justify-between hover:bg-slate-50/50 transition-colors">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="flex-shrink-0 w-3 h-3 rounded-full ring-2 ring-white shadow-sm" style="background-color: {{ $bgColor }}"></span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-slate-800 truncate">{{ $banner->title ?: 'Untitled Banner' }}</p>
                                <p class="text-xs text-slate-400 mt-0.5 truncate">{{ $banner->message }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 ml-4">
                            {{-- Toggle button --}}
                            <form method="POST" action="{{ route('admin.banners.toggle', $banner->id) }}" class="inline">
                                @csrf
                                <button type="submit" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none {{ $banner->is_active ? 'bg-[#2563EB]' : 'bg-slate-300' }}" title="{{ $banner->is_active ? 'Deactivate' : 'Activate' }}">
                                    <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow-sm transition-transform {{ $banner->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                </button>
                            </form>

                            {{-- Edit button --}}
                            <button onclick="openEditModal({{ $banner->id }}, '{{ addslashes($banner->title ?? '') }}', '{{ addslashes($banner->message) }}', '{{ $bgColor }}', '{{ $textColor }}', {{ $speed }})" class="p-2 text-slate-400 hover:text-[#2563EB] hover:bg-blue-50 rounded-xl transition" title="Edit banner">
                                <x-heroicon-o-pencil class="w-4 h-4" />
                            </button>

                            {{-- Delete button --}}
                            <form method="POST" action="{{ route('admin.banners.destroy', $banner->id) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Delete this banner?')" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition" title="Delete banner">
                                    <x-heroicon-o-trash class="w-4 h-4" />
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center">
                        <div class="flex items-center justify-center w-12 h-12 mx-auto rounded-full bg-slate-100 mb-4">
                            <x-heroicon-o-megaphone class="w-6 h-6 text-slate-400" />
                        </div>
                        <p class="text-sm font-medium text-slate-500">No banners yet</p>
                        <p class="text-xs text-slate-400 mt-1">Create your first banner using the form</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Edit Banner Modal --}}
<div id="editBannerModal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeEditModal()"></div>
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl">
            <form id="editBannerForm" method="POST" class="p-6 space-y-5">
                @csrf
                @method('PUT')
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2.5">
                        <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-[#2563EB]/10">
                            <x-heroicon-o-pencil class="w-5 h-5 text-[#2563EB]" />
                        </div>
                        <h3 class="text-sm font-bold text-slate-800">Edit Banner</h3>
                    </div>
                    <button type="button" onclick="closeEditModal()" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1.5">Title</label>
                    <input type="text" name="title" id="editTitle"
                           class="w-full px-3.5 py-2.5 bg-slate-50/80 border border-slate-200/80 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition"
                           placeholder="e.g. System Maintenance Notice">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1.5">Message *</label>
                    <textarea name="message" id="editMessage" required rows="3"
                              class="w-full px-3.5 py-2.5 bg-slate-50/80 border border-slate-200/80 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10 outline-none transition"
                              placeholder="Enter banner message..."></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1.5">Background</label>
                        <div class="flex items-center gap-2 mb-2">
                            <input type="color" name="background_color" id="editBgColorPicker" value="#1e40af" class="w-10 h-10 rounded-lg border border-slate-200/80 cursor-pointer">
                            <input type="text" name="background_color_hex" id="editBgColorHex" value="#1e40af" class="flex-1 px-3 py-2 bg-slate-50/80 border border-slate-200/80 rounded-xl text-xs text-slate-600 font-mono focus:border-[#2563EB] outline-none transition">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1.5">Text Color</label>
                        <div class="flex items-center gap-2 mb-2">
                            <input type="color" name="text_color" id="editTxtColorPicker" value="#ffffff" class="w-10 h-10 rounded-lg border border-slate-200/80 cursor-pointer">
                            <input type="text" name="text_color_hex" id="editTxtColorHex" value="#ffffff" class="flex-1 px-3 py-2 bg-slate-50/80 border border-slate-200/80 rounded-xl text-xs text-slate-600 font-mono focus:border-[#2563EB] outline-none transition">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1.5">Speed</label>
                    <div class="flex items-center gap-3">
                        <input type="range" name="speed" id="editSpeed" min="1" max="100" value="50" class="flex-1 h-1.5 bg-slate-200 rounded-full appearance-none cursor-pointer accent-[#2563EB]">
                        <span class="text-xs text-slate-400 font-medium w-8 text-right" id="editSpeedLabel">50</span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1.5">Status</label>
                    <div class="flex items-center gap-3">
                        <button type="button" role="switch" aria-checked="true" id="editStatusToggle" class="relative inline-flex h-6 w-11 items-center rounded-full bg-[#2563EB] transition-colors focus:outline-none focus:ring-2 focus:ring-[#2563EB]/30 focus:ring-offset-2">
                            <span class="inline-block h-4 w-4 transform translate-x-6 rounded-full bg-white shadow-sm transition-transform"></span>
                        </button>
                        <input type="hidden" name="is_enabled" value="1" id="editStatusInput">
                        <span class="text-sm text-slate-600 font-medium" id="editStatusLabel">Active</span>
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeEditModal()" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold rounded-xl px-6 py-2.5 transition">Cancel</button>
                    <button type="submit" class="flex-1 bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-sm font-bold rounded-xl px-6 py-2.5 transition">Update Banner</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Create form toggles
    const toggle = document.getElementById('statusToggle');
    const input = document.getElementById('statusInput');
    const label = document.getElementById('statusLabel');

    toggle.addEventListener('click', () => {
        const isChecked = toggle.getAttribute('aria-checked') === 'true';
        toggle.setAttribute('aria-checked', !isChecked);
        const span = toggle.querySelector('span');
        if (!isChecked) {
            toggle.classList.add('bg-[#2563EB]');
            toggle.classList.remove('bg-slate-300');
            span.classList.add('translate-x-6');
            span.classList.remove('translate-x-1');
            input.value = '1';
            label.textContent = 'Active';
        } else {
            toggle.classList.remove('bg-[#2563EB]');
            toggle.classList.add('bg-slate-300');
            span.classList.remove('translate-x-6');
            span.classList.add('translate-x-1');
            input.value = '0';
            label.textContent = 'Inactive';
        }
    });

    // Create form color pickers
    function setBgColor(hex) {
        document.getElementById('bgColorPicker').value = hex;
        document.getElementById('bgColorHex').value = hex;
    }
    function setTxtColor(hex) {
        document.getElementById('txtColorPicker').value = hex;
        document.getElementById('txtColorHex').value = hex;
    }
    document.getElementById('bgColorPicker').addEventListener('input', function() {
        document.getElementById('bgColorHex').value = this.value;
    });
    document.getElementById('bgColorHex').addEventListener('input', function() {
        if (/^#[0-9a-f]{6}$/i.test(this.value)) {
            document.getElementById('bgColorPicker').value = this.value;
        }
    });
    document.getElementById('txtColorPicker').addEventListener('input', function() {
        document.getElementById('txtColorHex').value = this.value;
    });
    document.getElementById('txtColorHex').addEventListener('input', function() {
        if (/^#[0-9a-f]{6}$/i.test(this.value)) {
            document.getElementById('txtColorPicker').value = this.value;
        }
    });

    // Edit modal
    function openEditModal(id, title, message, bgColor, txtColor, speed) {
        document.getElementById('editBannerForm').action = '{{ url(config("app.admin_path") . "/banners") }}/' + id;
        document.getElementById('editTitle').value = title;
        document.getElementById('editMessage').value = message;
        document.getElementById('editBgColorPicker').value = bgColor;
        document.getElementById('editBgColorHex').value = bgColor;
        document.getElementById('editTxtColorPicker').value = txtColor;
        document.getElementById('editTxtColorHex').value = txtColor;
        document.getElementById('editSpeed').value = speed;
        document.getElementById('editSpeedLabel').textContent = speed;
        document.getElementById('editBannerModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeEditModal() {
        document.getElementById('editBannerModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    // Edit modal toggles
    const editToggle = document.getElementById('editStatusToggle');
    const editInput = document.getElementById('editStatusInput');
    const editLabel = document.getElementById('editStatusLabel');

    editToggle.addEventListener('click', () => {
        const isChecked = editToggle.getAttribute('aria-checked') === 'true';
        editToggle.setAttribute('aria-checked', !isChecked);
        const span = editToggle.querySelector('span');
        if (!isChecked) {
            editToggle.classList.add('bg-[#2563EB]');
            editToggle.classList.remove('bg-slate-300');
            span.classList.add('translate-x-6');
            span.classList.remove('translate-x-1');
            editInput.value = '1';
            editLabel.textContent = 'Active';
        } else {
            editToggle.classList.remove('bg-[#2563EB]');
            editToggle.classList.add('bg-slate-300');
            span.classList.remove('translate-x-6');
            span.classList.add('translate-x-1');
            editInput.value = '0';
            editLabel.textContent = 'Inactive';
        }
    });

    // Edit speed slider
    document.getElementById('editSpeed').addEventListener('input', function() {
        document.getElementById('editSpeedLabel').textContent = this.value;
    });

    // Edit modal color pickers
    document.getElementById('editBgColorPicker').addEventListener('input', function() {
        document.getElementById('editBgColorHex').value = this.value;
    });
    document.getElementById('editBgColorHex').addEventListener('input', function() {
        if (/^#[0-9a-f]{6}$/i.test(this.value)) {
            document.getElementById('editBgColorPicker').value = this.value;
        }
    });
    document.getElementById('editTxtColorPicker').addEventListener('input', function() {
        document.getElementById('editTxtColorHex').value = this.value;
    });
    document.getElementById('editTxtColorHex').addEventListener('input', function() {
        if (/^#[0-9a-f]{6}$/i.test(this.value)) {
            document.getElementById('editTxtColorPicker').value = this.value;
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeEditModal();
    });
</script>
@endpush
@endsection
