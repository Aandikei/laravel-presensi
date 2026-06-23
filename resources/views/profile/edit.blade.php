{{-- <x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout> --}}

<x-layouts.admin>
    <x-slot:title>Profile</x-slot:title>

    <div class="container px-6 py-8 mx-auto">
        <x-breadcrumb :items="[
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Profile'],
        ]" />
        <h2 class="mt-4 mb-8 text-2xl font-semibold text-gray-700 dark:text-gray-200">
            Profile
        </h2>

        <div class="grid gap-6">

            {{-- Update Profile Info --}}
            <div class="p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
                <h3 class="mb-1 text-lg font-semibold text-gray-700 dark:text-gray-200">
                    Profile Information
                </h3>
                <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">
                    Update nama dan email kamu.
                </p>
                @include('profile.partials.update-profile-information-form')
            </div>

            {{-- Update Password --}}
            <div class="p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
                <h3 class="mb-1 text-lg font-semibold text-gray-700 dark:text-gray-200">
                    Update Password
                </h3>
                <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">
                    Gunakan password yang kuat dan unik.
                </p>
                @include('profile.partials.update-password-form')
            </div>

            {{-- Delete Account --}}
            <div class="p-6 bg-white rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 dark:bg-gray-800">
                <h3 class="mb-1 text-lg font-semibold text-gray-700 dark:text-gray-200">
                    Delete Account
                </h3>
                <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">
                    Setelah dihapus, semua data akan hilang permanen.
                </p>
                @include('profile.partials.delete-user-form')
            </div>

        </div>
    </div>
</x-layouts.admin>
