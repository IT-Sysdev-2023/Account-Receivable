<?php

namespace App\Http\Controllers;

use App\Models\CustomerAccountSummary;
use App\Models\ReportModels\CustomerLedger;
use App\Models\TransactionModels\PaymentDetails;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function dashboard()
    {
        return Inertia::render('Dashboard', [
            'ledgerTotals' => $this->getLedgerTotals(),
            'floatingTotals' => $this->getFloatingTotals(),
        ]);
    }

    protected function getLedgerTotals()
    {
        $now = Carbon::now();
        $sevenDaysAgo = $now->copy()->subDays(7);
        $thirtyDaysAgo = $now->copy()->subDays(30);

        $getTotals = function ($startDate = null, $endDate = null) {
            $query = CustomerLedger::where('amount_paid', '>', 0);

            if ($startDate && $endDate) {
                $query->whereBetween('updated_at', [$startDate, $endDate]);
            } elseif ($startDate) {
                $query->where('updated_at', '>=', $startDate);
            }

            return $query->select('type', DB::raw('SUM(amount_paid) as total_amount'))
                ->groupBy('type')
                ->get()
                ->pluck('total_amount', 'type')
                ->toArray();
        };

        $sevenDayTotals = $getTotals($sevenDaysAgo, $now);
        $thirtyDayTotals = $getTotals($thirtyDaysAgo, $now);
        $overallTotals = $getTotals();

        $getLatestUpdates = CustomerLedger::where('amount_paid', '>', 0)
            ->select('type', DB::raw('MAX(updated_at) as latest_updated_at'))
            ->groupBy('type')
            ->get()
            ->pluck('latest_updated_at', 'type')
            ->map(fn($date) => Carbon::parse($date)->diffForHumans())
            ->toArray();

        return [
            'sales_invoice' => [
                'last_7_days' => $sevenDayTotals['Sales Invoice'] ?? 0,
                'last_30_days' => $thirtyDayTotals['Sales Invoice'] ?? 0,
                'overall' => $overallTotals['Sales Invoice'] ?? 0,
                'last_updated' => $getLatestUpdates['Sales Invoice'] ?? null,
            ],
            'charge_invoice' => [
                'last_7_days' => $sevenDayTotals['Charge Invoice'] ?? 0,
                'last_30_days' => $thirtyDayTotals['Charge Invoice'] ?? 0,
                'overall' => $overallTotals['Charge Invoice'] ?? 0,
                'last_updated' => $getLatestUpdates['Charge Invoice'] ?? null,
            ],
            'payment' => [
                'last_7_days' => $sevenDayTotals['Payment'] ?? 0,
                'last_30_days' => $thirtyDayTotals['Payment'] ?? 0,
                'overall' => $overallTotals['Payment'] ?? 0,
                'last_updated' => $getLatestUpdates['Payment'] ?? null,
            ],
            'bg' => [
                'last_7_days' => $sevenDayTotals['BG'] ?? 0,
                'last_30_days' => $thirtyDayTotals['BG'] ?? 0,
                'overall' => $overallTotals['BG'] ?? 0,
                'last_updated' => $getLatestUpdates['BG'] ?? null,
            ],
        ];
    }

    protected function getFloatingTotals()
    {
        $now = Carbon::now();
        $sevenDaysAgo = $now->copy()->subDays(7);
        $thirtyDaysAgo = $now->copy()->subDays(30);

        $getTotals = function ($startDate = null, $endDate = null) {
            $query = PaymentDetails::where('amount_paid', '>', 0)
                ->where('status', 'Floating');

            if ($startDate && $endDate) {
                $query->whereBetween('updated_at', [$startDate, $endDate]);
            } elseif ($startDate) {
                $query->where('updated_at', '>=', $startDate);
            }

            return $query->select(
                'payment_type',
                'check_type',
                DB::raw('SUM(amount_paid) as total_amount')
            )
                ->groupBy('payment_type', 'check_type')
                ->get()
                ->groupBy('payment_type')
                ->map(function ($items) {
                    return $items->pluck('total_amount', 'check_type')->toArray();
                });
        };

        $sevenDayTotals = $getTotals($sevenDaysAgo, $now);
        $thirtyDayTotals = $getTotals($thirtyDaysAgo, $now);
        $overallTotals = $getTotals();

        return [
            'check' => [
                'post_dated' => [
                    'last_7_days' => $sevenDayTotals['Check']['Post Dated Check'] ?? 0,
                    'last_30_days' => $thirtyDayTotals['Check']['Post Dated Check'] ?? 0,
                    'overall' => $overallTotals['Check']['Post Dated Check'] ?? 0,
                ],
                'dated' => [
                    'last_7_days' => $sevenDayTotals['Check']['Dated Check'] ?? 0,
                    'last_30_days' => $thirtyDayTotals['Check']['Dated Check'] ?? 0,
                    'overall' => $overallTotals['Check']['Dated Check'] ?? 0,
                ],
                'total' => [
                    'last_7_days' => ($sevenDayTotals['Check']['Post Dated Check'] ?? 0) + ($sevenDayTotals['Check']['Dated Check'] ?? 0),
                    'last_30_days' => ($thirtyDayTotals['Check']['Post Dated Check'] ?? 0) + ($thirtyDayTotals['Check']['Dated Check'] ?? 0),
                    'overall' => ($overallTotals['Check']['Post Dated Check'] ?? 0) + ($overallTotals['Check']['Dated Check'] ?? 0),
                ],
            ],
            'creditable' => [
                'last_7_days' => $sevenDayTotals['Creditable(WHT)']['N/A'] ?? 0,
                'last_30_days' => $thirtyDayTotals['Creditable(WHT)']['N/A'] ?? 0,
                'overall' => $overallTotals['Creditable(WHT)']['N/A'] ?? 0,
            ],
        ];
    }

    public function getInvoiceChartData(Request $request)
    {
        $period = $request->query('period', '7days');
        $now = Carbon::now();

        $data = [];
        $labels = [];

        switch ($period) {
            case '7days':
                $startDate = $now->copy()->subDays(6)->startOfDay(); // 7 days including today
                $endDate = $now->copy()->endOfDay();

                // Simplified query without created_at in GROUP BY
                $results = CustomerLedger::select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('DAYNAME(created_at) as day'),
                    'type',
                    DB::raw('COUNT(*) as count')
                )
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->whereIn('type', ['Sales Invoice', 'Charge Invoice'])
                    ->groupBy(DB::raw('DATE(created_at)'), DB::raw('DAYNAME(created_at)'), 'type')
                    ->get();

                // Create complete date range
                $dateRange = [];
                for ($i = 0; $i < 7; $i++) {
                    $date = $now->copy()->subDays(6 - $i);
                    $dateRange[$date->format('Y-m-d')] = [
                        'day' => $date->format('l'),
                        'index' => $i
                    ];
                }

                $labels = array_map(fn($entry) => $entry['day'], $dateRange);

                // Initialize data structure
                $data = [
                    'Sales Invoice' => array_fill(0, 7, 0),
                    'Charge Invoice' => array_fill(0, 7, 0)
                ];

                // Fill data
                foreach ($results as $item) {
                    if (isset($dateRange[$item->date])) {
                        $index = $dateRange[$item->date]['index'];
                        $data[$item->type][$index] = $item->count;
                    }
                }
                break;

            case '4weeks':
                $startDate = $now->copy()->subWeeks(4);

                $results = CustomerLedger::select(
                    DB::raw('WEEK(created_at, 1) as week'),
                    'type',
                    DB::raw('COUNT(*) as count')
                )
                    ->whereBetween('created_at', [$startDate, $now])
                    ->whereIn('type', ['Sales Invoice', 'Charge Invoice'])
                    ->groupBy('week', 'type') // Removed created_at
                    ->get();

                // Create week mapping
                $weekMap = [];
                for ($i = 0; $i < 4; $i++) {
                    $weekStart = $now->copy()->subWeeks(3 - $i)->startOfWeek();
                    $weekNum = $weekStart->weekOfYear;
                    $weekMap[$weekNum] = [
                        'label' => "Week " . ($i + 1),
                        'index' => $i
                    ];
                }

                // Initialize data
                $data = [
                    'Sales Invoice' => array_fill(0, 4, 0),
                    'Charge Invoice' => array_fill(0, 4, 0)
                ];
                $labels = array_column($weekMap, 'label');

                // Fill data
                foreach ($results as $item) {
                    if (isset($weekMap[$item->week])) {
                        $index = $weekMap[$item->week]['index'];
                        $data[$item->type][$index] = $item->count;
                    }
                }
                break;

            case 'months':
                $startDate = $now->copy()->startOfYear();

                $results = CustomerLedger::select(
                    DB::raw('MONTH(created_at) as month_num'),
                    DB::raw('MONTHNAME(created_at) as month'),
                    'type',
                    DB::raw('COUNT(*) as count')
                )
                    ->whereBetween('created_at', [$startDate, $now])
                    ->whereIn('type', ['Sales Invoice', 'Charge Invoice'])
                    ->groupBy('month_num', 'month', 'type') // Group by month number and name
                    ->get();

                // Create month mapping
                $monthMap = [];
                for ($i = 1; $i <= $now->month; $i++) {
                    $monthDate = $now->copy()->startOfYear()->addMonths($i - 1);
                    $monthMap[$i] = [
                        'name' => $monthDate->format('F'),
                        'index' => $i - 1
                    ];
                }
                $labels = array_column($monthMap, 'name');

                // Initialize data
                $data = [
                    'Sales Invoice' => array_fill(0, count($monthMap), 0),
                    'Charge Invoice' => array_fill(0, count($monthMap), 0)
                ];

                // Fill data
                foreach ($results as $item) {
                    if (isset($monthMap[$item->month_num])) {
                        $index = $monthMap[$item->month_num]['index'];
                        $data[$item->type][$index] = $item->count;
                    }
                }
                break;
        }

        return [
            'labels' => array_values($labels),
            'datasets' => [
                [
                    'label' => 'Sales Invoice',
                    'data' => $data['Sales Invoice'],
                ],
                [
                    'label' => 'Charge Invoice',
                    'data' => $data['Charge Invoice'],
                ]
            ]
        ];
    }

    public function getInvoicePieData(Request $request)
    {
        $period = $request->query('period', '7days');
        $now = Carbon::now();

        $query = DB::table('invoice_items');

        switch ($period) {
            case '7days':
                $startDate = $now->copy()->subDays(6)->startOfDay();
                $query->whereBetween('created_at', [$startDate, $now]);
                break;
            case '4weeks':
                $startDate = $now->copy()->subWeeks(4);
                $query->whereBetween('created_at', [$startDate, $now]);
                break;
                // No date filter for 'months' (overall total)
        }

        $results = $query->select(
            'item_name',
            DB::raw('SUM(quantity) as total_quantity')
        )
            ->groupBy('item_name')
            ->orderBy('total_quantity', 'desc')
            ->get();

        // Prepare data for chart
        $labels = [];
        $data = [];
        $backgroundColors = [];
        $borderColors = [];

        // Generate distinct colors for each item
        foreach ($results as $index => $item) {
            $labels[] = $item->item_name;
            $data[] = $item->total_quantity;

            // Generate HSL color with golden angle approximation
            $hue = ($index * 137.508) % 360;
            $backgroundColors[] = "hsla({$hue}, 70%, 60%, 0.5)";
            $borderColors[] = "hsla({$hue}, 70%, 60%, 1)";
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Items Sold',
                    'data' => $data,
                    'backgroundColor' => $backgroundColors,
                    'borderColor' => $borderColors,
                ]
            ]
        ];
    }

    public function getCustomerAccountsSummary(Request $request)
    {
        $period = $request->query('period', 'overall');
        $now = now();

        // Determine date filter
        switch ($period) {
            case '7days':
                $startDate = $now->copy()->subDays(7);
                break;
            case '4weeks':
                $startDate = $now->copy()->subWeeks(4);
                break;
            case 'overall':
            default:
                $startDate = $now->copy()->startOfYear();
                break;
        }

        // Query view
        $customers = CustomerAccountSummary::query()
            ->whereBetween('last_activity', [$startDate, $now])
            ->orderBy('customer_name')
            ->paginate(10);

        return response()->json($customers);
    }
}
