<?php
/*
 * File name: PaymentDataTable.php
 * Last modified: 2021.05.07 at 19:12:31
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */

namespace App\DataTables;

use App\Models\CustomField;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Services\DataTable;

class PaymentDataTable extends DataTable
{
    /**
     * custom fields columns
     * @var array
     */
    public static $customFields = [];

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return DataTableAbstract
     */
    public function dataTable($query)
    {
        $dataTable = new EloquentDataTable($query);
        $columns = array_column($this->getColumns(), 'data');
        $dataTable = $dataTable
            ->editColumn('updated_at', function ($payment) {
                return getDateColumn($payment, 'updated_at');
            })->editColumn('amount', function ($payment) {
                return getPriceColumn($payment, 'amount');
            })
            ->editColumn('payment_method.name', function ($payment) {
                return $payment->paymentMethod->name;
            })
            ->editColumn('payment_status.status', function ($payment) {
                return $payment->paymentStatus->status;
            })
            ->editColumn('user.name', function ($payment) {
                return getLinksColumnByRouteName([$payment->user], 'users.edit', 'id', 'name');
            })
            ->addColumn('action', 'payments.datatables_actions')
            ->rawColumns($columns);

        return $dataTable;
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        $columns = [
            [
                'data' => 'amount',
                'title' => trans('lang.payment_amount'),

            ],
            [
                'data' => 'description',
                'title' => trans('lang.payment_description'),

            ],
            (auth()->check() && auth()->user()->hasAnyRole(['admin', 'provider'])) ? [
                'data' => 'user.name',
                'title' => trans('lang.payment_user_id'),

            ] : null,
            [
                'data' => 'payment_method.name',
                'name' => 'paymentMethod.name',
                'title' => trans('lang.payment_payment_method_id'),

            ],
            [
                'data' => 'payment_status.status',
                'name' => 'paymentStatus.status',
                'title' => trans('lang.payment_payment_status_id'),

            ],
            [
                'data' => 'updated_at',
                'title' => trans('lang.payment_updated_at'),
                'searchable' => false,
            ]
        ];
        $columns = array_filter($columns);
        $hasCustomField = in_array(Payment::class, setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFieldsCollection = CustomField::where('custom_field_model', Payment::class)->where('in_table', '=', true)->get();
            foreach ($customFieldsCollection as $key => $field) {
                array_splice($columns, $field->order - 1, 0, [[
                    'data' => 'custom_fields.' . $field->name . '.view',
                    'title' => trans('lang.payment_' . $field->name),
                    'orderable' => false,
                    'searchable' => false,
                ]]);
            }
        }
        return $columns;
    }

    /**
     * Get query source of dataTable.
     *
     * @param Payment $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Payment $model)
    {
        if (auth()->user()->hasRole('admin')) {
            return $model->newQuery()->with("user")
                ->with("paymentMethod")
                ->with("paymentStatus")
                ->select('payments.*')
                ->orderBy('id', 'desc');
        } else if (auth()->user()->hasRole('provider')) {
            $eProviderId = DB::raw("json_extract(e_provider, '$.id')");
            return $model->newQuery()->with("user")
                ->with("paymentMethod")
                ->with("paymentStatus")
                ->join("bookings", "payments.id", "=", "bookings.payment_id")
                ->join("e_provider_users", "e_provider_users.e_provider_id", "=", $eProviderId)
                ->where('e_provider_users.user_id', auth()->id())
                ->groupBy('payments.id')
                ->orderBy('payments.id', 'desc')
                ->select('payments.*');
        } else {
            return $model->newQuery()->with("user")
                ->with("paymentMethod")
                ->with("paymentStatus")
                ->where('payments.user_id', auth()->id())
                ->select('payments.*')
                ->orderBy('id', 'desc');
        }
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return Builder
     */
    public function html()
    {
        return $this->builder()
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->parameters(array_merge(
                config('datatables-buttons.parameters'), [
                    'language' => json_decode(
                        file_get_contents(base_path('resources/lang/' . app()->getLocale() . '/datatable.json')
                        ), true)
                ]
            ));
    }

    /**
     * Export PDF using DOMPDF
     * @return mixed
     */
    public function pdf()
    {
        $data = $this->getDataForPrint();
        $pdf = PDF::loadView($this->printPreview, compact('data'));
        return $pdf->download($this->filename() . '.pdf');
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'paymentsdatatable_' . time();
    }
}
