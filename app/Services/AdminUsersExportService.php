<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminUsersExportService
{
    /** @var list<string> */
    private const HEADINGS = [
        'ID',
        'Name',
        'Email',
        'Phone',
        'Status',
        'Registration Source',
        'Address',
        'Latitude',
        'Longitude',
        'Orders Count',
        'Cart Items',
        'Wishlist Items',
        'Login Count',
        'Last Login',
        'Last Login IP',
        'Web Last Login',
        'App Installed',
        'App Installed At',
        'Device Type',
        'Device Brand',
        'Device Model',
        'OS Version',
        'App Version',
        'Google Linked',
        'Email Verified At',
        'Notifications Enabled',
        'Registered At',
        'Updated At',
    ];

    /** @var list<string> */
    private const HEADER_COLORS = [
        '2BAE49',
        '1E8E3E',
        'F57C00',
        'D32F2F',
        '2BAE49',
        'F57C00',
        '1E8E3E',
        'D32F2F',
        '2BAE49',
        'F57C00',
        'D32F2F',
        '1E8E3E',
        '2BAE49',
        'F57C00',
        'D32F2F',
        '1E8E3E',
        '2BAE49',
        'F57C00',
        'D32F2F',
        '1E8E3E',
        '2BAE49',
        'F57C00',
        'D32F2F',
        '1E8E3E',
        '2BAE49',
        'F57C00',
        'D32F2F',
        '1E8E3E',
    ];

    /**
     * @param  Collection<int, User>  $users
     */
    public function downloadExcel(Collection $users): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('FreshNonVeg Users');

        foreach (self::HEADINGS as $colIndex => $heading) {
            $cell = $sheet->getCell([$colIndex + 1, 1]);
            $cell->setValue($heading);
            $columnLetter = $cell->getColumn();
            $sheet->getStyle($columnLetter.'1')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => self::HEADER_COLORS[$colIndex] ?? '2BAE49'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ]);
            $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
        }
        $sheet->getRowDimension(1)->setRowHeight(28);

        $row = 2;
        foreach ($users as $user) {
            $values = $this->rowValues($user);
            foreach ($values as $colIndex => $value) {
                $sheet->setCellValue([$colIndex + 1, $row], $value);
            }
            if ($row % 2 === 0) {
                $lastCol = $sheet->getCell([count(self::HEADINGS), $row])->getColumn();
                $sheet->getStyle('A'.$row.':'.$lastCol.$row)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F0FDF4'],
                    ],
                ]);
            }
            $row++;
        }

        $filename = 'freshnonveg-users-'.now()->format('Y-m-d-His').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * @param  Collection<int, User>  $users
     */
    public function downloadCsv(Collection $users): StreamedResponse
    {
        $filename = 'freshnonveg-users-'.now()->format('Y-m-d-His').'.csv';

        return Response::streamDownload(function () use ($users) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, self::HEADINGS);
            foreach ($users as $user) {
                fputcsv($handle, $this->rowValues($user));
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return list<int|string|null>
     */
    private function rowValues(User $user): array
    {
        return [
            $user->id,
            $user->name,
            $user->email,
            $user->phone,
            $user->isActive() ? 'Active' : 'Inactive',
            $user->registration_source,
            $user->address,
            $user->latitude,
            $user->longitude,
            $user->orders_count ?? $user->orders()->count(),
            $user->cart_items_count ?? $user->cartItems()->count(),
            $user->wishlist_items_count ?? $user->wishlistItems()->count(),
            $user->login_count,
            $user->last_login_at?->format('Y-m-d H:i:s'),
            $user->last_login_ip,
            $user->web_last_login_at?->format('Y-m-d H:i:s'),
            $user->is_app_installed ? 'Yes' : 'No',
            $user->app_installed_at?->format('Y-m-d H:i:s'),
            $user->device_type,
            $user->device_brand,
            $user->device_model,
            $user->os_version,
            $user->app_version,
            $user->google_id ? 'Yes' : 'No',
            $user->email_verified_at?->format('Y-m-d H:i:s'),
            $user->notifications_enabled ? 'Yes' : 'No',
            $user->created_at?->format('Y-m-d H:i:s'),
            $user->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
