<!DOCTYPE html>
<html lang="<?= $this->lang->getCurrentLanguage() ?>" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? $this->e($title) . ' - ' : '' ?><?= $this->e($config['app']['name']) ?></title>
    
    <!-- Tailwind CSS -->
    <link href="<?= $this->asset('css/style.css') ?>" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" href="<?= $this->asset('images/favicon.ico') ?>" type="image/x-icon">
    
    <!-- Meta tags -->
    <meta name="description" content="Professional Upholstery Business Management System">
    <meta name="author" content="CNA Upholstery">
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?= $csrf_token ?>">
</head>
<body class="h-full bg-gray-50">
    <div id="app" class="min-h-full">
        <!-- Navigation -->
        <nav class="bg-cna-primary-600 shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <!-- Logo -->
                        <div class="flex-shrink-0">
                            <a href="/dashboard" class="text-white text-xl font-bold">
                                CNA Upholstery
                            </a>
                        </div>
                        
                        <!-- Main Navigation -->
                        <div class="hidden md:ml-10 md:flex md:space-x-8">
                            <a href="/dashboard" class="text-white hover:text-cna-primary-200 px-3 py-2 rounded-md text-sm font-medium">
                                <?= CNA\Utils\Language::t('nav.dashboard') ?>
                            </a>
                            <a href="/clients" class="text-white hover:text-cna-primary-200 px-3 py-2 rounded-md text-sm font-medium">
                                <?= CNA\Utils\Language::t('nav.clients') ?>
                            </a>
                            <a href="/estimates" class="text-white hover:text-cna-primary-200 px-3 py-2 rounded-md text-sm font-medium">
                                <?= CNA\Utils\Language::t('nav.estimates') ?>
                            </a>
                            <a href="/invoices" class="text-white hover:text-cna-primary-200 px-3 py-2 rounded-md text-sm font-medium">
                                <?= CNA\Utils\Language::t('nav.invoices') ?>
                            </a>
                            <a href="/portfolio" class="text-white hover:text-cna-primary-200 px-3 py-2 rounded-md text-sm font-medium">
                                <?= CNA\Utils\Language::t('nav.portfolio') ?>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Right side -->
                    <div class="flex items-center space-x-4">
                        <!-- Language Switcher -->
                        <div class="relative">
                            <select onchange="window.location.href='/lang/' + this.value" class="bg-cna-primary-500 text-white border-0 text-sm rounded-md px-2 py-1">
                                <option value="en" <?= $this->lang->getCurrentLanguage() === 'en' ? 'selected' : '' ?>>EN</option>
                                <option value="es" <?= $this->lang->getCurrentLanguage() === 'es' ? 'selected' : '' ?>>ES</option>
                            </select>
                        </div>
                        
                        <!-- User Menu -->
                        <?php if ($user): ?>
                        <div class="relative">
                            <div class="flex items-center space-x-3">
                                <span class="text-white text-sm">
                                    Hello, <?= $this->e($user['first_name']) ?>
                                </span>
                                <a href="/logout" class="bg-cna-primary-700 hover:bg-cna-primary-800 text-white px-3 py-2 rounded-md text-sm font-medium">
                                    <?= CNA\Utils\Language::t('auth.logout') ?>
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Flash Messages -->
        <?php if ($flash_messages['success']): ?>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <div class="alert-success">
                    <?= $this->e($flash_messages['success']) ?>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($flash_messages['error']): ?>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <div class="alert-error">
                    <?= $this->e($flash_messages['error']) ?>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($flash_messages['warning']): ?>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <div class="alert-warning">
                    <?= $this->e($flash_messages['warning']) ?>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($flash_messages['info']): ?>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <div class="alert-info">
                    <?= $this->e($flash_messages['info']) ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Main Content -->
        <main class="flex-1">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <?= $content ?>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="<?= $this->asset('js/app.js') ?>"></script>
    <script>
        // CSRF token for AJAX requests
        window.csrfToken = '<?= $csrf_token ?>';
        
        // Delete confirmation
        function confirmDelete(url, message) {
            if (confirm(message || '<?= CNA\Utils\Language::t('common.confirm_delete') ?>')) {
                fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.csrfToken
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'An error occurred');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred');
                });
            }
        }
    </script>
</body>
</html>