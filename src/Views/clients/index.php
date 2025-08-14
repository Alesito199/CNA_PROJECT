<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-900"><?= CNA\Utils\Language::t('clients.title') ?></h1>
    <a href="/clients/create" class="btn-primary">
        <?= CNA\Utils\Language::t('clients.add_client') ?>
    </a>
</div>

<!-- Search Form -->
<div class="card mb-6">
    <form method="GET" action="/clients" class="flex gap-4">
        <div class="flex-1">
            <input 
                type="text" 
                name="search" 
                value="<?= $this->e($search) ?>" 
                placeholder="<?= CNA\Utils\Language::t('common.search') ?>..."
                class="form-input w-full"
            >
        </div>
        <button type="submit" class="btn-primary">
            <?= CNA\Utils\Language::t('common.search') ?>
        </button>
        <?php if ($search): ?>
            <a href="/clients" class="btn-secondary">
                Clear
            </a>
        <?php endif; ?>
    </form>
</div>

<!-- Clients Table -->
<div class="card">
    <?php if (empty($clients['data'])): ?>
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900"><?= CNA\Utils\Language::t('clients.no_clients') ?></h3>
            <div class="mt-6">
                <a href="/clients/create" class="btn-primary">
                    <?= CNA\Utils\Language::t('clients.add_client') ?>
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($clients['data'] as $client): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="/clients/<?= $client['id'] ?>" class="text-cna-primary-600 hover:text-cna-primary-900 font-medium">
                                <?= $this->e($client['first_name'] . ' ' . $client['last_name']) ?>
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= $this->e($client['company'] ?: '-') ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= $client['email'] ? '<a href="mailto:' . $this->e($client['email']) . '" class="text-cna-primary-600 hover:text-cna-primary-900">' . $this->e($client['email']) . '</a>' : '-' ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= $client['phone'] ? '<a href="tel:' . $this->e($client['phone']) . '" class="text-cna-primary-600 hover:text-cna-primary-900">' . $this->e($client['phone']) . '</a>' : '-' ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= $this->lang->formatDate($client['created_at'], 'medium') ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <a href="/clients/<?= $client['id'] ?>" class="text-cna-primary-600 hover:text-cna-primary-900">
                                <?= CNA\Utils\Language::t('common.view') ?>
                            </a>
                            <a href="/clients/<?= $client['id'] ?>/edit" class="text-yellow-600 hover:text-yellow-900">
                                <?= CNA\Utils\Language::t('common.edit') ?>
                            </a>
                            <button 
                                onclick="confirmDelete('/clients/<?= $client['id'] ?>')"
                                class="text-red-600 hover:text-red-900"
                            >
                                <?= CNA\Utils\Language::t('common.delete') ?>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($clients['last_page'] > 1): ?>
            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                <div class="flex-1 flex justify-between sm:hidden">
                    <?php if ($clients['current_page'] > 1): ?>
                        <a href="?page=<?= $clients['current_page'] - 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>" class="btn-secondary">
                            Previous
                        </a>
                    <?php endif; ?>
                    <?php if ($clients['current_page'] < $clients['last_page']): ?>
                        <a href="?page=<?= $clients['current_page'] + 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>" class="btn-secondary">
                            Next
                        </a>
                    <?php endif; ?>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing <?= $clients['from'] ?> to <?= $clients['to'] ?> of <?= $clients['total'] ?> results
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                            <?php for ($i = 1; $i <= $clients['last_page']; $i++): ?>
                                <a 
                                    href="?page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?>"
                                    class="relative inline-flex items-center px-4 py-2 border text-sm font-medium <?= $i === $clients['current_page'] ? 'z-10 bg-cna-primary-50 border-cna-primary-500 text-cna-primary-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50' ?>"
                                >
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                        </nav>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>