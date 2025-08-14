<?php

namespace CNA\Controllers;

use CNA\Models\Estimate;
use CNA\Models\Client;
use CNA\Utils\Security;
use CNA\Utils\Session;

class EstimateController extends BaseController
{
    private Estimate $estimateModel;
    private Client $clientModel;

    public function __construct()
    {
        parent::__construct();
        $this->estimateModel = new Estimate();
        $this->clientModel = new Client();
    }

    public function index(): void
    {
        $this->requireAuth();

        $page = max(1, (int) $this->getInput('page', 1));
        $search = trim($this->getInput('search', ''));

        if ($search) {
            $estimates = $this->estimateModel->searchEstimates($search);
            $pagination = [
                'data' => $estimates,
                'total' => count($estimates),
                'per_page' => count($estimates),
                'current_page' => 1,
                'last_page' => 1,
            ];
        } else {
            $estimates = $this->estimateModel->getEstimatesWithClients(15, ($page - 1) * 15);
            $total = $this->estimateModel->count();
            $pagination = [
                'data' => $estimates,
                'total' => $total,
                'per_page' => 15,
                'current_page' => $page,
                'last_page' => (int) ceil($total / 15),
                'from' => (($page - 1) * 15) + 1,
                'to' => min($page * 15, $total),
            ];
        }

        $this->view->render('estimates/index', [
            'title' => $this->lang->translate('estimates.title'),
            'estimates' => $pagination,
            'search' => $search
        ]);
    }

    public function show(string $id): void
    {
        $this->requireAuth();

        $estimate = $this->estimateModel->getEstimateWithDetails($id);
        if (!$estimate) {
            Session::flash('error', 'Estimate not found');
            $this->redirect('/estimates');
        }

        $this->view->render('estimates/show', [
            'title' => $this->lang->translate('estimates.estimate_details'),
            'estimate' => $estimate
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();

        $clients = $this->clientModel->all();

        $this->view->render('estimates/create', [
            'title' => $this->lang->translate('estimates.add_estimate'),
            'clients' => $clients
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::flash('error', 'Invalid request');
            $this->redirect('/estimates/create');
        }

        $user = Session::user();

        $data = [
            'client_id' => $this->getInput('client_id'),
            'title' => Security::sanitizeInput($this->getInput('title', '')),
            'description' => Security::sanitizeInput($this->getInput('description', '')),
            'tax_rate' => (float) $this->getInput('tax_rate', 6.625),
            'valid_until' => $this->getInput('valid_until') ?: date('Y-m-d', strtotime('+30 days')),
            'notes' => Security::sanitizeInput($this->getInput('notes', '')),
            'user_id' => $user['id'],
            'status' => 'draft'
        ];

        // Validation
        $errors = $this->validate([
            'client_id' => 'required',
            'title' => 'required|max:255',
        ], $data);

        // Validate client exists
        if ($data['client_id'] && !$this->clientModel->find($data['client_id'])) {
            $errors['client_id'] = 'Selected client does not exist';
        }

        if (!empty($errors)) {
            $this->setOldInput($data);
            Session::flash('errors', $errors);
            $this->redirect('/estimates/create');
        }

        try {
            $data['estimate_number'] = $this->estimateModel->generateEstimateNumber();
            $estimateId = $this->estimateModel->create($data);

            // Add items if provided
            $items = $this->getInput('items', []);
            if (is_array($items)) {
                foreach ($items as $index => $item) {
                    if (!empty($item['description']) && !empty($item['quantity']) && !empty($item['unit_price'])) {
                        $this->estimateModel->addItem($estimateId, [
                            'description' => Security::sanitizeInput($item['description']),
                            'quantity' => (float) $item['quantity'],
                            'unit_price' => (float) $item['unit_price'],
                            'sort_order' => $index
                        ]);
                    }
                }
            }

            // Update totals
            $this->estimateModel->updateTotals($estimateId);

            Session::flash('success', $this->lang->translate('estimates.estimate_saved'));
            $this->clearOldInput();
            $this->redirect('/estimates/' . $estimateId);
            
        } catch (\Exception $e) {
            error_log("Estimate creation failed: " . $e->getMessage());
            Session::flash('error', 'Failed to save estimate. Please try again.');
            $this->setOldInput($data);
            $this->redirect('/estimates/create');
        }
    }

    public function edit(string $id): void
    {
        $this->requireAuth();

        $estimate = $this->estimateModel->getEstimateWithDetails($id);
        if (!$estimate) {
            Session::flash('error', 'Estimate not found');
            $this->redirect('/estimates');
        }

        $clients = $this->clientModel->all();

        $this->view->render('estimates/edit', [
            'title' => $this->lang->translate('estimates.edit_estimate'),
            'estimate' => $estimate,
            'clients' => $clients
        ]);
    }

    public function update(string $id): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::flash('error', 'Invalid request');
            $this->redirect('/estimates/' . $id . '/edit');
        }

        $estimate = $this->estimateModel->find($id);
        if (!$estimate) {
            Session::flash('error', 'Estimate not found');
            $this->redirect('/estimates');
        }

        $data = [
            'client_id' => $this->getInput('client_id'),
            'title' => Security::sanitizeInput($this->getInput('title', '')),
            'description' => Security::sanitizeInput($this->getInput('description', '')),
            'tax_rate' => (float) $this->getInput('tax_rate', 6.625),
            'valid_until' => $this->getInput('valid_until'),
            'notes' => Security::sanitizeInput($this->getInput('notes', '')),
        ];

        // Validation
        $errors = $this->validate([
            'client_id' => 'required',
            'title' => 'required|max:255',
        ], $data);

        // Validate client exists
        if ($data['client_id'] && !$this->clientModel->find($data['client_id'])) {
            $errors['client_id'] = 'Selected client does not exist';
        }

        if (!empty($errors)) {
            $this->setOldInput($data);
            Session::flash('errors', $errors);
            $this->redirect('/estimates/' . $id . '/edit');
        }

        try {
            $this->estimateModel->update($id, $data);

            // Update items
            $items = $this->getInput('items', []);
            if (is_array($items)) {
                // Clear existing items
                $existingItems = $this->estimateModel->getItems($id);
                foreach ($existingItems as $item) {
                    $this->estimateModel->deleteItem($item['id']);
                }

                // Add new items
                foreach ($items as $index => $item) {
                    if (!empty($item['description']) && !empty($item['quantity']) && !empty($item['unit_price'])) {
                        $this->estimateModel->addItem($id, [
                            'description' => Security::sanitizeInput($item['description']),
                            'quantity' => (float) $item['quantity'],
                            'unit_price' => (float) $item['unit_price'],
                            'sort_order' => $index
                        ]);
                    }
                }
            }

            // Update totals
            $this->estimateModel->updateTotals($id);

            Session::flash('success', $this->lang->translate('estimates.estimate_saved'));
            $this->clearOldInput();
            $this->redirect('/estimates/' . $id);
            
        } catch (\Exception $e) {
            error_log("Estimate update failed: " . $e->getMessage());
            Session::flash('error', 'Failed to update estimate. Please try again.');
            $this->setOldInput($data);
            $this->redirect('/estimates/' . $id . '/edit');
        }
    }

    public function delete(string $id): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
        }

