@extends('layouts.admin')

@section('title', 'Maintenance Mode - Xpressdatahub Admin')
@section('header', 'Maintenance Mode')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-slate-800 mb-2">System Maintenance Mode</h2>
            <p class="text-slate-500 text-sm mb-6">
                When maintenance mode is enabled, the site will be inaccessible to all users except administrators. 
                Users will see a "Site under maintenance" page. You can still access the admin dashboard.
            </p>

            <form action="{{ route('admin.config.maintenance.toggle') }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl border {{ $isMaintenance ? 'border-red-200' : 'border-slate-200' }}">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $isMaintenance ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600' }}">
                            @if($isMaintenance)
                                <x-heroicon-o-lock-closed class="w-5 h-5" />
                            @else
                                <x-heroicon-o-lock-open class="w-5 h-5" />
                            @endif
                        </div>
                        <div>
                            <p class="font-medium text-slate-800">
                                Current Status: 
                                <span class="{{ $isMaintenance ? 'text-red-600' : 'text-green-600' }}">
                                    {{ $isMaintenance ? 'Enabled (Site Offline)' : 'Disabled (Site Online)' }}
                                </span>
                            </p>
                        </div>
                    </div>
                    
                    <button type="submit" 
                            class="px-5 py-2 rounded-xl text-sm font-semibold transition-all duration-200 
                                   {{ $isMaintenance 
                                        ? 'bg-slate-200 hover:bg-slate-300 text-slate-700' 
                                        : 'bg-red-600 hover:bg-red-700 text-white shadow-sm hover:shadow-md hover:shadow-red-500/20' }}">
                        {{ $isMaintenance ? 'Disable Maintenance' : 'Enable Maintenance' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
