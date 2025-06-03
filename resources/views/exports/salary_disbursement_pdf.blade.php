<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Monthly Salary Disbursement Report</title>
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
            border-bottom: 3px solid #dc3545;
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
            color: #dc3545;
            margin-bottom: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .report-period {
            font-size: 16px;
            color: #dc3545;
            font-weight: bold;
            margin-bottom: 5px;
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
        .summary-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 12px;
            margin-bottom: 15px;
        }
        .summary-title {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 8px;
            color: #333;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }
        .summary-item {
            text-align: center;
            padding: 8px;
            background-color: white;
            border-radius: 3px;
        }
        .summary-amount {
            font-size: 14px;
            font-weight: bold;
            color: #007bff;
        }
        .summary-label {
            font-size: 9px;
            color: #666;
            margin-top: 2px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 9px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
            font-size: 9px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .amount {
            text-align: right;
            font-family: monospace;
        }
        .status-pending {
            color: #ffc107;
            font-weight: bold;
        }
        .status-reviewed {
            color: #17a2b8;
            font-weight: bold;
        }
        .status-approved {
            color: #28a745;
            font-weight: bold;
        }
        .status-paid {
            color: #007bff;
            font-weight: bold;
        }
        .totals-row {
            background-color: #e9ecef !important;
            font-weight: bold;
            border-top: 2px solid #333;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #888;
            border-top: 1px solid #ddd;
            padding-top: 8px;
        }
        .signature-section {
            margin-top: 30px;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
        }
        .signature-box {
            text-align: center;
            border-top: 1px solid #333;
            padding-top: 5px;
            margin-top: 40px;
        }
        .signature-label {
            font-size: 10px;
            font-weight: bold;
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
                    <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #dc3545, #c82333); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 20px;">
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
            <div class="report-title">Monthly Salary Disbursement Report</div>
            <div class="report-period">{{ $month_name }}</div>
            <div class="report-subtitle">Confidential Payroll Information</div>
            <div class="report-info">
                <strong>Generated on:</strong> {{ $generated_at }} | <strong>Generated by:</strong> {{ $generated_by }} | <strong>Document ID:</strong> SAL-{{ $month }}-{{ date('His') }}
            </div>
        </div>
    </div>

    <div class="summary-box">
        <div class="summary-title">Financial Summary</div>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-amount">{{ number_format($totals['basic_salary'], 2) }}</div>
                <div class="summary-label">Total Basic Salary</div>
            </div>
            <div class="summary-item">
                <div class="summary-amount">{{ number_format($totals['adjustments'], 2) }}</div>
                <div class="summary-label">Total Adjustments</div>
            </div>
            <div class="summary-item">
                <div class="summary-amount">{{ number_format($totals['leave_deductions'], 2) }}</div>
                <div class="summary-label">Total Deductions</div>
            </div>
            <div class="summary-item">
                <div class="summary-amount">{{ number_format($totals['bonus_allowance'], 2) }}</div>
                <div class="summary-label">Total Bonus/Allowance</div>
            </div>
            <div class="summary-item">
                <div class="summary-amount">{{ number_format($totals['gross_salary'], 2) }}</div>
                <div class="summary-label">Total Gross Salary</div>
            </div>
            <div class="summary-item">
                <div class="summary-amount">{{ number_format($totals['net_payable'], 2) }}</div>
                <div class="summary-label">Total Net Payable</div>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Employee Name</th>
                <th>Basic Salary</th>
                <th>Adjustments</th>
                <th>Leave Deductions</th>
                <th>Bonus/Allowance</th>
                <th>Gross Salary</th>
                <th>Net Payable</th>
                <th>Status</th>
                <th>Review Date</th>
                <th>Approval Date</th>
                <th>Payment Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
            <tr>
                <td>{{ $row['employee_name'] }}</td>
                <td class="amount">{{ number_format($row['basic_salary'], 2) }}</td>
                <td class="amount">{{ number_format($row['adjustments'], 2) }}</td>
                <td class="amount">{{ number_format($row['leave_deductions'], 2) }}</td>
                <td class="amount">{{ number_format($row['bonus_allowance'], 2) }}</td>
                <td class="amount">{{ number_format($row['gross_salary'], 2) }}</td>
                <td class="amount">{{ number_format($row['net_payable'], 2) }}</td>
                <td class="status-{{ $row['status'] }}">
                    {{ ucfirst(str_replace('_', ' ', $row['status'])) }}
                </td>
                <td>{{ $row['reviewed_at'] ? date('Y-m-d', strtotime($row['reviewed_at'])) : '-' }}</td>
                <td>{{ $row['approved_at'] ? date('Y-m-d', strtotime($row['approved_at'])) : '-' }}</td>
                <td>{{ $row['paid_at'] ? date('Y-m-d', strtotime($row['paid_at'])) : '-' }}</td>
            </tr>
            @endforeach
            <tr class="totals-row">
                <td>TOTALS ({{ $total_employees }} employees)</td>
                <td class="amount">{{ number_format($totals['basic_salary'], 2) }}</td>
                <td class="amount">{{ number_format($totals['adjustments'], 2) }}</td>
                <td class="amount">{{ number_format($totals['leave_deductions'], 2) }}</td>
                <td class="amount">{{ number_format($totals['bonus_allowance'], 2) }}</td>
                <td class="amount">{{ number_format($totals['gross_salary'], 2) }}</td>
                <td class="amount">{{ number_format($totals['net_payable'], 2) }}</td>
                <td colspan="4"></td>
            </tr>
        </tbody>
    </table>

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-label">Prepared By</div>
        </div>
        <div class="signature-box">
            <div class="signature-label">Reviewed By</div>
        </div>
        <div class="signature-box">
            <div class="signature-label">Approved By</div>
        </div>
    </div>

    <div class="footer">
        <p>This report contains confidential financial information. Please handle with care.</p>
        <p>{{ $company ? $company->name : 'Company Name' }} - Payroll Management System</p>
    </div>
</body>
</html>
