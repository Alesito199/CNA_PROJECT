<div class="text-center mb-6">
    <h2 class="text-2xl font-bold text-gray-900"><?= CNA\Utils\Language::t('auth.register') ?></h2>
</div>

<form method="POST" action="/register" class="space-y-6">
    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
    
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label for="first_name" class="block text-sm font-medium text-gray-700">
                <?= CNA\Utils\Language::t('clients.first_name') ?>
            </label>
            <input 
                type="text" 
                name="first_name" 
                id="first_name" 
                value="<?= $this->old('first_name') ?>"
                class="form-input mt-1 block w-full" 
                required
                autofocus
            >
        </div>

        <div>
            <label for="last_name" class="block text-sm font-medium text-gray-700">
                <?= CNA\Utils\Language::t('clients.last_name') ?>
            </label>
            <input 
                type="text" 
                name="last_name" 
                id="last_name" 
                value="<?= $this->old('last_name') ?>"
                class="form-input mt-1 block w-full" 
                required
            >
        </div>
    </div>

    <div>
        <label for="username" class="block text-sm font-medium text-gray-700">
            Username
        </label>
        <input 
            type="text" 
            name="username" 
            id="username" 
            value="<?= $this->old('username') ?>"
            class="form-input mt-1 block w-full" 
            required
        >
    </div>

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
            minlength="8"
        >
    </div>

    <div>
        <label for="password_confirm" class="block text-sm font-medium text-gray-700">
            Confirm Password
        </label>
        <input 
            type="password" 
            name="password_confirm" 
            id="password_confirm" 
            class="form-input mt-1 block w-full" 
            required
            minlength="8"
        >
    </div>

    <div>
        <button type="submit" class="btn-primary w-full">
            <?= CNA\Utils\Language::t('auth.register') ?>
        </button>
    </div>

    <div class="text-center">
        <span class="text-sm text-gray-600">Already have an account?</span>
        <a href="/login" class="text-sm text-cna-primary-600 hover:text-cna-primary-500 font-medium">
            <?= CNA\Utils\Language::t('auth.login') ?>
        </a>
    </div>
</form>