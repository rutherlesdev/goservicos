<?php
/*
 * File name: EServiceReviewDataTable.php
 * Last modified: 2021.03.21 at 21:14:07
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */

namespace App\DataTables;

use App\Models\CustomField;
use App\Models\EServiceReview;
use Barryvdh\DomPDF\Facade as PDF;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Services\DataTable;

class EServiceReviewDataTable extends DataTable
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
            ->editColumn('updated_at', function ($eServiceReview) {
                return getDateColumn($eServiceReview, 'updated_at');
            })
            ->editColumn('user.name', function ($eServiceReview) {
                return getLinksColumnByRouteName([$eServiceReview->user], 'users.edit', 'id', 'name');
            })
            ->editColumn('e_service.name', function ($eServiceReview) {
                return getLinksColumnByRouteName([$eServiceReview->eService], 'eServices.edit', 'id', 'name');
            })
            ->addColumn('action', 'e_service_reviews.datatables_actions')
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
                'data' => 'review',
                'title' => trans('lang.e_service_review_review'),

            ],
            [
                'data' => 'rate',
                'title' => trans('lang.e_service_review_rate'),

            ],
            [
                'data' => 'user.name',
                'title' => trans('lang.e_service_review_user_id'),

            ],
            [
                'name' => 'eService.name',
                'data' => 'e_service.name',
                'title' => trans('lang.e_service_review_e_service_id'),

            ],
            [
                'data' => 'updated_at',
                'title' => trans('lang.e_service_review_updated_at'),
                'searchable' => false,
            ]
        ];

        $hasCustomField = in_array(EServiceReview::class, setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFieldsCollection = CustomField::where('custom_field_model', EServiceReview::class)->where('in_table', '=', true)->get();
            foreach ($customFieldsCollection as $key => $field) {
                array_splice($columns, $field->order - 1, 0, [[
                    'data' => 'custom_fields.' . $field->name . '.view',
                    'title' => trans('lang.e_service_review_' . $field->name),
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
     * @param EServiceReview $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(EServiceReview $model)
    {
        if (auth()->user()->hasRole('admin')) {
            return $model->newQuery()->with("user")->with("eService");
        } else if (auth()->user()->hasRole('provider')) {
            return $model->newQuery()->with("user")->with("eService")->join("e_services", "e_services.id", "=", "e_service_reviews.e_service_id")
                ->join("e_provider_users", "e_provider_users.e_provider_id", "=", "e_services.e_provider_id")
                ->where('e_provider_users.user_id', auth()->id())
                ->groupBy('e_service_reviews.id')
                ->select('e_service_reviews.*');
        } else if (auth()->user()->hasRole('customer')) {
            return $model->newQuery()->where('e_service_reviews.user_id', auth()->id())
                ->select('e_service_reviews.*');
        } else {
            return $model->newQuery()->with("user")->with("eService");
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
            ->addAction(['width' => '80px', 'printable' => false, 'responsivePriority' => '100'])
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
        return 'e_service_reviewsdatatable_' . time();
    }
}
