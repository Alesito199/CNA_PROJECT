<?php

namespace CNA\Models;

class Invoice extends BaseModel
{
    protected string $table = 'invoices';
    protected array $fillable = [
        'invoice_number', 'estimate_id', 'client_id', 'user_id', 'title', 'description',
        'subtotal', 'tax_rate', 'tax_amount', 'total', 'amount_paid', 'status', 
        'due_date', 'paid_date', 'notes'
    ];

    public function getItems(string $invoiceId): array
    {
        $sql = "SELECT * FROM invoice_items WHERE invoice_id = :invoice_id ORDER BY sort_order, created_at";
        return $this->db->fetchAll($sql, ['invoice_id' => $invoiceId]);
    }

    public function addItem(string $invoiceId, array $itemData): string
    {
        $itemData['invoice_id'] = $invoiceId;
        $itemData['id'] = \CNA\Utils\Security::generateUUID();
        $itemData['total'] = $itemData['quantity'] * $itemData['unit_price'];
        
        return $this->db->insert('invoice_items', $itemData);
    }

    public function updateItem(string $itemId, array $itemData): bool
    {
        $itemData['total'] = $itemData['quantity'] * $itemData['unit_price'];
        $updated = $this->db->update('invoice_items', $itemData, ['id' => $itemId]);
        return $updated > 0;
    }

    public function deleteItem(string $itemId): bool
    {
        $deleted = $this->db->delete('invoice_items', ['id' => $itemId]);
        return $deleted > 0;
    }

    public function calculateTotals(string $invoiceId): array
    {
        // Get all items
        $items = $this->getItems($invoiceId);
        $subtotal = array_sum(array_column($items, 'total'));
        
        // Get tax rate
        $invoice = $this->find($invoiceId);
        $taxRate = $invoice['tax_rate'] ?? 6.625;
        
        $taxAmount = ($subtotal * $taxRate) / 100;
        $total = $subtotal + $taxAmount;
        
        return [
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'total' => $total
        ];
    }

    public function updateTotals(string $invoiceId): bool
    {
        $totals = $this->calculateTotals($invoiceId);
        return $this->update($invoiceId, $totals);
    }

    public function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        
        // Get the last invoice number for this month
        $sql = "SELECT invoice_number FROM invoices WHERE invoice_number LIKE :pattern ORDER BY invoice_number DESC LIMIT 1";
        $pattern = "INV-{$year}{$month}-%";
        $result = $this->db->fetch($sql, ['pattern' => $pattern]);
        
        if ($result) {
            // Extract the sequence number and increment
            $parts = explode('-', $result['invoice_number']);
            $sequence = (int) $parts[2] + 1;
        } else {
            $sequence = 1;
        }
        
        return sprintf("INV-%s%s-%04d", $year, $month, $sequence);
    }

    public function getInvoicesWithClients(int $limit = 0, int $offset = 0): array
    {
        $sql = "
            SELECT i.*, 
                   c.first_name, c.last_name, c.company,
                   u.first_name as user_first_name, u.last_name as user_last_name
            FROM invoices i
            JOIN clients c ON i.client_id = c.id
            JOIN users u ON i.user_id = u.id
            ORDER BY i.created_at DESC
        ";
        
        if ($limit > 0) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        
        return $this->db->fetchAll($sql);
    }

    public function getInvoiceWithDetails(string $id): ?array
    {
        $sql = "
            SELECT i.*, 
                   c.first_name, c.last_name, c.company, c.email, c.phone, c.address, c.city, c.state, c.zip_code,
                   u.first_name as user_first_name, u.last_name as user_last_name
            FROM invoices i
            JOIN clients c ON i.client_id = c.id
            JOIN users u ON i.user_id = u.id
            WHERE i.id = :id
        ";
        
        $invoice = $this->db->fetch($sql, ['id' => $id]);
        if ($invoice) {
            $invoice['items'] = $this->getItems($id);
        }
        
        return $invoice;
    }

    public function searchInvoices(string $query): array
    {
        $sql = "
            SELECT i.*, 
                   c.first_name, c.last_name, c.company,
                   u.first_name as user_first_name, u.last_name as user_last_name
            FROM invoices i
            JOIN clients c ON i.client_id = c.id
            JOIN users u ON i.user_id = u.id
            WHERE i.invoice_number ILIKE :query 
               OR i.title ILIKE :query 
               OR i.description ILIKE :query
               OR c.first_name ILIKE :query 
               OR c.last_name ILIKE :query 
               OR c.company ILIKE :query
            ORDER BY i.created_at DESC
        ";
        
        return $this->db->fetchAll($sql, ['query' => "%{$query}%"]);
    }

    public function getInvoicesByStatus(string $status): array
    {
        $sql = "
            SELECT i.*, 
                   c.first_name, c.last_name, c.company
            FROM invoices i
            JOIN clients c ON i.client_id = c.id
            WHERE i.status = :status
            ORDER BY i.created_at DESC
        ";
        
        return $this->db->fetchAll($sql, ['status' => $status]);
    }

    public function recordPayment(string $invoiceId, float $amount, string $notes = ''): bool
    {
        $invoice = $this->find($invoiceId);
        if (!$invoice) {
            return false;
        }

        $newAmountPaid = $invoice['amount_paid'] + $amount;
        $total = $invoice['total'];
        
        $updateData = [
            'amount_paid' => $newAmountPaid
        ];
        
        // Update status based on payment
        if ($newAmountPaid >= $total) {
            $updateData['status'] = 'paid';
            $updateData['paid_date'] = date('Y-m-d');
        } elseif ($newAmountPaid > 0) {
            $updateData['status'] = 'partial';
        }
        
        if ($notes) {
            $currentNotes = $invoice['notes'] ?? '';
            $paymentNote = "\nPayment: $" . number_format($amount, 2) . " on " . date('Y-m-d') . " - " . $notes;
            $updateData['notes'] = $currentNotes . $paymentNote;
        }
        
        return $this->update($invoiceId, $updateData);
    }

    public function getBalanceDue(array $invoice): float
    {
        return max(0, $invoice['total'] - $invoice['amount_paid']);
    }

    public function isOverdue(array $invoice): bool
    {
        if ($invoice['status'] === 'paid' || !$invoice['due_date']) {
            return false;
        }
        
        return strtotime($invoice['due_date']) < time();
    }

    public function getUnpaidInvoices(): array
    {
        $sql = "
            SELECT i.*, 
                   c.first_name, c.last_name, c.company
            FROM invoices i
            JOIN clients c ON i.client_id = c.id
            WHERE i.status IN ('sent', 'partial', 'overdue')
            ORDER BY i.due_date ASC
        ";
        
        return $this->db->fetchAll($sql);
    }

    public function updateOverdueInvoices(): int
    {
        $sql = "
            UPDATE invoices 
            SET status = 'overdue' 
            WHERE status IN ('sent', 'partial') 
            AND due_date < CURRENT_DATE
        ";
        
        $stmt = $this->db->query($sql);
        return $stmt->rowCount();
    }

    public function getMonthlyRevenue(int $year = null, int $month = null): array
    {
        $year = $year ?: date('Y');
        $month = $month ?: date('n');
        
        $sql = "
            SELECT 
                COALESCE(SUM(total), 0) as total_billed,
                COALESCE(SUM(amount_paid), 0) as total_paid,
                COUNT(*) as invoice_count
            FROM invoices 
            WHERE EXTRACT(YEAR FROM created_at) = :year 
            AND EXTRACT(MONTH FROM created_at) = :month
        ";
        
        return $this->db->fetch($sql, compact('year', 'month')) ?: [];
    }
}