@extends('layouts.master')
@section('main-content')
@section('page-css')
<link rel="stylesheet" href="{{asset('assets/styles/vendor/datatables.min.css')}}">
@endsection

<div class="breadcrumb">
    <h1>Bonus/Allowance List</h1>
    <ul>
        <li>Bonus/Allowance</li>
    </ul>
</div>
<div class="separator-breadcrumb border-top"></div>
<div class="row" id="section_BonusAllowance_list">
    <div class="col-md-12">
        <div class="card text-left">
            <div class="card-header text-right bg-transparent">
                <a class="btn btn-primary btn-md m-1" @click="New_BonusAllowance"><i class="i-Add text-white mr-2"></i> Add Bonus/Allowance</a>
                <a class="btn btn-success btn-md m-1" @click="New_BulkBonusAllowance"><i class="i-Add text-white mr-2"></i> Add Bonus/Allowance for All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="bonus_allowance_list_table" class="display table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Employee</th>
                                <th>Amount</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bonuses as $bonus)
                            <tr>
                                <td></td>
                                <td>{{ $bonus->employee->firstname ?? '' }} {{ $bonus->employee->lastname ?? '' }}</td>
                                <td>{{ $bonus->amount }}</td>
                                <td>{{ ucfirst($bonus->type) }}</td>
                                <td>{{ $bonus->description }}</td>
                                <td>{{ $bonus->created_at->format('Y-m-d') }}</td>
                                <td>
                                    <a @click="Edit_BonusAllowance({{ $bonus }})" class="ul-link-action text-success" data-toggle="tooltip" data-placement="top" title="Edit">
                                        <i class="i-Edit"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Add & Edit Bonus/Allowance -->
        <div class="modal fade" id="BonusAllowance_Modal" tabindex="-1" role="dialog" aria-labelledby="BonusAllowance_Modal" aria-hidden="true">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 v-if="editmode" class="modal-title">Edit Bonus/Allowance</h5>
                        <h5 v-else class="modal-title">Add Bonus/Allowance</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="editmode ? Update_BonusAllowance() : Create_BonusAllowance()">
                            <div class="row">
                                <div class="col-md-12">
                                    <label class="ul-form__label">Employee <span class="field_required">*</span></label>
                                    <v-select placeholder="Select Employee" v-model="bonus.employee_id" :reduce="label => label.value" :options="employees.map(emp => ({label: emp.firstname + ' ' + emp.lastname, value: emp.id}))"></v-select>
                                    <span class="error" v-if="errors && errors.employee_id">@{{ errors.employee_id[0] }}</span>
                                </div>
                                <div class="col-md-12">
                                    <label class="ul-form__label">Amount <span class="field_required">*</span></label>
                                    <input type="number" step="0.01" v-model="bonus.amount" class="form-control" name="amount" required>
                                    <span class="error" v-if="errors && errors.amount">@{{ errors.amount[0] }}</span>
                                </div>
                                <div class="col-md-12">
                                    <label class="ul-form__label">Type <span class="field_required">*</span></label>
                                    <select v-model="bonus.type" class="form-control" name="type" required>
                                        <option value="fixed">Fixed</option>
                                        <option value="percentage">Percentage</option>
                                    </select>
                                    <span class="error" v-if="errors && errors.type">@{{ errors.type[0] }}</span>
                                </div>
                                <div class="col-md-12">
                                    <label class="ul-form__label">Description</label>
                                    <textarea v-model="bonus.description" class="form-control" name="description"></textarea>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-primary" :disabled="SubmitProcessing">
                                        Submit
                                    </button>
                                    <div v-once class="typo__p" v-if="SubmitProcessing">
                                        <div class="spinner spinner-primary mt-3"></div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Bulk Add Bonus/Allowance for All -->
        <div class="modal fade" id="BulkBonusAllowance_Modal" tabindex="-1" role="dialog" aria-labelledby="BulkBonusAllowance_Modal" aria-hidden="true">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Bonus/Allowance for All Employees</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="Create_BulkBonusAllowance()">
                            <div class="row">
                                <div class="col-md-12">
                                    <label class="ul-form__label">Amount <span class="field_required">*</span></label>
                                    <input type="number" step="0.01" v-model="bulkBonus.amount" class="form-control" name="amount" required>
                                    <span class="error" v-if="bulkErrors && bulkErrors.amount">@{{ bulkErrors.amount[0] }}</span>
                                </div>
                                <div class="col-md-12">
                                    <label class="ul-form__label">Type <span class="field_required">*</span></label>
                                    <select v-model="bulkBonus.type" class="form-control" name="type" required>
                                        <option value="fixed">Fixed</option>
                                        <option value="percentage">Percentage</option>
                                    </select>
                                    <span class="error" v-if="bulkErrors && bulkErrors.type">@{{ bulkErrors.type[0] }}</span>
                                </div>
                                <div class="col-md-12">
                                    <label class="ul-form__label">Description</label>
                                    <textarea v-model="bulkBonus.description" class="form-control" name="description"></textarea>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-success" :disabled="BulkSubmitProcessing">
                                        Submit
                                    </button>
                                    <div v-once class="typo__p" v-if="BulkSubmitProcessing">
                                        <div class="spinner spinner-primary mt-3"></div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('page-js')
<script src="{{asset('assets/js/vendor/datatables.min.js')}}"></script>
<script src="{{asset('assets/js/datatables.script.js')}}"></script>
<script>
Vue.component('v-select', VueSelect.VueSelect)
var app = new Vue({
    el: '#section_BonusAllowance_list',
    data: {
        employees: @json($employees ?? []),
        bonuses: @json($bonuses ?? []),
        bonus: {
            employee_id: '',
            amount: '',
            type: 'fixed',
            description: ''
        },
        editmode: false,
        errors: {},
        SubmitProcessing: false,
        // Bulk add state
        bulkBonus: {
            amount: '',
            type: 'fixed',
            description: ''
        },
        bulkErrors: {},
        BulkSubmitProcessing: false
    },
    methods: {
        New_BonusAllowance() {
            this.editmode = false;
            this.bonus = { employee_id: '', amount: '', type: 'fixed', description: '' };
            this.errors = {};
            $('#BonusAllowance_Modal').modal('show');
        },
        Edit_BonusAllowance(bonus) {
            this.editmode = true;
            this.bonus = Object.assign({}, bonus);
            this.errors = {};
            $('#BonusAllowance_Modal').modal('show');
        },
        Create_BonusAllowance() {
            this.SubmitProcessing = true;
            axios.post("{{ route('hrm.bonus_allowance.store') }}", this.bonus)
                .then(response => { window.location.reload(); })
                .catch(error => { this.errors = error.response.data.errors || {}; })
                .finally(() => { this.SubmitProcessing = false; });
        },
        Update_BonusAllowance() {
            this.SubmitProcessing = true;
            axios.put(`/hrm/bonus-allowance/${this.bonus.id}`, this.bonus)
                .then(response => { window.location.reload(); })
                .catch(error => { this.errors = error.response.data.errors || {}; })
                .finally(() => { this.SubmitProcessing = false; });
        },
        // Bulk add methods
        New_BulkBonusAllowance() {
            this.bulkBonus = { amount: '', type: 'fixed', description: '' };
            this.bulkErrors = {};
            $('#BulkBonusAllowance_Modal').modal('show');
        },
        Create_BulkBonusAllowance() {
            this.BulkSubmitProcessing = true;
            axios.post("{{ route('hrm.bonus_allowance.bulk_store') }}", {
                employee_ids: this.employees.map(e => e.id),
                amount: this.bulkBonus.amount,
                type: this.bulkBonus.type,
                description: this.bulkBonus.description
            })
            .then(response => { window.location.reload(); })
            .catch(error => { this.bulkErrors = error.response.data.errors || {}; })
            .finally(() => { this.BulkSubmitProcessing = false; });
        }
    }
});
</script>
@endsection 