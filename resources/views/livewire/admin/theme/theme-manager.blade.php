<div>
    <x-slot:title>
        {{ t('theme_manager') }}
    </x-slot:title>

     <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => route('admin.dashboard')],
        ['label' => t('theme_manager')],
    ]" />
    <div class="max-w-6xl md:flex md:items-center md:justify-between">
        <x-page-heading>
            {{ t('theme_manager') }}
        </x-page-heading>
    </div>
    <!-- No Themes Message -->
    @if (empty($themes) || count($themes) === 0)
    <div
        class="mb-6 p-4 flex justify-start items-center gap-2 bg-info-50 dark:bg-info-800/20 border-l-4 border-info-500 dark:border-info-500 text-info-700 dark:text-info-400 rounded-md">
        <x-heroicon-o-paint-brush class="w-5 h-5 text-info-400 dark:text-info-500" />
        <p class=" text-slate-600 dark:text-slate-400 text-base">
            {{ t(key: 'no_themes_found') }}
        </p>
    </div>
    @endif

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3 py-4">
        <!-- Upload Theme Card -->
        <!-- Theme Cards -->
        @foreach ($themes as $theme)
        <div
            class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden hover:shadow-md transition duration-200">
            <!-- Theme Image -->
            <div class="relative w-full h-58 overflow-hidden">
                <img class="w-full h-full object-cover transform hover:scale-105 transition duration-500"
                    src="{{ asset('storage/' . $theme->theme_url) }}" alt="{{ $theme->name }}"
                    onerror="this.src='{{ asset('img/img-placeholder.png') }}'; this.onerror='';">
            </div>
            <!-- Theme Details -->
            <div class="flex items-center justify-between p-4 border-t border-slate-200 dark:border-slate-700">
                <div class="flex flex-col">
                    <h4 class="font-medium text-slate-800 dark:text-slate-200">{{ $theme->name }}</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        @if ($theme->version)
                        {{ t('version') }} {{ $theme->version }}
                        @endif
                    </p>
                </div>

            </div>
            <!-- Theme Status / Activation Button -->
            <div class="p-4 pt-0">
                @if ($theme->active)
                <div disabled
                    class="flex justify-center items-center px-3 py-2 space-x-1.5 w-full text-sm font-medium text-slate-500 bg-slate-200 dark:bg-slate-700 dark:text-slate-400 rounded-md opacity-70 cursor-not-allowed">
                    <x-heroicon-s-check-circle class="w-5 h-5 text-white" />
                    <span>{{ t('active_theme') }}</span>
                </div>
                @else
                <button wire:click="activate('{{ $theme->folder }}')"
                    class="flex justify-center items-center px-3 py-2 space-x-1.5 w-full text-sm font-medium text-primary-600 rounded-md border border-primary-200 dark:border-slate-600 dark:text-primary-400 hover:text-white hover:bg-primary-600 hover:border-primary-600 dark:hover:bg-primary-600 dark:hover:border-primary-600 dark:hover:text-white transition-all duration-200">
                    <x-heroicon-o-bolt class="w-5 h-5" />
                    <span>{{ t('activate_theme') }}</span>
                </button>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>