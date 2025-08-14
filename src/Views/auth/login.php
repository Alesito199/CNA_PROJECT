<div class="text-center mb-6">
    <h2 class="text-2xl font-bold text-gray-900"><?= CNA\Utils\Language::t('auth.login') ?></h2>
</div>

<form method="POST" action="/login" class="space-y-6">
    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
    
    <div>
        <label for="email" class="block text-sm font-medium text-gray-700">
            <?= CNA\Utils\Language::t('auth.email') ?>
        </label>
        <input 
            type="email" 
            name="email" 
            id="email" 
            value="<?= $this->old('email') ?>"
            class="form-input mt-1 block w-full" 
            required
            autofocus
        >
    </div>

    <div>
        <label for="password" class="block text-sm font-medium text-gray-700">
            <?= CNA\Utils\Language::t('auth.password') ?>
        </label>
        <input 
            type="password" 
            name="password" 
            id="password" 
            class="form-input mt-1 block w-full" 
            required
        >
    </div>

    <div class="flex items-center justify-between">
        <div class="flex items-center">
            <input 
                type="checkbox" 
                name="remember" 
                id="remember" 
                value="1"
                class="h-4 w-4 text-cna-primary-600 focus:ring-cna-primary-500 border-gray-300 rounded"
            >
            <label for="remember" class="ml-2 block text-sm text-gray-700">
                <?= CNA\Utils\Language::t('auth.remember_me') ?>
            </label>
        </div>

        <div class="text-sm">
            <a href="/forgot-password" class="text-cna-primary-600 hover:text-cna-primary-500">
                <?= CNA\Utils\Language::t('auth.forgot_password') ?>
            </a>
        </div>
    </div>

    <div>
        <button type="submit" class="btn-primary w-full">
            <?= CNA\Utils\Language::t('auth.login') ?>
        </button>
    </div>

    <div class="text-center">
        <span class="text-sm text-gray-600">Don't have an account?</span>
        <a href="/register" class="text-sm text-cna-primary-600 hover:text-cna-primary-500 font-medium">
            <?= CNA\Utils\Language::t('auth.register') ?>
        </a>
    </div>
</form>