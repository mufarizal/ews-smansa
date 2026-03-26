<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Delete Account') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.destroy') }}" class="rounded-lg border border-red-200 bg-red-50 p-5">
        @csrf
        @method('delete')

        <h3 class="text-base font-semibold text-red-800">
            {{ __('Are you sure you want to delete your account?') }}
        </h3>

        <p class="mt-2 text-sm text-red-700">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
        </p>

        <div class="mt-4">
            <label for="password" class="block text-sm font-medium text-red-800">{{ __('Password') }}</label>
            <input id="password" name="password" type="password"
                class="mt-1 block w-full rounded-md border-red-300 bg-white shadow-sm focus:border-red-500 focus:ring-red-500"
                placeholder="{{ __('Password') }}">

            @error('password', 'userDeletion')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-4 flex justify-end">
            <button type="submit"
                class="inline-flex items-center rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-red-500">
                {{ __('Delete Account') }}
            </button>
        </div>
    </form>
</section>
