<?php

namespace CNA\Models;

class Client extends BaseModel
{
    protected string $table = 'clients';
    protected array $fillable = [
        'first_name', 'last_name', 'company', 'email', 'phone', 
        'address', 'city', 'state', 'zip_code', 'notes'
    ];

    public function getFullName(array $client): string
    {
        return trim($client['first_name'] . ' ' . $client['last_name']);
    }

    public function getDisplayName(array $client): string
    {
        $fullName = $this->getFullName($client);
        if (!empty($client['company'])) {
            return $fullName . ' (' . $client['company'] . ')';
        }
        return $fullName;
    }

    public function searchClients(string $query): array
    {
        return $this->search($query, ['first_name', 'last_name', 'company', 'email', 'phone']);
    }

    public function getClientEstimates(string $clientId): array
    {
        $sql = "
            SELECT e.*, u.first_name as user_first_name, u.last_name as user_last_name
            FROM estimates e
            JOIN users u ON e.user_id = u.id
            WHERE e.client_id = :client_id
            ORDER BY e.created_at DESC
        ";
        
        return $this->db->fetchAll($sql, ['client_id' => $clientId]);
    }

    public function getClientInvoices(string $clientId): array
    {
        $sql = "
            SELECT i.*, u.first_name as user_first_name, u.last_name as user_last_name
            FROM invoices i
            JOIN users u ON i.user_id = u.id
            WHERE i.client_id = :client_id
            ORDER BY i.created_at DESC
        ";
        
        return $this->db->fetchAll($sql, ['client_id' => $clientId]);
    }

    public function getClientStats(string $clientId): array
    {
        // Get estimates stats
        $estimatesQuery = "
            SELECT 
                COUNT(*) as total_estimates,
                COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_estimates,
                COALESCE(SUM(total), 0) as total_estimate_value
            FROM estimates 
            WHERE client_id = :client_id
        ";
        $estimateStats = $this->db->fetch($estimatesQuery, ['client_id' => $clientId]);

        // Get invoices stats
        $invoicesQuery = "
            SELECT 
                COUNT(*) as total_invoices,
                COUNT(CASE WHEN status = 'paid' THEN 1 END) as paid_invoices,
                COALESCE(SUM(total), 0) as total_invoice_value,
                COALESCE(SUM(amount_paid), 0) as total_paid_amount
            FROM invoices 
            WHERE client_id = :client_id
        ";
        $invoiceStats = $this->db->fetch($invoicesQuery, ['client_id' => $clientId]);

        return array_merge($estimateStats ?: [], $invoiceStats ?: []);
    }

    public function getRecentClients(int $limit = 5): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT :limit";
        return $this->db->fetchAll($sql, ['limit' => $limit]);
    }

    public function isEmailTaken(string $email, string $excludeId = null): bool
    {
        return $this->exists('email', $email, $excludeId);
    }
}