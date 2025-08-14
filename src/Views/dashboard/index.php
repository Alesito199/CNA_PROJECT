<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <!-- Total Clients -->
    <div class="card">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-cna-primary-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-cna-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600"><?= CNA\Utils\Language::t('dashboard.total_clients') ?></p>
                <p class="text-2xl font-semibold text-gray-900"><?= $stats['total_clients'] ?></p>
            </div>
        </div>
    </div>

    <!-- Pending Estimates -->
    <div class="card">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-cna-secondary-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-cna-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600"><?= CNA\Utils\Language::t('dashboard.pending_estimates') ?></p>
                <p class="text-2xl font-semibold text-gray-900">0</p>
            </div>
        </div>
    </div>

    <!-- Unpaid Invoices -->
    <div class="card">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                    </svg>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600"><?= CNA\Utils\Language::t('dashboard.unpaid_invoices') ?></p>
                <p class="text-2xl font-semibold text-gray-900">0</p>
            </div>
        </div>
    </div>

    <!-- Monthly Revenue -->
    <div class="card">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600"><?= CNA\Utils\Language::t('dashboard.monthly_revenue') ?></p>
                <p class="text-2xl font-semibold text-gray-900">$0.00</p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Recent Activity -->
    <div class="card">
        <h3 class="text-lg font-medium text-gray-900 mb-4"><?= CNA\Utils\Language::t('dashboard.recent_activity') ?></h3>
        <div class="space-y-3">
            <p class="text-gray-500 text-center py-8">No recent activity</p>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card">
        <h3 class="text-lg font-medium text-gray-900 mb-4"><?= CNA\Utils\Language::t('dashboard.quick_actions') ?></h3>
        <div class="space-y-3">
            <a href="/clients/create" class="block w-full btn-primary text-center">
                <?= CNA\Utils\Language::t('clients.add_client') ?>
            </a>
            <a href="/estimates/create" class="block w-full btn-secondary text-center">
                <?= CNA\Utils\Language::t('estimates.add_estimate') ?>
            </a>
            <a href="/portfolio/create" class="block w-full btn-secondary text-center">
                <?= CNA\Utils\Language::t('portfolio.add_item') ?>
            </a>
        </div>
    </div>
</div>

<!-- Recent Clients -->
<?php if (!empty($stats['recent_clients'])): ?>
<div class="mt-8">
    <div class="card">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Clients</h3>
        <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Added</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($stats['recent_clients'] as $client): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="/clients/<?= $client['id'] ?>" class="text-cna-primary-600 hover:text-cna-primary-900">
                                <?= $this->e($client['first_name'] . ' ' . $client['last_name']) ?>
                                <?php if ($client['company']): ?>
                                    <span class="text-gray-500">- <?= $this->e($client['company']) ?></span>
                                <?php endif; ?>
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= $this->e($client['email'] ?: '-') ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= $this->e($client['phone'] ?: '-') ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= $this->lang->formatDate($client['created_at'], 'medium') ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>