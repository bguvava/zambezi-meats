<?php

declare(strict_types=1);

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;

/**
 * PDF Export Service.
 *
 * Handles export of product and service data to PDF format.
 *
 * @requirement PROD-014 PDF export for products with filters
 * @requirement SERV-012 PDF export for services
 */
class PdfExportService
{
    /**
     * Export products to PDF.
     *
     * @param  Collection  $products  Collection of products to export
     * @param  array  $filters  Applied filters for report header
     * @return \Illuminate\Http\Response
     */
    public function exportProducts(Collection $products, array $filters = [])
    {
        $data = [
            'products' => $products,
            'filters' => $filters,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'total_products' => $products->count(),
            'total_stock_value' => $products->sum(function ($product) {
                return $product->price_aud * $product->stock;
            }),
        ];

        $pdf = Pdf::loadView('pdf.products', $data);

        // Set paper size and orientation
        $pdf->setPaper('a4', 'landscape');

        // Set options
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'sans-serif',
        ]);

        $filename = 'products-export-' . now()->format('Y-m-d-His') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Export product stock report to PDF.
     *
     * @param  Collection  $products  Collection of products with stock info
     * @return \Illuminate\Http\Response
     */
    public function exportStockReport(Collection $products)
    {
        $data = [
            'products' => $products,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'total_products' => $products->count(),
            'low_stock_count' => $products->where('stock', '<', 10)->count(),
            'out_of_stock_count' => $products->where('stock', 0)->count(),
            'total_stock_value' => $products->sum(function ($product) {
                return $product->price_aud * $product->stock;
            }),
        ];

        $pdf = Pdf::loadView('pdf.stock-report', $data);

        $pdf->setPaper('a4', 'portrait');

        $filename = 'stock-report-' . now()->format('Y-m-d-His') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Export product price list to PDF.
     *
     * @param  Collection  $products  Collection of products for price list
     * @param  array  $options  Additional options (show_images, show_descriptions, etc.)
     * @return \Illuminate\Http\Response
     */
    public function exportPriceList(Collection $products, array $options = [])
    {
        $data = [
            'products' => $products->sortBy('category.name')->groupBy('category.name'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'options' => array_merge([
                'show_images' => true,
                'show_descriptions' => false,
                'show_sku' => true,
                'show_stock' => false,
            ], $options),
        ];

        $pdf = Pdf::loadView('pdf.price-list', $data);

        $pdf->setPaper('a4', 'portrait');

        $filename = 'price-list-' . now()->format('Y-m-d-His') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Export services to PDF.
     *
     * @requirement SERV-012 PDF export for services
     *
     * @param  Collection  $services  Collection of services to export
     * @param  array  $filters  Applied filters for report header
     * @return \Illuminate\Http\Response
     */
    public function exportServices(Collection $services, array $filters = [])
    {
        $data = [
            'services' => $services,
            'filters' => $filters,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'total_services' => $services->count(),
            'one_time_count' => $services->where('billing_cycle', 'one_time')->count(),
            'recurring_count' => $services->whereIn('billing_cycle', ['monthly', 'quarterly', 'yearly'])->count(),
            'total_one_time_value' => $services->where('billing_cycle', 'one_time')->sum('price_aud'),
        ];

        $pdf = Pdf::loadView('pdf.services', $data);

        // Set paper size and orientation
        $pdf->setPaper('a4', 'landscape');

        // Set options
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'sans-serif',
        ]);

        $filename = 'services-export-' . now()->format('Y-m-d-His') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Export service price list to PDF.
     *
     * @param  Collection  $services  Collection of services for price list
     * @param  array  $options  Additional options
     * @return \Illuminate\Http\Response
     */
    public function exportServicePriceList(Collection $services, array $options = [])
    {
        $data = [
            'services' => $services->sortBy('category.name')->groupBy('category.name'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'options' => array_merge([
                'show_features' => true,
                'show_descriptions' => true,
            ], $options),
        ];

        $pdf = Pdf::loadView('pdf.service-price-list', $data);

        $pdf->setPaper('a4', 'portrait');

        $filename = 'service-price-list-' . now()->format('Y-m-d-His') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Format currency for PDF.
     *
     * @param  float  $amount
     * @param  string  $currency
     * @return string
     */
    protected function formatCurrency(float $amount, string $currency = 'AUD'): string
    {
        $symbols = [
            'AUD' => 'A$',
            'USD' => '$',
            'ZWL' => 'Z$',
        ];

        $symbol = $symbols[$currency] ?? '$';

        return $symbol . number_format($amount, 2);
    }
}
