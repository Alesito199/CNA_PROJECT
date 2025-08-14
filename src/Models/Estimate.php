<?php

namespace CNA\Models;

class Estimate extends BaseModel
{
    protected string $table = 'estimates';
    protected array $fillable = [
        'estimate_number', 'client_id', 'user_id', 'title', 'description',
        'subtotal', 'tax_rate', 'tax_amount', 'total', 'status', 
        'valid_until', 'notes'
    ];

    public function getItems(string $estimateId): array
    {
        $sql = "SELECT * FROM estimate_items WHERE estimate_id = :estimate_id ORDER BY sort_order, created_at";
        return $this->db->fetchAll($sql, ['estimate_id' => $estimateId]);
    }

    public function addItem(string $estimateId, array $itemData): string
    {
        $itemData['estimate_id'] = $estimateId;
        $itemData['id'] = \CNA\Utils\Security::generateUUID();
        $itemData['total'] = $itemData['quantity'] * $itemData['unit_price'];
        
        return $this->db->insert('estimate_items', $itemData);
    }

    public function updateItem(string $itemId, array $itemData): bool
    {
        $itemData['total'] = $itemData['quantity'] * $itemData['unit_price'];
        $updated = $this->db->update('estimate_items', $itemData, ['id' => $itemId]);
        return $updated > 0;
    }

    public function deleteItem(string $itemId): bool
    {
        $deleted = $this->db->delete('estimate_items', ['id' => $itemId]);
        return $deleted > 0;
    }

    public function calculateTotals(string $estimateId): array
    {
        // Get all items
        $items = $this->getItems($estimateId);
        $subtotal = array_sum(array_column($items, 'total'));
        
        // Get tax rate
        $estimate = $this->find($estimateId);
        $taxRate = $estimate['tax_rate'] ?? 6.625;
        
        $taxAmount = ($subtotal * $taxRate) / 100;
        $total = $subtotal + $taxAmount;
        
        return [
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'total' => $total
        ];
    }

    public function updateTotals(string $estimateId): bool
    {
        $totals = $this->calculateTotals($estimateId);
        return $this->update($estimateId, $totals);
    }

    public function generateEstimateNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        
        // Get the last estimate number for this month
        $sql = "SELECT estimate_number FROM estimates WHERE estimate_number LIKE :pattern ORDER BY estimate_number DESC LIMIT 1";
        $pattern = "EST-{$year}{$month}-%";
        $result = $this->db->fetch($sql, ['pattern' => $pattern]);
        
        if ($result) {
            // Extract the sequence number and increment
            $parts = explode('-', $result['estimate_number']);
            $sequence = (int) $parts[2] + 1;
        } else {
            $sequence = 1;
        }
        
        return sprintf("EST-%s%s-%04d", $year, $month, $sequence);
    }

    public function getEstimatesWithClients(int $limit = 0, int $offset = 0): array
    {
        $sql = "
            SELECT e.*, 
                   c.first_name, c.last_name, c.company,
                   u.first_name as user_first_name, u.last_name as user_last_name
            FROM estimates e
            JOIN clients c ON e.client_id = c.id
            JOIN users u ON e.user_id = u.id
            ORDER BY e.created_at DESC
        ";
        
        if ($limit > 0) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        
        return $this->db->fetchAll($sql);
    }

    public function getEstimateWithDetails(string $id): ?array
    {
        $sql = "
            SELECT e.*, 
                   c.first_name, c.last_name, c.company, c.email, c.phone, c.address, c.city, c.state, c.zip_code,
                   u.first_name as user_first_name, u.last_name as user_last_name
            FROM estimates e
            JOIN clients c ON e.client_id = c.id
            JOIN users u ON e.user_id = u.id
            WHERE e.id = :id
        ";
        
        $estimate = $this->db->fetch($sql, ['id' => $id]);
        if ($estimate) {
            $estimate['items'] = $this->getItems($id);
        }
        
        return $estimate;
    }

    public function searchEstimates(string $query): array
    {
        $sql = "
            SELECT e.*, 
                   c.first_name, c.last_name, c.company,
                   u.first_name as user_first_name, u.last_name as user_last_name
            FROM estimates e
            JOIN clients c ON e.client_id = c.id
            JOIN users u ON e.user_id = u.id
            WHERE e.estimate_number ILIKE :query 
               OR e.title ILIKE :query 
               OR e.description ILIKE :query
               OR c.first_name ILIKE :query 
               OR c.last_name ILIKE :query 
               OR c.company ILIKE :query
            ORDER BY e.created_at DESC
        ";
        
        return $this->db->fetchAll($sql, ['query' => "%{$query}%"]);
    }

    public function getEstimatesByStatus(string $status): array
    {
        $sql = "
            SELECT e.*, 
                   c.first_name, c.last_name, c.company
            FROM estimates e
            JOIN clients c ON e.client_id = c.id
            WHERE e.status = :status
            ORDER BY e.created_at DESC
        ";
        
        return $this->db->fetchAll($sql, ['status' => $status]);
    }

    public function convertToInvoice(string $estimateId, string $userId): ?string
    {
        $estimate = $this->getEstimateWithDetails($estimateId);
        if (!$estimate) {
            return null;
        }

        $this->beginTransaction();
        
        try {
            $invoiceModel = new Invoice();
            
            // Create invoice
            $invoiceData = [
                'estimate_id' => $estimateId,
                'client_id' => $estimate['client_id'],
                'user_id' => $userId,
                'title' => $estimate['title'],
                'description' => $estimate['description'],
                'subtotal' => $estimate['subtotal'],
                'tax_rate' => $estimate['tax_rate'],
                'tax_amount' => $estimate['tax_amount'],
                'total' => $estimate['total'],
                'status' => 'draft',
                'due_date' => date('Y-m-d', strtotime('+30 days')),
                'notes' => $estimate['notes']
            ];
            
            $invoiceData['invoice_number'] = $invoiceModel->generateInvoiceNumber();
            $invoiceId = $invoiceModel->create($invoiceData);
            
            // Copy items
            foreach ($estimate['items'] as $item) {
                $invoiceModel->addItem($invoiceId, [
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'sort_order' => $item['sort_order']
                ]);
            }
            
            // Update estimate status
            $this->update($estimateId, ['status' => 'approved']);
            
            $this->commit();
            return $invoiceId;
            
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
}