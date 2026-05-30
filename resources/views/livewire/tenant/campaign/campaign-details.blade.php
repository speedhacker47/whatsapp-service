<div>
    <x-slot:title>
        {{ $campaign->name }} - {{ t('campaign_details') }}
    </x-slot:title>

     <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => tenant_route('tenant.dashboard')],
        ['label' => t('campaigns'), 'route' => tenant_route('tenant.campaigns.list')],
        ['label' => t('campaign_details')]
]" />

    <section class="bg-gray-50 dark:bg-slate-800">

        {{-- buttons --}}
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6">
            <div class="mb-4 sm:mb-0 flex flex-col sm:flex-row gap-4">
            </div>
            <div class="flex flex-col sm:flex-row gap-4">
                @if ($campaign->pause_campaign)
                <x-button.green wire:click="resumeCampaign" class="flex items-center justify-center  ">
                    <x-heroicon-o-play class="h-4 w-4 mr-2 animate-slow-ping" />
                    {{ t($campaign->pause_campaign ? 'resume_campaign' : 'pause_campaign') }}
                </x-button.green>
                @else
                <x-button.danger wire:click="resumeCampaign" class="flex items-center justify-center">
                    <x-heroicon-o-pause class="h-4 w-4 mr-2 animate-slow-ping" />
                    {{ t($campaign->pause_campaign ? 'resume_campaign' : 'pause_campaign') }}
                </x-button.danger>
                @endif
                @if ($isRetryAble)
                <x-button.green wire:click="retryCampaign" class="flex items-center justify-center  ">
                    <x-heroicon-o-arrow-path class="h-4 w-4 mr-2" />
                    {{ t('resend_campaign') }}
                </x-button.green>
                @endif

                @if(checkPermission('tenant.campaigns.create'))
                <x-button.primary wire:click="createCampaign" class="flex items-center justify-center">
                    <x-heroicon-o-plus class="h-5 w-5 mr-2" />
                    {{ t('create_new_campaign') }}
                </x-button.primary>
                @endif
            </div>
        </div>

        {{-- panel 1 --}}
        <div
            class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6 dark:bg-slate-800 dark:border-slate-700">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                <div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ t('campaign_name_capital') }}
                    </span>
                    <h2 class="mt-1 text-lg dark:text-gray-400">{{ $campaign->name }}</h2>
                </div>
                <div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ t('status_capital') }}</span>
                    <div class="mt-1">
                        @php
                        $badgeClasses = match ($campaignStatus) {
                        'fail' => 'bg-danger-100 text-danger-700 dark:bg-danger-900 dark:text-danger-300',
                        'pending' => 'bg-warning-100 text-warning-700 dark:bg-warning-900 dark:text-warning-300',
                        'sent' => 'bg-success-100 text-success-700 dark:bg-success-900 dark:text-success-300',
                        'executed' => 'bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300',
                        default => 'bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-300',
                        };

                        $statusLabel = match ($campaignStatus) {
                        'fail' => t('failed'),
                        'pending' => t('in_progress'),
                        'sent' => t('success'),
                        'executed' => t('executed'),
                        default => 'Unknown',
                        };
                        @endphp

                        <span class="px-3 py-1 rounded-full text-sm {{ $badgeClasses }}">
                            {{ t($statusLabel) }}
                        </span>
                    </div>
                </div>
                <div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ t('template_capital') }}</span>
                    <p class="mt-1 dark:text-gray-400 break-words">{{ $template_name }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ t('scheduled_at_capital') }}</span>
                    <p class="mt-1 text-gray-900 dark:text-gray-400">
                        {{ format_date_time($campaign->scheduled_send_time) }}</p>
                </div>
            </div>
        </div>

        {{-- panel 2 --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 2xl:grid-cols-4 gap-6 mb-6">
            <!-- Total Contacts -->
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 dark:bg-slate-800 dark:border-slate-700">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-gray-500 font-medium dark:text-gray-400 uppercase">
                        {{ t('total') . ' ' . t($campaign->rel_type) . ' ' . t('in_this_campaign') }}</h3>
                    <div
                        class="w-10 h-10 bg-primary-50 rounded-full flex items-center justify-center dark:bg-primary-900">
                        <x-heroicon-o-user-group class="h-6 w-6 text-primary-500 dark:text-primary-400 " />
                    </div>
                </div>
                <div class="flex items-baseline justify-between">
                    <span class="text-3xl font-bold dark:text-gray-400">{{ $totalCount }}</span>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ t('total') }}</span>
                </div>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    {{ $totalCampaignsPercent . '% ' . t('of_total_leads') }}</p>
            </div>

            <!-- Total Delivered -->
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 dark:bg-slate-800 dark:border-slate-700">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-gray-500 font-medium dark:text-gray-400">{{ t('total_delivered') }}</h3>
                    <div
                        class="w-10 h-10 bg-success-50 rounded-full flex items-center justify-center dark:bg-success-900">
                        <x-heroicon-o-check class="h-5 w-5 text-success-500 dark:text-success-400" />
                    </div>
                </div>
                <div class="flex items-baseline justify-between">
                    <span class="text-3xl font-bold dark:text-gray-400">{{ $totalDeliveredPercent }}%</span>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ t('rate') }}</span>
                </div>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400"><span>{{ $deliverCount }}</span>
                    {{ t('messages_delivered') }}</p>
            </div>

            <!-- Total Read -->
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 dark:bg-slate-800 dark:border-slate-700">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-gray-500 font-medium dark:text-gray-400">{{ t('total_read') }}</h3>
                    <div class="w-10 h-10 bg-info-50 rounded-full flex items-center justify-center dark:bg-primary-900">
                        <x-heroicon-o-eye class="h-6 w-6 text-primary-500 dark:text-primary-400" />
                    </div>
                </div>
                <div class="flex items-baseline justify-between">
                    <span class="text-3xl font-bold dark:text-gray-400">{{ $totalReadPercent }}%</span>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ t('rate') }}</span>
                </div>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400"><span>{{ $readCount }}</span>
                    {{ ' ' . t('total_read_messages') }}</p>
            </div>

            <!-- Total Failed -->
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 dark:bg-slate-800 dark:border-slate-700">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-gray-500 font-medium dark:text-gray-400">{{ t('total_failed') }}</h3>
                    <div
                        class="w-10 h-10 bg-danger-50 rounded-full flex items-center justify-center dark:bg-danger-900">
                        <x-heroicon-o-exclamation-circle class="h-6 w-6 text-danger-500 dark:text-danger-400" />
                    </div>
                </div>
                <div class="flex items-baseline justify-between">
                    <span class="text-3xl font-bold dark:text-gray-400">{{ $totalFailedPercent }}%</span>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ t('rate') }}</span>
                </div>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    <span>{{ $failedCount }}</span>{{ ' ' . t('total_fail') }}
                </p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 dark:text-gray-400 dark:border-slate-700 dark:bg-slate-800"
            x-data="{ activeTab: 'queue' }" x-cloak>
            <div class="border-b border-gray-200 dark:border-slate-700">
                <div class="flex space-x-8 px-6">
                    <button x-on:click="activeTab = 'queue'"
                        :class="{ 'border-b-2 border-primary-500 text-primary-600': activeTab === 'queue', 'text-gray-500': activeTab !== 'queue' }"
                        class="py-4 px-2 font-medium text-sm focus:outline-none dark:text-gray-400">
                        {{ t('queue') }}
                    </button>
                    <button x-on:click="activeTab = 'executed'"
                        :class="{ 'border-b-2 border-primary-500 text-primary-600': activeTab === 'executed', 'text-gray-500': activeTab !== 'executed' }"
                        class="py-4 px-2 font-medium text-sm focus:outline-none dark:text-gray-400">
                        {{ t('executed') }}
                    </button>
                </div>
            </div>
            <div class="p-6">
                <div x-show="activeTab === 'queue'" class="text-gray-500" wire:poll.30s="refreshTable">
                    <livewire:tenant.tables.campaign-detail-table />
                </div>
                <div x-show="activeTab === 'executed'" class="text-gray-500" wire:poll.30s="refreshTable">
                    <livewire:tenant.tables.campaign-executed-table />
                </div>
            </div>
        </div>
    </section>
</div>

</section>