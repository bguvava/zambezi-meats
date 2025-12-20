<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

/**
 * User export service for PDF generation.
 *
 * @requirement USER-009 Implement user export to PDF
 */
class UserExportService
{
    /**
     * Export users to PDF.
     */
    public function exportToPdf(Request $request): \Illuminate\Http\Response
    {
        $query = User::query();

        // Apply same filters as index method
        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }

        if ($request->filled('role')) {
            $query->role($request->input('role'));
        }

        if ($request->filled('status')) {
            $query->status($request->input('status'));
        }

        // Get all matching users (no pagination for export)
        $users = $query->orderBy('created_at', 'desc')->get();

        // Generate PDF
        $pdf = Pdf::loadView('pdf.users', [
            'users' => $users,
            'filters' => [
                'search' => $request->input('search'),
                'role' => $request->input('role'),
                'status' => $request->input('status'),
            ],
            'generatedAt' => now()->format('d M Y H:i:s'),
            'totalUsers' => $users->count(),
        ]);

        // Configure PDF settings
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        return $pdf->download('zambezi-meats-users-' . now()->format('Y-m-d') . '.pdf');
    }
}
