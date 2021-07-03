<?php
/*
 * File name: CouponDataTable.php
 * Last modified: 2021.04.12 at 10:04:50
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */

namespace App\DataTables;

use App\Models\Coupon;
use App\Models\CustomField;
use App\Models\Post;
use Barryvdh\DomPDF\Facade as PDF;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Services\DataTable;

class CouponDataTable extends DataTable
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
            ->editColumn('updated_at', function ($coupon) {
                return getDateColumn($coupon, 'updated_at');
            })
            ->editColumn('description', function ($coupon) {
                return getStripedHtmlColumn($coupon, 'description');
            })
            ->editColumn('expires_at', function ($coupon) {
                return getDateColumn($coupon, 'expires_at');
            })
            ->editColumn('enabled', function ($coupon) {
                return getBooleanColumn($coupon, 'enabled');
            })
            ->editColumn('discount', function ($coupon) {
                if ($coupon['discount_type'] == 'percent') {
                    return $coupon['discount'] . "%";
                }
                return getPriceColumn($coupon, 'discount');
            })
            ->addColumn('action', 'coupons.datatables_actions')
            ->rawColumns(array_merge($columns, ['action']));

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
                'data' => 'code',
                'title' => trans('lang.coupon_code'),

            ],
            [
                'data' => 'discount',
                'title' => trans('lang.coupon_discount'),

            ],
            [
                'data' => 'description',
                'title' => trans('lang.coupon_description'),

            ],
            [
                'data' => 'expires_at',
                'title' => trans('lang.coupon_expires_at'),

            ],
            [
                'data' => 'enabled',
                'title' => trans('lang.coupon_enabled'),

            ],
            [
                'data' => 'updated_at',
                'title' => trans('lang.coupon_updated_at'),
                'searchable' => false,
            ]
        ];

        $hasCustomField = in_array(Coupon::class, setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFieldsCollection = CustomField::where('custom_field_model', Coupon::class)->where('in_table', '=', true)->get();
            foreach ($customFieldsCollection as $key => $field) {
                array_splice($columns, $field->order - 1, 0, [[
                    'data' => 'custom_fields.' . $field->name . '.view',
                    'title' => trans('lang.coupon_' . $field->name),
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
     * @param Coupon $model
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function query(Coupon $model)
    {
        if (auth()->user()->hasRole('admin')) {
            return $model->newQuery();
        } elseif (auth()->user()->hasRole('provider')) {
            $eProviders = $model->join("discountables", "discountables.coupon_id", "=", "coupons.id")
                ->join("e_provider_users", "e_provider_users.e_provider_id", "=", "discountables.discountable_id")
                ->where('discountable_type', 'App\\Models\\EProvider')
                ->where("e_provider_users.user_id", auth()->id())->select("coupons.*");

            return $model->join("discountables", "discountables.coupon_id", "=", "coupons.id")
                ->join("e_services", "e_services.id", "=", "discountables.discountable_id")
                ->where('discountable_type', 'App\\Models\\EService')
                ->join("e_provider_users", "e_provider_users.e_provider_id", "=", "e_services.e_provider_id")
                ->where("e_provider_users.user_id", auth()->id())
                ->select("coupons.*")
                ->union($eProviders);
        } else {
            $model->newQuery();
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
            ->addAction(['title' => trans('lang.actions'), 'width' => '80px', 'printable' => false, 'responsivePriority' => '100'])
            ->parameters(array_merge(
                config('datatables-buttons.parameters'), [
                    'language' => json_decode(
                        file_get_contents(base_path('resources/lang/' . app()->getLocale() . '/datatable.json')
                        ), true),
                    'order' => [[5, 'desc']],
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
        return 'couponsdatatable_' . time();
    }
}
