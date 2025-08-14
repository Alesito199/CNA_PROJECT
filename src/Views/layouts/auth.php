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
<body class="h-full bg-gray-100">
    <div class="min-h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Logo -->
            <div class="text-center">
                <h1 class="text-3xl font-bold text-cna-primary-600 mb-2">CNA Upholstery</h1>
                <p class="text-gray-600"><?= CNA\Utils\Language::t('app.welcome') ?></p>
            </div>

            <!-- Language Switcher -->
            <div class="flex justify-center">
                <select onchange="window.location.href='/lang/' + this.value" class="form-input text-sm">
                    <option value="en" <?= $this->lang->getCurrentLanguage() === 'en' ? 'selected' : '' ?>>English</option>
                    <option value="es" <?= $this->lang->getCurrentLanguage() === 'es' ? 'selected' : '' ?>>Español</option>
                </select>
            </div>

            <!-- Flash Messages -->
            <?php if ($flash_messages['success']): ?>
                <div class="alert-success">
                    <?= $this->e($flash_messages['success']) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($flash_messages['error']): ?>
                <div class="alert-error">
                    <?= $this->e($flash_messages['error']) ?>
                </div>
            <?php endif; ?>

            <!-- Content -->
            <div class="card">
                <?= $content ?>
            </div>
        </div>
    </div>
</body>
</html>