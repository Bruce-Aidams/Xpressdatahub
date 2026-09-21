@extends('layouts.user')

@section('title', 'Create Shop')
@section('page-title', 'Create Your Shop')
@section('page-description', 'Set up your own data storefront and start earning.')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 sm:px-6 py-5 border-b border-slate-100 flex items-center gap-4 bg-slate-50/50">
            <div class="w-12 h-12 rounded-xl bg-[#EA580C]/10 flex items-center justify-center shrink-0">
                <x-heroicon-o-building-storefront class="w-6 h-6 text-[#EA580C]" />
            </div>
            <div>
                <h2 class="text-lg font-bold text-slate-800">Shop Setup</h2>
                <p class="text-xs text-slate-500 mt-0.5">Fill in the details below to launch your personal storefront.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('user.shop.store') }}" class="p-5 sm:p-6 space-y-5">
            @csrf
            
            {{-- Shop Name --}}
            <div>
                <label for="name" class="block text-xs font-semibold text-slate-600 mb-1.5">Shop Name <span class="text-red-500">*</span></label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    required
                    value="{{ old('name') }}"
                    placeholder="Enter your desired shop name"
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-300 focus:border-[#EA580C] focus:ring-1 focus:ring-[#EA580C]/20 outline-none transition-all"
                >
                @error('name')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Description --}}
            <div>
                <label for="description" class="block text-xs font-semibold text-slate-600 mb-1.5">Description</label>
                <textarea
                    id="description"
                    name="description"
                    rows="3"
                    placeholder="Describe what your shop offers..."
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-300 focus:border-[#EA580C] focus:ring-1 focus:ring-[#EA580C]/20 outline-none transition-all resize-none"
                >{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                {{-- WhatsApp Number --}}
                <div>
                    <label for="whatsapp_number" class="block text-xs font-semibold text-slate-600 mb-1.5">WhatsApp Number</label>
                    <input
                        type="text"
                        id="whatsapp_number"
                        name="whatsapp_number"
                        value="{{ old('whatsapp_number') }}"
                        placeholder="+233 XX XXX XXXX"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-300 focus:border-[#EA580C] focus:ring-1 focus:ring-[#EA580C]/20 outline-none transition-all"
                    >
                    @error('whatsapp_number')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- WhatsApp Group Link --}}
                <div>
                    <label for="whatsapp_group_link" class="block text-xs font-semibold text-slate-600 mb-1.5">WhatsApp Group Link</label>
                    <input
                        type="url"
                        id="whatsapp_group_link"
                        name="whatsapp_group_link"
                        value="{{ old('whatsapp_group_link') }}"
                        placeholder="https://chat.whatsapp.com/..."
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-300 focus:border-[#EA580C] focus:ring-1 focus:ring-[#EA580C]/20 outline-none transition-all"
                    >
                    @error('whatsapp_group_link')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="pt-2">
                <button
                    type="submit"
                    class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-6 py-3 bg-[#EA580C] hover:bg-[#C2410C] text-white text-sm font-semibold rounded-xl transition-all duration-200 shadow-sm shadow-orange-500/15"
                >
                    <x-heroicon-o-plus-circle class="w-5 h-5" />
                    Create Shop
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
