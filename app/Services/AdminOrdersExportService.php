<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Support\CheckoutDelivery;
use App\Support\Company;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminOrdersExportService
{
    /** @var list<string> */
    private const ORDER_HEADINGS = [
        'Order ID', 'Order No', 'Order Date', 'Status', 'Payment Method', 'Payment ID',
        'Customer ID', 'Customer Name', 'Customer Email', 'Customer Phone',
        'Delivery Phone', 'Delivery Address', 'Post Office', 'City', 'State', 'Pincode',
        'Delivery Type', 'Delivery Date', 'Delivery Slot',
        'Items Count', 'Items', 'Subtotal', 'Coupon', 'Discount', 'Delivery Fee', 'Wallet Used', 'Grand Total',
        'Customer Notes', 'Cancelled At', 'Cancel Reason',
    ];

    /** @var list<string> */
    private const ITEM_HEADINGS = [
        'Order No', 'Order Date', 'Status', 'Customer Name', 'Customer Phone',
        'Product ID', 'Product', 'Weight', 'Preference Note', 'Quantity', 'Unit Price', 'Line Total', 'Image URL',
    ];

    /** @param  Collection<int, Order>  $orders */
    public function downloadExcel(Collection $orders, string $filenameBase = 'freshnonveg-orders'): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;

        $this->writeCompanySheet($spreadsheet->getActiveSheet());

        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Orders');
        $this->writeSheet($sheet, self::ORDER_HEADINGS, $orders->map(fn (Order $o) => $this->orderRow($o))->all());

        $items = $spreadsheet->createSheet();
        $items->setTitle('Order Items');
        $this->writeSheet($items, self::ITEM_HEADINGS, $orders->flatMap(
            fn (Order $o) => $o->items->map(fn (OrderItem $i) => $this->itemRow($o, $i))
        )->all());

        $spreadsheet->setActiveSheetIndex(1);

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $filenameBase.'-'.now()->format('Y-m-d-His').'.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /** @param  Collection<int, Order>  $orders */
    public function downloadCsv(Collection $orders): StreamedResponse
    {
        return Response::streamDownload(function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            $co = Company::billHeader();
            fputcsv($handle, ['Seller', $co['legal_name']]);
            fputcsv($handle, ['GSTIN', $co['gstin']]);
            fputcsv($handle, ['PAN', $co['pan']]);
            fputcsv($handle, ['Address', $co['address']]);
            fputcsv($handle, []);
            fputcsv($handle, self::ORDER_HEADINGS);
            foreach ($orders as $order) {
                fputcsv($handle, $this->orderRow($order));
            }
            fclose($handle);
        }, 'freshnonveg-orders-'.now()->format('Y-m-d-His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function writeCompanySheet(Worksheet $sheet): void
    {
        $co = Company::billHeader();
        $sheet->setTitle('Company');
        $rows = [
            ['Tax Invoice — Seller'],
            ['Brand', $co['brand']],
            ['Legal name', $co['legal_name']],
            ['GSTIN', $co['gstin']],
            ['PAN', $co['pan']],
            ['GST type', $co['registration_type']],
            ['State', $co['state']],
            ['Principal place of business', $co['address']],
        ];
        foreach ($rows as $i => $row) {
            $sheet->setCellValue([1, $i + 1], $row[0]);
            if (isset($row[1])) {
                $sheet->setCellValue([2, $i + 1], $row[1]);
            }
        }
        $sheet->getStyle('A1:B1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2BAE49']],
        ]);
        $sheet->getStyle('A2:A8')->getFont()->setBold(true);
        $sheet->getColumnDimension('A')->setWidth(32);
        $sheet->getColumnDimension('B')->setWidth(70);
        $sheet->mergeCells('A1:B1');
    }

    /**
     * @param  list<string>  $headings
     * @param  list<list<int|float|string|null>>  $rows
     */
    private function writeSheet(Worksheet $sheet, array $headings, array $rows): void
    {
        $colors = ['2BAE49', 'F57C00', 'D32F2F', '1E8E3E'];

        foreach ($headings as $i => $heading) {
            $cell = $sheet->getCell([$i + 1, 1]);
            $cell->setValue($heading);
            $col = $cell->getColumn();
            $sheet->getStyle($col.'1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colors[$i % count($colors)]]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            ]);
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getRowDimension(1)->setRowHeight(28);
        $sheet->freezePane('A2');

        $lastCol = $sheet->getCell([count($headings), 1])->getColumn();
        foreach ($rows as $r => $values) {
            $rowNo = $r + 2;
            foreach ($values as $i => $value) {
                $sheet->setCellValue([$i + 1, $rowNo], $value);
            }
            if ($rowNo % 2 === 0) {
                $sheet->getStyle('A'.$rowNo.':'.$lastCol.$rowNo)->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F0FDF4');
            }
        }
    }

    /** @return list<int|float|string|null> */
    private function orderRow(Order $order): array
    {
        $subtotal = $order->items->sum(fn (OrderItem $i) => (float) $i->unit_price * (int) $i->quantity);

        return [
            $order->id,
            $order->order_number,
            $order->created_at?->format('Y-m-d H:i:s'),
            $order->status,
            $order->payment_method,
            $order->cashfree_payment_id,
            $order->user_id,
            $order->user?->name,
            $order->user?->email,
            $order->user?->phone,
            $order->phone,
            $order->delivery_address,
            $order->post_office,
            $order->city,
            $order->state,
            $order->pincode,
            $order->delivery_schedule_type,
            $order->delivery_date?->format('Y-m-d'),
            $this->slotLabel($order->delivery_time),
            $order->items->sum('quantity'),
            $order->items->map(fn (OrderItem $i) => $i->product_name.' ('.$i->weight_label.') x'.$i->quantity)->implode('; '),
            round($subtotal, 2),
            $order->coupon_code,
            (float) $order->discount_amount,
            (float) $order->delivery_fee,
            (float) $order->wallet_amount,
            (float) $order->total,
            $order->customer_notes,
            $order->cancelled_at?->format('Y-m-d H:i:s'),
            $order->cancel_reason,
        ];
    }

    /** @var array<string, string> */
    private array $slotLabels = [];

    private function slotLabel(?string $key): ?string
    {
        if (! $key) {
            return null;
        }

        return $this->slotLabels[$key] ??= (CheckoutDelivery::labelFor($key) ?: $key);
    }

    /** @return list<int|float|string|null> */
    private function itemRow(Order $order, OrderItem $item): array
    {
        return [
            $order->order_number,
            $order->created_at?->format('Y-m-d H:i:s'),
            $order->status,
            $order->user?->name,
            $order->user?->phone ?? $order->phone,
            $item->product_id,
            $item->product_name,
            $item->weight_label,
            $item->preference_note,
            (int) $item->quantity,
            (float) $item->unit_price,
            round((float) $item->unit_price * (int) $item->quantity, 2),
            $item->product?->images->first()?->url(),
        ];
    }
}