        $estimate = $this->estimateModel->find($id);
        if (!$estimate) {
            $this->json(['success' => false, 'message' => 'Estimate not found'], 404);
        }

        try {
            $this->estimateModel->delete($id);
            $this->json(['success' => true, 'message' => 'Estimate deleted successfully']);
        } catch (\Exception $e) {
            error_log("Estimate deletion failed: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to delete estimate'], 500);
        }
    }

    public function convertToInvoice(string $id): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
        }

        $estimate = $this->estimateModel->find($id);
        if (!$estimate) {
            $this->json(['success' => false, 'message' => 'Estimate not found'], 404);
        }

        if ($estimate['status'] === 'approved') {
            $this->json(['success' => false, 'message' => 'Estimate has already been converted to invoice'], 400);
        }

        try {
            $user = Session::user();
            $invoiceId = $this->estimateModel->convertToInvoice($id, $user['id']);
            
            if ($invoiceId) {
                $this->json(['success' => true, 'message' => 'Estimate converted to invoice successfully', 'invoice_id' => $invoiceId]);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to convert estimate to invoice'], 500);
            }
        } catch (\Exception $e) {
            error_log("Estimate conversion failed: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to convert estimate to invoice'], 500);
        }
    }

    public function updateStatus(string $id): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
        }

        $estimate = $this->estimateModel->find($id);
        if (!$estimate) {
            $this->json(['success' => false, 'message' => 'Estimate not found'], 404);
        }

        $status = $this->getInput('status');
        $validStatuses = ['draft', 'sent', 'approved', 'rejected', 'expired'];
        
        if (!in_array($status, $validStatuses)) {
            $this->json(['success' => false, 'message' => 'Invalid status'], 400);
        }

        try {
            $this->estimateModel->update($id, ['status' => $status]);
            $this->json(['success' => true, 'message' => 'Status updated successfully']);
        } catch (\Exception $e) {
            error_log("Status update failed: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to update status'], 500);
        }
    }
}