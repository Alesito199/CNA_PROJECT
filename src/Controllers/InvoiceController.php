<?php

namespace CNA\Controllers;

use CNA\Models\Invoice;
use CNA\Models\Client;
use CNA\Utils\Security;
use CNA\Utils\Session;

class InvoiceController extends BaseController
{
    private Invoice $invoiceModel;
    private Client $clientModel;

    public function __construct()
    {
        parent::__construct();
        $this->invoiceModel = new Invoice();
        $this->clientModel = new Client();
    }

    public function index(): void
    {
        $this->requireAuth();

        $page = max(1, (int) $this->getInput('page', 1));
        $search = trim($this->getInput('search', ''));

        if ($search) {
            $invoices = $this->invoiceModel->searchInvoices($search);
            $pagination = [
                'data' => $invoices,
                'total' => count($invoices),
                'per_page' => count($invoices),
                'current_page' => 1,
                'last_page' => 1,
            ];
        } else {
            $invoices = $this->invoiceModel->getInvoicesWithClients(15, ($page - 1) * 15);
            $total = $this->invoiceModel->count();
            $pagination = [
                'data' => $invoices,
                'total' => $total,
                'per_page' => 15,
                'current_page' => $page,
                'last_page' => (int) ceil($total / 15),
                'from' => (($page - 1) * 15) + 1,
                'to' => min($page * 15, $total),
            ];
        }

        $this->view->render('invoices/index', [
            'title' => $this->lang->translate('invoices.title'),
            'invoices' => $pagination,
            'search' => $search
        ]);
    }

    public function show(string $id): void
    {
        $this->requireAuth();

        $invoice = $this->invoiceModel->getInvoiceWithDetails($id);
        if (!$invoice) {
            Session::flash('error', 'Invoice not found');
            $this->redirect('/invoices');
        }

        $this->view->render('invoices/show', [
            'title' => $this->lang->translate('invoices.invoice_details'),
            'invoice' => $invoice
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();

        $clients = $this->clientModel->all();

        $this->view->render('invoices/create', [
            'title' => $this->lang->translate('invoices.add_invoice'),
            'clients' => $clients
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::flash('error', 'Invalid request');
            $this->redirect('/invoices/create');
        }

        $user = Session::user();

        $data = [
            'client_id' => $this->getInput('client_id'),
            'title' => Security::sanitizeInput($this->getInput('title', '')),
            'description' => Security::sanitizeInput($this->getInput('description', '')),
            'tax_rate' => (float) $this->getInput('tax_rate', 6.625),
            'due_date' => $this->getInput('due_date') ?: date('Y-m-d', strtotime('+30 days')),
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
            $this->redirect('/invoices/create');
        }

        try {
            $data['invoice_number'] = $this->invoiceModel->generateInvoiceNumber();
            $invoiceId = $this->invoiceModel->create($data);

            // Add items if provided
            $items = $this->getInput('items', []);
            if (is_array($items)) {
                foreach ($items as $index => $item) {
                    if (!empty($item['description']) && !empty($item['quantity']) && !empty($item['unit_price'])) {
                        $this->invoiceModel->addItem($invoiceId, [
                            'description' => Security::sanitizeInput($item['description']),
                            'quantity' => (float) $item['quantity'],
                            'unit_price' => (float) $item['unit_price'],
                            'sort_order' => $index
                        ]);
                    }
                }
            }

            // Update totals
            $this->invoiceModel->updateTotals($invoiceId);

            Session::flash('success', $this->lang->translate('invoices.invoice_saved'));
            $this->clearOldInput();
            $this->redirect('/invoices/' . $invoiceId);
            
        } catch (\Exception $e) {
            error_log("Invoice creation failed: " . $e->getMessage());
            Session::flash('error', 'Failed to save invoice. Please try again.');
            $this->setOldInput($data);
            $this->redirect('/invoices/create');
        }
    }

    public function edit(string $id): void
    {
        $this->requireAuth();

        $invoice = $this->invoiceModel->getInvoiceWithDetails($id);
        if (!$invoice) {
            Session::flash('error', 'Invoice not found');
            $this->redirect('/invoices');
        }

        $clients = $this->clientModel->all();

        $this->view->render('invoices/edit', [
            'title' => $this->lang->translate('invoices.edit_invoice'),
            'invoice' => $invoice,
            'clients' => $clients
        ]);
    }

    public function update(string $id): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::flash('error', 'Invalid request');
            $this->redirect('/invoices/' . $id . '/edit');
        }

        $invoice = $this->invoiceModel->find($id);
        if (!$invoice) {
            Session::flash('error', 'Invoice not found');
            $this->redirect('/invoices');
        }

        $data = [
            'client_id' => $this->getInput('client_id'),
            'title' => Security::sanitizeInput($this->getInput('title', '')),
            'description' => Security::sanitizeInput($this->getInput('description', '')),
            'tax_rate' => (float) $this->getInput('tax_rate', 6.625),
            'due_date' => $this->getInput('due_date'),
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
            $this->redirect('/invoices/' . $id . '/edit');
        }

        try {
            $this->invoiceModel->update($id, $data);

            // Update items
            $items = $this->getInput('items', []);
            if (is_array($items)) {
                // Clear existing items
                $existingItems = $this->invoiceModel->getItems($id);
                foreach ($existingItems as $item) {
                    $this->invoiceModel->deleteItem($item['id']);
                }

                // Add new items
                foreach ($items as $index => $item) {
                    if (!empty($item['description']) && !empty($item['quantity']) && !empty($item['unit_price'])) {
                        $this->invoiceModel->addItem($id, [
                            'description' => Security::sanitizeInput($item['description']),
                            'quantity' => (float) $item['quantity'],
                            'unit_price' => (float) $item['unit_price'],
                            'sort_order' => $index
                        ]);
                    }
                }
            }

            // Update totals
            $this->invoiceModel->updateTotals($id);

            Session::flash('success', $this->lang->translate('invoices.invoice_saved'));
            $this->clearOldInput();
            $this->redirect('/invoices/' . $id);
            
        } catch (\Exception $e) {
            error_log("Invoice update failed: " . $e->getMessage());
            Session::flash('error', 'Failed to update invoice. Please try again.');
            $this->setOldInput($data);
            $this->redirect('/invoices/' . $id . '/edit');
        }
    }

    public function delete(string $id): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
        }

