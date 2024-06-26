<?php

namespace App\Livewire;

use App\Models\Accountant;
use App\Models\CaseHandler;
use App\Models\Doctor;
use App\Models\EmployeePayRoll;
use App\Models\LabTechnician;
use App\Models\Nurse;
use App\Models\Pharmacist;
use App\Models\Receptionist;
use Illuminate\Database\Eloquent\Builder;
use Livewire\WithPagination;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\User;
use Livewire\Attributes\Lazy;

#[Lazy]
class EmployeePayrollTable extends LivewireTableComponent
{
    use WithPagination;

    protected $model = EmployeePayroll::class;

    public $showButtonOnHeader = true;

    public $showFilterOnHeader = true;

    public $buttonComponent = 'employee_payrolls.add-button';

    public $FilterComponent = ['employee_payrolls.filter-button', EmployeePayroll::FILTER_STATUS_ARR];

    public $statusFilter;

    protected $listeners = ['refresh' => '$refresh', 'changeFilter', 'resetPage'];

    // public function resetPage($pageName = 'page')
    // {
    //     $rowsPropertyData = $this->getRows()->toArray();
    //     $prevPageNum = $rowsPropertyData['current_page'] - 1;
    //     $prevPageNum = $prevPageNum > 0 ? $prevPageNum : 1;
    //     $pageNum = count($rowsPropertyData['data']) > 0 ? $rowsPropertyData['current_page'] : $prevPageNum;

    //     $this->setPage($pageNum, $pageName);
    // }

    public function changeFilter($statusFilter)
    {
        $this->resetPage($this->getComputedPageName());
        $this->statusFilter = $statusFilter;
        $this->setBuilder($this->builder());
    }

    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setDefaultSort('employee_payrolls.created_at', 'desc')
            ->setQueryStringStatus(false);
        $this->setTdAttributes(function (Column $column, $row, $columnIndex, $rowIndex) {
            if ($column->isField('sr_no') || $column->isField('payroll_id') || $column->isField('month') || $column->isField('year')) {
                return [
                    'class' => 'p-5',
                ];
            }

            return [];
        });

        $this->setThAttributes(function (Column $column) {
            if ($column->isField('net_salary')) {
                return [
                    'class' => 'd-flex justify-content-center text-end',
                ];
            }

            return [];
        });
    }

    public function placeholder()
    {
        return view('livewire.skeleton_files.common_skeleton_af');
    }

    public function columns(): array
    {
        return [
            Column::make(__('messages.employee_payroll.sr_no'), 'sr_no')
                ->sortable()
                ->searchable(),
            Column::make(__('messages.employee_payroll.payroll_id'), 'payroll_id')
                ->view('employee_payrolls.columns.payroll_id')
                ->searchable()
                ->sortable(),
            Column::make(__('messages.employee_payroll.employee'), 'type')
               ->sortable()
                ->searchable(function (Builder $query, $searchTerm) {
                    $query->orWhereHasMorph('owner', [
                        Nurse::class,
                        Doctor::class,
                        LabTechnician::class,
                        Receptionist::class,
                        Pharmacist::class,
                        Accountant::class,
                        CaseHandler::class
                    ], function (Builder $subQuery) use ($searchTerm) {
                        $subQuery->where(function (Builder $q) use ($searchTerm) {
                            $q->whereHas('User', function (Builder $q) use ($searchTerm) {
                                $q->where('first_name', 'like', '%' . $searchTerm . '%')
                                    ->orWhere('last_name', 'like', '%' . $searchTerm . '%');
                            });
                        });
                    });
                })
                ->view('employee_payrolls.columns.employee'),
            Column::make(__('messages.employee_payroll.month'), 'month')
                ->sortable()
                ->searchable(),
            Column::make(__('messages.employee_payroll.year'), 'year')
                ->sortable()
                ->searchable(),
            Column::make(__('messages.employee_payroll.net_salary'), 'net_salary')
                ->view('employee_payrolls.columns.net_salary')
                ->sortable()
                ->searchable(),
            Column::make(__('messages.common.status'), 'status')
                ->searchable()
                ->view('employee_payrolls.columns.status'),
            Column::make(__('messages.common.action'), 'id')
                ->view('employee_payrolls.action'),
        ];
    }

    public function builder(): Builder
    {
        $query = EmployeePayroll::whereHasMorph(
            'owner', [
                Nurse::class,
                Doctor::class,
                LabTechnician::class,
                Receptionist::class,
                Pharmacist::class,
                Accountant::class,
                CaseHandler::class,
            ], function ($q, $type) {
                if (in_array($type, EmployeePayroll::PYAYROLLUSERS)) {
                    if ($type == \App\Models\Doctor::class) {
                        $q->whereHas('doctorUser', function (Builder $qr) {
                            return $qr;
                        });
                    } else {
                        $q->whereHas('user', function (Builder $qr) {
                            return $qr;
                        });
                    }
                }
            })->with('owner')->select('employee_payrolls.*');

        $query->when(isset($this->statusFilter), function (Builder $q) {
            if ($this->statusFilter == 1) {
                $q->where('status', $this->statusFilter);
            }
            if ($this->statusFilter == 2) {
                $q->where('status', EmployeePayroll::NOT_PAID);
            }
        });

        return $query;
    }
}
