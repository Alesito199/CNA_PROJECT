<?php

namespace CNA\Controllers;

use CNA\Models\Client;
use CNA\Models\User;

class DashboardController extends BaseController
{
    public function index(): void
    {
        $this->requireAuth();

        $clientModel = new Client();
        
        // Get dashboard stats
        $stats = [
            'total_clients' => $clientModel->count(),
            'recent_clients' => $clientModel->getRecentClients(5),
        ];

        $this->view->render('dashboard/index', [
            'title' => $this->lang->translate('dashboard.title'),
            'stats' => $stats
        ]);
    }
}