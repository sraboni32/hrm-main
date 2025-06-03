<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Employee Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 15px;
            color: #333;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            position: relative;
        }
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="%23f0f0f0" opacity="0.3"/><circle cx="75" cy="75" r="1" fill="%23f0f0f0" opacity="0.3"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.1;
            z-index: -1;
        }
        .header {
            margin-bottom: 30px;
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 8px 8px 0 0;
            box-shadow: 0 2px 10px rgba(0,123,255,0.1);
            position: relative;
        }
        .header::after {
            content: '';
            position: absolute;
            bottom: -3px;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, #007bff 0%, #0056b3 50%, #007bff 100%);
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
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
        .company-tagline {
            font-size: 12px;
            color: #007bff;
            font-style: italic;
            margin-bottom: 5px;
            font-weight: 500;
        }
        .company-details {
            font-size: 10px;
            color: #666;
            line-height: 1.5;
            background: rgba(0,123,255,0.05);
            padding: 8px;
            border-radius: 4px;
            border-left: 3px solid #007bff;
        }
        .report-section {
            text-align: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
        }
        .report-title {
            font-size: 22px;
            color: #007bff;
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
        .summary-box {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 18px;
            margin-bottom: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .summary-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
            text-align: center;
        }
        .summary-stats {
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
            padding: 10px;
        }
        .summary-label {
            font-size: 10px;
            color: #666;
            margin-bottom: 5px;
        }
        .summary-value {
            font-size: 18px;
            font-weight: bold;
            color: #007bff;
        }
        .stat-item {
            text-align: center;
        }
        .stat-number {
            font-size: 18px;
            font-weight: bold;
            color: #007bff;
        }
        .stat-label {
            font-size: 10px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            font-size: 10px;
        }
        th {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 12px 8px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #0056b3;
            font-size: 10px;
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
            background-color: #e3f2fd;
        }
        .status-active {
            background-color: #d4edda;
            color: #155724;
            padding: 4px 8px;
            border-radius: 12px;
            font-weight: bold;
            font-size: 9px;
        }
        .status-inactive {
            background-color: #f8d7da;
            color: #721c24;
            padding: 4px 8px;
            border-radius: 12px;
            font-weight: bold;
            font-size: 9px;
        }
        .employee-name {
            text-align: left;
            font-weight: bold;
            color: #333;
        }
        .employee-email {
            text-align: left;
            color: #666;
        }
        .department-info {
            color: #007bff;
            font-weight: 500;
        }
        .footer {
            margin-top: 40px;
            border-top: 2px solid #007bff;
            padding-top: 20px;
            font-size: 9px;
            color: #666;
        }
        .footer-content {
            display: table;
            width: 100%;
        }
        .footer-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .footer-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            text-align: right;
        }
        .footer-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .confidential-notice {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 5px;
            padding: 10px;
            margin-top: 15px;
            text-align: center;
            font-size: 10px;
            color: #856404;
        }
        .page-break {
            page-break-after: always;
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
                    <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #007bff, #0056b3); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 20px;">
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
            <div class="report-title">Employee Report</div>
            <div class="report-subtitle">Comprehensive Employee Information & Analytics</div>
            <div class="report-info">
                <strong>Generated on:</strong> {{ $generated_at }} | <strong>Generated by:</strong> {{ $generated_by }} | <strong>Document ID:</strong> EMP-{{ date('Ymd-His') }}
            </div>
        </div>
    </div>

    <div class="summary-box">
        <div class="summary-title">Report Summary</div>
        <div class="summary-stats">
            <div class="stat-item">
                <div class="stat-number">{{ $total_employees }}</div>
                <div class="stat-label">Total Employees</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">{{ $employees->where('is_active', 1)->count() }}</div>
                <div class="stat-label">Active Employees</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">{{ $employees->where('is_active', 0)->count() }}</div>
                <div class="stat-label">Inactive Employees</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">{{ $employees->groupBy('company.name')->count() }}</div>
                <div class="stat-label">Companies</div>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Employment Type</th>
                <th>Company</th>
                <th>Department</th>
                <th>Designation</th>
                <th>Office Shift</th>
                <th>Joining Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($employees as $employee)
            <tr>
                <td>{{ $employee->id }}</td>
                <td class="employee-name">{{ $employee->firstname }} {{ $employee->lastname }}</td>
                <td class="employee-email">{{ $employee->email ?? '---' }}</td>
                <td>{{ $employee->phone ?? '---' }}</td>
                <td>{{ $employee->employment_type ?? '---' }}</td>
                <td>{{ $employee->company ? $employee->company->name : '---' }}</td>
                <td class="department-info">{{ $employee->department ? $employee->department->department : '---' }}</td>
                <td class="department-info">{{ $employee->designation ? $employee->designation->designation : '---' }}</td>
                <td>{{ $employee->office_shift ? $employee->office_shift->name : '---' }}</td>
                <td>{{ $employee->joining_date ? \Carbon\Carbon::parse($employee->joining_date)->format('M d, Y') : '---' }}</td>
                <td>
                    <span class="{{ $employee->is_active ? 'status-active' : 'status-inactive' }}">
                        {{ $employee->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div class="footer-content">
            <div class="footer-left">
                <div class="footer-title">{{ $company ? $company->name : 'Onchain Software & Research Limited' }}</div>
                <div>Human Resource Management System</div>
                @if($company)
                    @if($company->address)
                        <div>{{ $company->address }}</div>
                    @endif
                    @if($company->phone || $company->email)
                        <div>
                            @if($company->phone)Phone: {{ $company->phone }}@endif
                            @if($company->phone && $company->email) | @endif
                            @if($company->email)Email: {{ $company->email }}@endif
                        </div>
                    @endif
                @endif
            </div>
            <div class="footer-right">
                <div class="footer-title">Document Information</div>
                <div><strong>Report Type:</strong> Employee Report</div>
                <div><strong>Generated:</strong> {{ $generated_at }}</div>
                <div><strong>Generated by:</strong> {{ $generated_by }}</div>
                <div><strong>Document ID:</strong> EMP-{{ date('Ymd-His') }}</div>
                <div><strong>Total Records:</strong> {{ $total_employees }}</div>
            </div>
        </div>

        <div class="confidential-notice">
            <strong>⚠️ CONFIDENTIAL DOCUMENT</strong><br>
            This report contains confidential employee information and is intended solely for authorized personnel.
            Unauthorized distribution, copying, or disclosure is strictly prohibited and may be subject to legal action.
            Please handle this document in accordance with your organization's data protection policies.
        </div>
    </div>
</body>
</html>
