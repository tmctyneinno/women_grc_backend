@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 bg-white border-b border-gray-200">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Admin Dashboard</h1>
            <span class="px-3 py-1 text-sm font-semibold rounded-full 
                         {{ Auth::guard('admin')->user()->is_active 
                            ? 'bg-green-100 text-green-800' 
                            : 'bg-red-100 text-red-800' }}">
                {{ Auth::guard('admin')->user()->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-indigo-50 border border-indigo-100 rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-gray-900">Welcome</h3>
                        <p class="text-sm text-gray-600">{{ Auth::guard('admin')->user()->name }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-green-50 border border-green-100 rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-gray-900">Email</h3>
                        <p class="text-sm text-gray-600">{{ Auth::guard('admin')->user()->email }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-purple-50 border border-purple-100 rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-gray-900">Role</h3>
                        <p class="text-sm text-gray-600 capitalize">{{ Auth::guard('admin')->user()->role }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Admin Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Account Created:</p>
                    <p class="text-sm font-medium text-gray-900">
                        {{ Auth::guard('admin')->user()->created_at->format('M d, Y') }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Last Updated:</p>
                    <p class="text-sm font-medium text-gray-900">
                        {{ Auth::guard('admin')->user()->updated_at->format('M d, Y') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection