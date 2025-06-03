<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Leave & Absence Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 15px;
            color: #333;
        }
        .header {
            margin-bottom: 30px;
            border-bottom: 3px solid #17a2b8;
            padding-bottom: 20px;
        }
        .company-header {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .company-logo {
            display: table-cell;
            width: 80px;
            vertical-align: middle;
        }
        .company-info {
            display: table-cell;
            vertical-align: middle;
            padding-left: 20px;
        }
        .company-name {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 3px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .company-tagline {
            font-size: 12px;
            color: #666;
            font-style: italic;
            margin-bottom: 5px;
        }
        .company-details {
            font-size: 10px;
            color: #888;
            line-height: 1.4;
        }
        .report-section {
            text-align: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
        }
        .report-title {
            font-size: 20px;
            color: #17a2b8;
            margin-bottom: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .report-subtitle {
            font-size: 14px;
            color: #666;
            margin-bottom: 8px;
        }
        .report-info {
            font-size: 10px;
            color: #888;
        }
        .summary-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .summary-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 12px;
            color: #333;
            text-align: center;
        }
        .summary-grid {
            display: table;
            width: 100%;
        }
        .summary-row {
            display: table-row;
        }
        .summary-item {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 8px;
        }
        .summary-label {
            font-size: 10px;
            color: #666;
            margin-bottom: 3px;
        }
        .summary-value {
            font-size: 16px;
            font-weight: bold;
            color: #17a2b8;
        }
        .leave-types-section {
            background: linear-gradient(135deg, #e8f4f8 0%, #d1ecf1 100%);
            border: 1px solid #bee5eb;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
        }
        .leave-types-title {
            font-size: 12px;
            font-weight: bold;
            color: #0c5460;
            margin-bottom: 8px;
            text-align: center;
        }
        .leave-types-grid {
            display: table;
            width: 100%;
        }
        .leave-type-item {
            display: table-cell;
            text-align: center;
            padding: 5px;
            font-size: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 9px;
        }
        th {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
            padding: 10px 6px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #138496;
            font-size: 9px;
        }
        td {
            padding: 8px 6px;
            text-align: center;
            border: 1px solid #dee2e6;
            vertical-align: middle;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        tr:hover {
            background-color: #e8f4f8;
        }
        .employee-name {
            text-align: left;
            font-weight: bold;
            color: #333;
        }
        .leave-type {
            color: #17a2b8;
            font-weight: 500;
        }
        .status-approved {
            background-color: #d4edda;
            color: #155724;
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: bold;
            font-size: 8px;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: bold;
            font-size: 8px;
        }
        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: bold;
            font-size: 8px;
        }
        .days-count {
            font-weight: bold;
            color: #17a2b8;
        }
        .reason-cell {
            text-align: left;
            max-width: 150px;
            word-wrap: break-word;
            font-size: 8px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #dee2e6;
            padding-top: 15px;
        }
        .footer p {
            margin: 3px 0;
        }
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-header">
            <div class="company-logo">
                @if($company && $company->logo)
                    <img src="{{ public_path('images/company/' . $company->logo) }}" alt="Company Logo" style="max-width: 70px; max-height: 70px;">
                @else
                    <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #17a2b8, #138496); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 20px;">
                        @if($company && $company->name)
                            {{ strtoupper(substr($company->name, 0, 1)) }}
                        @else
                            OS
                        @endif
                    </div>
                @endif
            </div>
            <div class="company-info">
                <div class="company-name">
                    {{ $company ? $company->name : 'Onchain Software & Research Limited' }}
                </div>
                <div class="company-tagline">
                    {{ $company && $company->tagline ? $company->tagline : 'Innovative Technology Solutions & Research Excellence' }}
                </div>
                <div class="company-details">
                    @if($company)
                        @if($company->address)
                            <strong>Address:</strong> {{ $company->address }}<br>
                        @endif
                        @if($company->phone)
                            <strong>Phone:</strong> {{ $company->phone }}
                        @endif
                        @if($company->email)
                            | <strong>Email:</strong> {{ $company->email }}
                        @endif
                        @if($company->website)
                            | <strong>Website:</strong> {{ $company->website }}
                        @endif
                        <br>
                        @if($company->tax_number)
                            <strong>Tax ID:</strong> {{ $company->tax_number }}
                        @endif
                        @if($company->registration_number)
                            | <strong>Reg. No:</strong> {{ $company->registration_number }}
                        @endif
                    @else
                        <strong>Address:</strong> 123 Innovation Drive, Tech Park, Silicon Valley, CA 94025, USA<br>
                        <strong>Phone:</strong> +1 (650) 555-0123 | <strong>Email:</strong> info@onchain-research.com | <strong>Website:</strong> www.onchain-research.com<br>
                        <strong>Tax ID:</strong> 12-3456789 | <strong>Reg. No:</strong> C4567890 | <strong>DUNS:</strong> 123456789
                    @endif
                </div>
            </div>
        </div>

        <div class="report-section">
            <div class="report-title">Leave & Absence Report</div>
            @if($search)
            <div style="font-size: 12px; color: #17a2b8; margin-bottom: 5px; font-weight: bold;">
                🔍 Search Filter: "{{ $search }}"
            </div>
            @endif
            <div class="report-subtitle">Employee Leave Management & Analytics</div>
            <div class="report-info">
                <strong>Generated on:</strong> {{ $generated_at }} | <strong>Generated by:</strong> {{ $generated_by }} | <strong>Document ID:</strong> LEV-{{ date('Ymd-His') }}
            </div>
        </div>
    </div>

    <div class="summary-section">
        <div class="summary-title">Leave Summary</div>
        <div class="summary-grid">
            <div class="summary-row">
                <div class="summary-item">
                    <div class="summary-label">Total Records</div>
                    <div class="summary-value">{{ $total_records }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Total Days Requested</div>
                    <div class="summary-value">{{ $summary['total_days_requested'] }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Approved Leaves</div>
                    <div class="summary-value">{{ $summary['approved_count'] }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Approval Rate</div>
                    <div class="summary-value">{{ $summary['approval_rate'] }}%</div>
                </div>
            </div>
            <div class="summary-row">
                <div class="summary-item">
                    <div class="summary-label">Pending Leaves</div>
                    <div class="summary-value">{{ $summary['pending_count'] }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Rejected Leaves</div>
                    <div class="summary-value">{{ $summary['rejected_count'] }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Leave Types</div>
                    <div class="summary-value">{{ $summary['leave_types']->count() }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Avg Days/Request</div>
                    <div class="summary-value">{{ $total_records > 0 ? round($summary['total_days_requested'] / $total_records, 1) : 0 }}</div>
                </div>
            </div>
        </div>
    </div>

    @if($summary['leave_types']->count() > 0)
    <div class="leave-types-section">
        <div class="leave-types-title">Leave Types Breakdown</div>
        <div class="leave-types-grid">
            @foreach($summary['leave_types'] as $type => $count)
            <div class="leave-type-item">
                <strong>{{ $type }}</strong><br>
                {{ $count }} requests
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($data->count() > 0)
    <table>
        <thead>
            <tr>
                <th style="width: 18%;">Employee Name</th>
                <th style="width: 12%;">Leave Type</th>
                <th style="width: 10%;">From Date</th>
                <th style="width: 10%;">To Date</th>
                <th style="width: 8%;">Days</th>
                <th style="width: 10%;">Status</th>
                <th style="width: 8%;">Balance</th>
                <th style="width: 14%;">Reason</th>
                <th style="width: 10%;">Applied Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
            <tr>
                <td class="employee-name">{{ $row['employee_name'] }}</td>
                <td class="leave-type">{{ $row['leave_type'] }}</td>
                <td>{{ \Carbon\Carbon::parse($row['from_date'])->format('M d, Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($row['to_date'])->format('M d, Y') }}</td>
                <td class="days-count">{{ $row['total_days'] }}</td>
                <td>
                    @php
                        $status = strtolower($row['status']);
                        $statusClass = 'status-pending';
                        if ($status === 'approved') $statusClass = 'status-approved';
                        elseif ($status === 'rejected') $statusClass = 'status-rejected';
                    @endphp
                    <span class="{{ $statusClass }}">{{ ucfirst($row['status']) }}</span>
                </td>
                <td>{{ $row['balance_days_left'] ?? '---' }}</td>
                <td class="reason-cell">{{ $row['reason'] ?: '---' }}</td>
                <td>{{ $row['applied_date'] ? \Carbon\Carbon::parse($row['applied_date'])->format('M d, Y') : '---' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="no-data">
        <p>No leave records found{{ $search ? ' for the search criteria' : '' }}.</p>
    </div>
    @endif

    <div class="footer">
        <p>This report contains confidential employee leave information.</p>
        <p>{{ $company ? $company->name : 'Onchain Software & Research Limited' }} - Human Resource Management System</p>
        <p>Report generated automatically on {{ $generated_at }}</p>
    </div>
</body>
</html>
