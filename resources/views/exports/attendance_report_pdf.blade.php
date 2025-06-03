<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 15px;
            color: #333;
        }
        .header {
            margin-bottom: 30px;
            border-bottom: 3px solid #28a745;
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
            color: #28a745;
            margin-bottom: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .report-period {
            font-size: 16px;
            color: #28a745;
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
            width: 33.33%;
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
            color: #007bff;
        }
        .summary-percentage {
            color: #28a745;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }
        th {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 10px 6px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #0056b3;
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
        .status-present {
            background-color: #d4edda;
            color: #155724;
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: bold;
            font-size: 9px;
        }
        .status-absent {
            background-color: #f8d7da;
            color: #721c24;
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: bold;
            font-size: 9px;
        }
        .status-late {
            background-color: #fff3cd;
            color: #856404;
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: bold;
            font-size: 9px;
        }
        .time-cell {
            font-family: 'Courier New', monospace;
            font-weight: bold;
        }
        .employee-name {
            text-align: left;
            font-weight: bold;
            color: #333;
        }
        .footer {
            margin-top: 40px;
            border-top: 2px solid #28a745;
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
            background-color: #d4edda;
            border: 1px solid #28a745;
            border-radius: 5px;
            padding: 10px;
            margin-top: 15px;
            text-align: center;
            font-size: 10px;
            color: #155724;
        }
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
        .page-break {
            page-break-before: always;
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
                    <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #28a745, #20c997); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 20px;">
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
            <div class="report-title">Attendance Report</div>
            <div class="report-period">{{ $period }}</div>
            <div class="report-subtitle">Employee Attendance & Time Tracking</div>
            <div class="report-info">
                <strong>Generated on:</strong> {{ $generated_at }} | <strong>Generated by:</strong> {{ $generated_by }} | <strong>Document ID:</strong> ATT-{{ date('Ymd-His') }}
            </div>
        </div>
    </div>

    <div class="summary-section">
        <div class="summary-title">Attendance Summary</div>
        <div class="summary-grid">
            <div class="summary-row">
                <div class="summary-item">
                    <div class="summary-label">Total Days</div>
                    <div class="summary-value">{{ $summary['total_days'] }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Present Days</div>
                    <div class="summary-value">{{ $summary['present_days'] }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Absent Days</div>
                    <div class="summary-value">{{ $summary['absent_days'] }}</div>
                </div>
            </div>
            <div class="summary-row">
                <div class="summary-item">
                    <div class="summary-label">Late Days</div>
                    <div class="summary-value">{{ $summary['late_days'] }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Total Work Hours</div>
                    <div class="summary-value">{{ $summary['total_work_hours'] }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Attendance Rate</div>
                    <div class="summary-value summary-percentage">{{ $summary['attendance_rate'] }}%</div>
                </div>
            </div>
        </div>
    </div>

    @if($attendances->count() > 0)
    <table>
        <thead>
            <tr>
                <th style="width: 20%;">Employee Name</th>
                <th style="width: 12%;">Date</th>
                <th style="width: 12%;">Clock In</th>
                <th style="width: 12%;">Clock Out</th>
                <th style="width: 12%;">Break Time</th>
                <th style="width: 12%;">Total Work</th>
                <th style="width: 12%;">Total Rest</th>
                <th style="width: 8%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendances as $attendance)
            <tr>
                <td class="employee-name">
                    {{ $attendance->employee ? $attendance->employee->firstname . ' ' . $attendance->employee->lastname : 'Unknown' }}
                </td>
                <td>{{ \Carbon\Carbon::parse($attendance->date)->format('M d, Y') }}</td>
                <td class="time-cell">{{ $attendance->clock_in ?? '---' }}</td>
                <td class="time-cell">{{ $attendance->clock_out ?? '---' }}</td>
                <td class="time-cell">{{ $attendance->break_time ?? '00:00' }}</td>
                <td class="time-cell">{{ $attendance->total_work ?? '00:00' }}</td>
                <td class="time-cell">{{ $attendance->total_rest ?? '00:00' }}</td>
                <td>
                    @php
                        $status = $attendance->status ?? 'Present';
                        $statusClass = 'status-present';
                        if ($status === 'Absent') $statusClass = 'status-absent';
                        elseif ($status === 'Late') $statusClass = 'status-late';
                    @endphp
                    <span class="{{ $statusClass }}">{{ $status }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="no-data">
        <p>No attendance records found for the selected period.</p>
    </div>
    @endif

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
                <div><strong>Report Type:</strong> Attendance Report</div>
                <div><strong>Generated:</strong> {{ $generated_at }}</div>
                <div><strong>Generated by:</strong> {{ $generated_by }}</div>
                <div><strong>Document ID:</strong> ATT-{{ date('Ymd-His') }}</div>
                <div><strong>Total Records:</strong> {{ $summary['total_days'] }}</div>
            </div>
        </div>

        <div class="confidential-notice">
            <strong>🔒 CONFIDENTIAL DOCUMENT</strong><br>
            This report contains confidential employee attendance information and is intended solely for authorized personnel.
            Unauthorized distribution, copying, or disclosure is strictly prohibited and may be subject to legal action.
            Please handle this document in accordance with your organization's data protection policies.
        </div>
    </div>
</body>
</html>
