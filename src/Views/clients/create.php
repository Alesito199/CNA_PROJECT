<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-900"><?= CNA\Utils\Language::t('clients.add_client') ?></h1>
    <a href="/clients" class="btn-secondary">
        <?= CNA\Utils\Language::t('common.cancel') ?>
    </a>
</div>

<div class="card">
    <form method="POST" action="/clients" data-validate>
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- First Name -->
            <div>
                <label for="first_name" class="block text-sm font-medium text-gray-700">
                    <?= CNA\Utils\Language::t('clients.first_name') ?> *
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

            <!-- Last Name -->
            <div>
                <label for="last_name" class="block text-sm font-medium text-gray-700">
                    <?= CNA\Utils\Language::t('clients.last_name') ?> *
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

            <!-- Company -->
            <div>
                <label for="company" class="block text-sm font-medium text-gray-700">
                    <?= CNA\Utils\Language::t('clients.company') ?>
                </label>
                <input 
                    type="text" 
                    name="company" 
                    id="company" 
                    value="<?= $this->old('company') ?>"
                    class="form-input mt-1 block w-full"
                >
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">
                    <?= CNA\Utils\Language::t('clients.email') ?>
                </label>
                <input 
                    type="email" 
                    name="email" 
                    id="email" 
                    value="<?= $this->old('email') ?>"
                    class="form-input mt-1 block w-full"
                >
            </div>

            <!-- Phone -->
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700">
                    <?= CNA\Utils\Language::t('clients.phone') ?>
                </label>
                <input 
                    type="tel" 
                    name="phone" 
                    id="phone" 
                    value="<?= $this->old('phone') ?>"
                    class="form-input mt-1 block w-full"
                    data-phone
                >
            </div>

            <!-- State -->
            <div>
                <label for="state" class="block text-sm font-medium text-gray-700">
                    <?= CNA\Utils\Language::t('clients.state') ?>
                </label>
                <input 
                    type="text" 
                    name="state" 
                    id="state" 
                    value="<?= $this->old('state') ?>"
                    class="form-input mt-1 block w-full"
                >
            </div>
        </div>

        <!-- Address -->
        <div class="mt-6">
            <label for="address" class="block text-sm font-medium text-gray-700">
                <?= CNA\Utils\Language::t('clients.address') ?>
            </label>
            <textarea 
                name="address" 
                id="address" 
                rows="3" 
                class="form-input mt-1 block w-full"
            ><?= $this->old('address') ?></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <!-- City -->
            <div>
                <label for="city" class="block text-sm font-medium text-gray-700">
                    <?= CNA\Utils\Language::t('clients.city') ?>
                </label>
                <input 
                    type="text" 
                    name="city" 
                    id="city" 
                    value="<?= $this->old('city') ?>"
                    class="form-input mt-1 block w-full"
                >
            </div>

            <!-- ZIP Code -->
            <div>
                <label for="zip_code" class="block text-sm font-medium text-gray-700">
                    <?= CNA\Utils\Language::t('clients.zip_code') ?>
                </label>
                <input 
                    type="text" 
                    name="zip_code" 
                    id="zip_code" 
                    value="<?= $this->old('zip_code') ?>"
                    class="form-input mt-1 block w-full"
                >
            </div>
        </div>

        <!-- Notes -->
        <div class="mt-6">
            <label for="notes" class="block text-sm font-medium text-gray-700">
                <?= CNA\Utils\Language::t('clients.notes') ?>
            </label>
            <textarea 
                name="notes" 
                id="notes" 
                rows="4" 
                class="form-input mt-1 block w-full"
                placeholder="Additional notes about the client..."
            ><?= $this->old('notes') ?></textarea>
        </div>

        <!-- Submit Buttons -->
        <div class="flex justify-end space-x-3 mt-8">
            <a href="/clients" class="btn-secondary">
                <?= CNA\Utils\Language::t('common.cancel') ?>
            </a>
            <button type="submit" class="btn-primary">
                <?= CNA\Utils\Language::t('common.save') ?>
            </button>
        </div>
    </form>
</div>