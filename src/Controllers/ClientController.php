<?php

namespace CNA\Controllers;

use CNA\Models\Client;
use CNA\Utils\Security;

class ClientController extends BaseController
{
    private Client $clientModel;

    public function __construct()
    {
        parent::__construct();
        $this->clientModel = new Client();
    }

    public function index(): void
    {
        $this->requireAuth();

        $page = max(1, (int) $this->getInput('page', 1));
        $search = trim($this->getInput('search', ''));

        if ($search) {
            $clients = $this->clientModel->searchClients($search);
            $pagination = [
                'data' => $clients,
                'total' => count($clients),
                'per_page' => count($clients),
                'current_page' => 1,
                'last_page' => 1,
            ];
        } else {
            $pagination = $this->clientModel->paginate($page, 15);
        }

        $this->view->render('clients/index', [
            'title' => $this->lang->translate('clients.title'),
            'clients' => $pagination,
            'search' => $search
        ]);
    }

    public function show(string $id): void
    {
        $this->requireAuth();

        $client = $this->clientModel->find($id);
        if (!$client) {
            Session::flash('error', 'Client not found');
            $this->redirect('/clients');
        }

        $stats = $this->clientModel->getClientStats($id);
        $estimates = $this->clientModel->getClientEstimates($id);
        $invoices = $this->clientModel->getClientInvoices($id);

        $this->view->render('clients/show', [
            'title' => $this->lang->translate('clients.client_details'),
            'client' => $client,
            'stats' => $stats,
            'estimates' => $estimates,
            'invoices' => $invoices
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();

        $this->view->render('clients/create', [
            'title' => $this->lang->translate('clients.add_client')
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::flash('error', 'Invalid request');
            $this->redirect('/clients/create');
        }

        $data = [
            'first_name' => Security::sanitizeInput($this->getInput('first_name', '')),
            'last_name' => Security::sanitizeInput($this->getInput('last_name', '')),
            'company' => Security::sanitizeInput($this->getInput('company', '')),
            'email' => trim($this->getInput('email', '')),
            'phone' => Security::sanitizeInput($this->getInput('phone', '')),
            'address' => Security::sanitizeInput($this->getInput('address', '')),
            'city' => Security::sanitizeInput($this->getInput('city', '')),
            'state' => Security::sanitizeInput($this->getInput('state', '')),
            'zip_code' => Security::sanitizeInput($this->getInput('zip_code', '')),
            'notes' => Security::sanitizeInput($this->getInput('notes', '')),
        ];

        // Validation
        $errors = $this->validate([
            'first_name' => 'required|max:100',
            'last_name' => 'required|max:100',
            'email' => 'email',
            'phone' => 'phone',
        ], $data);

        if (!empty($data['email']) && $this->clientModel->isEmailTaken($data['email'])) {
            $errors['email'] = 'Email is already taken by another client';
        }

        if (!empty($errors)) {
            $this->setOldInput($data);
            Session::flash('errors', $errors);
            $this->redirect('/clients/create');
        }

        try {
            $this->clientModel->create($data);
            Session::flash('success', $this->lang->translate('clients.client_saved'));
            $this->clearOldInput();
            $this->redirect('/clients');
        } catch (\Exception $e) {
            error_log("Client creation failed: " . $e->getMessage());
            Session::flash('error', 'Failed to save client. Please try again.');
            $this->setOldInput($data);
            $this->redirect('/clients/create');
        }
    }

    public function edit(string $id): void
    {
        $this->requireAuth();

        $client = $this->clientModel->find($id);
        if (!$client) {
            Session::flash('error', 'Client not found');
            $this->redirect('/clients');
        }

        $this->view->render('clients/edit', [
            'title' => $this->lang->translate('clients.edit_client'),
            'client' => $client
        ]);
    }

    public function update(string $id): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::flash('error', 'Invalid request');
            $this->redirect('/clients/' . $id . '/edit');
        }

        $client = $this->clientModel->find($id);
        if (!$client) {
            Session::flash('error', 'Client not found');
            $this->redirect('/clients');
        }

        $data = [
            'first_name' => Security::sanitizeInput($this->getInput('first_name', '')),
            'last_name' => Security::sanitizeInput($this->getInput('last_name', '')),
            'company' => Security::sanitizeInput($this->getInput('company', '')),
            'email' => trim($this->getInput('email', '')),
            'phone' => Security::sanitizeInput($this->getInput('phone', '')),
            'address' => Security::sanitizeInput($this->getInput('address', '')),
            'city' => Security::sanitizeInput($this->getInput('city', '')),
            'state' => Security::sanitizeInput($this->getInput('state', '')),
            'zip_code' => Security::sanitizeInput($this->getInput('zip_code', '')),
            'notes' => Security::sanitizeInput($this->getInput('notes', '')),
        ];

        // Validation
        $errors = $this->validate([
            'first_name' => 'required|max:100',
            'last_name' => 'required|max:100',
            'email' => 'email',
            'phone' => 'phone',
        ], $data);

        if (!empty($data['email']) && $this->clientModel->isEmailTaken($data['email'], $id)) {
            $errors['email'] = 'Email is already taken by another client';
        }

        if (!empty($errors)) {
            $this->setOldInput($data);
            Session::flash('errors', $errors);
            $this->redirect('/clients/' . $id . '/edit');
        }

        try {
            $this->clientModel->update($id, $data);
            Session::flash('success', $this->lang->translate('clients.client_saved'));
            $this->clearOldInput();
            $this->redirect('/clients/' . $id);
        } catch (\Exception $e) {
            error_log("Client update failed: " . $e->getMessage());
            Session::flash('error', 'Failed to update client. Please try again.');
            $this->setOldInput($data);
            $this->redirect('/clients/' . $id . '/edit');
        }
    }

    public function delete(string $id): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
        }

        $client = $this->clientModel->find($id);
        if (!$client) {
            $this->json(['success' => false, 'message' => 'Client not found'], 404);
        }

        try {
            $this->clientModel->delete($id);
            $this->json(['success' => true, 'message' => $this->lang->translate('clients.client_deleted')]);
        } catch (\Exception $e) {
            error_log("Client deletion failed: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to delete client'], 500);
        }
    }
}