        $invoice = $this->invoiceModel->find($id);
        if (!$invoice) {
            $this->json(['success' => false, 'message' => 'Invoice not found'], 404);
        }

        try {
            $this->invoiceModel->delete($id);
            $this->json(['success' => true, 'message' => 'Invoice deleted successfully']);
        } catch (\Exception $e) {
            error_log("Invoice deletion failed: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to delete invoice'], 500);
        }
    }

    public function recordPayment(string $id): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
        }

        $invoice = $this->invoiceModel->find($id);
        if (!$invoice) {
            $this->json(['success' => false, 'message' => 'Invoice not found'], 404);
        }

        $amount = (float) $this->getInput('amount', 0);
        $notes = trim($this->getInput('notes', ''));

        if ($amount <= 0) {
            $this->json(['success' => false, 'message' => 'Payment amount must be greater than zero'], 400);
        }

        $balanceDue = $this->invoiceModel->getBalanceDue($invoice);
        if ($amount > $balanceDue) {
            $this->json(['success' => false, 'message' => 'Payment amount cannot exceed balance due'], 400);
        }

        try {
            $success = $this->invoiceModel->recordPayment($id, $amount, $notes);
            
            if ($success) {
                $this->json(['success' => true, 'message' => $this->lang->translate('invoices.payment_recorded')]);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to record payment'], 500);
            }
        } catch (\Exception $e) {
            error_log("Payment recording failed: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to record payment'], 500);
        }
    }

    public function updateStatus(string $id): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
        }

        $invoice = $this->invoiceModel->find($id);
        if (!$invoice) {
            $this->json(['success' => false, 'message' => 'Invoice not found'], 404);
        }

        $status = $this->getInput('status');
        $validStatuses = ['draft', 'sent', 'partial', 'paid', 'overdue', 'cancelled'];
        
        if (!in_array($status, $validStatuses)) {
            $this->json(['success' => false, 'message' => 'Invalid status'], 400);
        }

        try {
            $this->invoiceModel->update($id, ['status' => $status]);
            $this->json(['success' => true, 'message' => 'Status updated successfully']);
        } catch (\Exception $e) {
            error_log("Status update failed: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to update status'], 500);
        }
    }
}