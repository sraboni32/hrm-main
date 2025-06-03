<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>KPI Summary Report</title>
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
            border-bottom: 3px solid #ffc107;
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
            color: #ffc107;
            margin-bottom: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .report-period {
            font-size: 16px;
            color: #ffc107;
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
            color: #28a745;
        }
        .top-performer {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
            text-align: center;
        }
        .top-performer-title {
            font-size: 12px;
            font-weight: bold;
            color: #856404;
            margin-bottom: 5px;
        }
        .top-performer-name {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            margin-bottom: 3px;
        }
        .top-performer-score {
            font-size: 16px;
            font-weight: bold;
            color: #ffc107;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 9px;
        }
        th {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 10px 6px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #20c997;
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
            background-color: #e8f5e8;
        }
        .employee-name {
            text-align: left;
            font-weight: bold;
            color: #333;
        }
        .performance-score {
            font-weight: bold;
        }
        .score-excellent {
            color: #28a745;
        }
        .score-good {
            color: #ffc107;
        }
        .score-average {
            color: #fd7e14;
        }
        .score-poor {
            color: #dc3545;
        }
        .completion-rate {
            font-weight: bold;
        }
        .rate-high {
            color: #28a745;
        }
        .rate-medium {
            color: #ffc107;
        }
        .rate-low {
            color: #dc3545;
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
                    <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #ffc107, #e0a800); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 20px;">
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
            <div class="report-title">KPI Summary Report</div>
            <div class="report-period">{{ $period }}</div>
            <div class="report-subtitle">Performance Analytics & Key Performance Indicators</div>
            <div class="report-info">
                <strong>Generated on:</strong> {{ $generated_at }} | <strong>Generated by:</strong> {{ $generated_by }} | <strong>Document ID:</strong> KPI-{{ date('Ymd-His') }}
            </div>
        </div>
    </div>

    <div class="summary-section">
        <div class="summary-title">Performance Overview</div>
        <div class="summary-grid">
            <div class="summary-row">
                <div class="summary-item">
                    <div class="summary-label">Total Employees</div>
                    <div class="summary-value">{{ $total_employees }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Avg Performance Score</div>
                    <div class="summary-value">{{ $summary['avg_performance_score'] }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Avg Task Completion</div>
                    <div class="summary-value">{{ $summary['avg_task_completion_rate'] }}%</div>
                </div>
            </div>
            <div class="summary-row">
                <div class="summary-item">
                    <div class="summary-label">Total Tasks Completed</div>
                    <div class="summary-value">{{ $summary['total_tasks_completed'] }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Total Projects</div>
                    <div class="summary-value">{{ $summary['total_projects_involved'] }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Report Period</div>
                    <div class="summary-value">{{ \Carbon\Carbon::parse($start_date)->diffInDays(\Carbon\Carbon::parse($end_date)) + 1 }} Days</div>
                </div>
            </div>
        </div>
    </div>

    @if($summary['top_performer'] !== 'N/A')
    <div class="top-performer">
        <div class="top-performer-title">🏆 Top Performer</div>
        <div class="top-performer-name">{{ $summary['top_performer'] }}</div>
        <div class="top-performer-score">Score: {{ $summary['top_performer_score'] }}/100</div>
    </div>
    @endif

    @if($data->count() > 0)
    <table>
        <thead>
            <tr>
                <th style="width: 20%;">Employee Name</th>
                <th style="width: 12%;">Company</th>
                <th style="width: 12%;">Department</th>
                <th style="width: 10%;">Attendance</th>
                <th style="width: 10%;">Tasks Done</th>
                <th style="width: 10%;">Total Tasks</th>
                <th style="width: 12%;">Completion %</th>
                <th style="width: 8%;">Projects</th>
                <th style="width: 6%;">Score</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
            <tr>
                <td class="employee-name">{{ $row['employee_name'] }}</td>
                <td>{{ $row['company'] }}</td>
                <td>{{ $row['department'] }}</td>
                <td>{{ $row['attendance_days'] }}</td>
                <td>{{ $row['tasks_completed'] }}</td>
                <td>{{ $row['total_tasks'] }}</td>
                <td class="completion-rate {{ $row['task_completion_rate'] >= 80 ? 'rate-high' : ($row['task_completion_rate'] >= 60 ? 'rate-medium' : 'rate-low') }}">
                    {{ number_format($row['task_completion_rate'], 1) }}%
                </td>
                <td>{{ $row['projects_involved'] }}</td>
                <td class="performance-score {{ $row['performance_score'] >= 80 ? 'score-excellent' : ($row['performance_score'] >= 60 ? 'score-good' : ($row['performance_score'] >= 40 ? 'score-average' : 'score-poor')) }}">
                    {{ number_format($row['performance_score'], 1) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="no-data">
        <p>No KPI data found for the selected period.</p>
    </div>
    @endif

    <div class="footer">
        <p>This report contains confidential performance information.</p>
        <p>{{ $company ? $company->name : 'Onchain Software & Research Limited' }} - Human Resource Management System</p>
        <p>Performance scores are calculated based on attendance, task completion, and project involvement.</p>
    </div>
</body>
</html>
