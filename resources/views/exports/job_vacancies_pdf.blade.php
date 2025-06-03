<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Job Vacancies Report</title>
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
            border-bottom: 3px solid #6f42c1;
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
            color: #6f42c1;
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
            color: #6f42c1;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }
        th {
            background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);
            color: white;
            padding: 10px 6px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #5a32a3;
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
            background-color: #f3e8ff;
        }
        .job-title {
            text-align: left;
            font-weight: bold;
            color: #333;
        }
        .job-description {
            text-align: left;
            max-width: 200px;
            word-wrap: break-word;
            font-size: 9px;
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
        .company-info {
            color: #6f42c1;
            font-weight: 500;
        }
        .link-cell {
            max-width: 150px;
            word-wrap: break-word;
            font-size: 9px;
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
                    <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #6f42c1, #5a32a3); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 20px;">
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
            <div class="report-title">Job Vacancies Report</div>
            <div class="report-subtitle">Current Job Openings & Career Opportunities</div>
            <div class="report-info">
                <strong>Generated on:</strong> {{ $generated_at }} | <strong>Generated by:</strong> {{ $generated_by }} | <strong>Document ID:</strong> JOB-{{ date('Ymd-His') }}
            </div>
        </div>
    </div>

    <div class="summary-section">
        <div class="summary-title">Vacancies Summary</div>
        <div class="summary-grid">
            <div class="summary-row">
                <div class="summary-item">
                    <div class="summary-label">Total Vacancies</div>
                    <div class="summary-value">{{ $total_vacancies }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Active Vacancies</div>
                    <div class="summary-value">{{ $summary['active_count'] }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Inactive Vacancies</div>
                    <div class="summary-value">{{ $summary['inactive_count'] }}</div>
                </div>
            </div>
        </div>
    </div>

    @if($job_vacancies->count() > 0)
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">ID</th>
                <th style="width: 20%;">Job Title</th>
                <th style="width: 30%;">Description</th>
                <th style="width: 15%;">Application Link</th>
                <th style="width: 8%;">Status</th>
                <th style="width: 12%;">Company</th>
                <th style="width: 10%;">Created At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($job_vacancies as $job)
            <tr>
                <td>{{ $job->id }}</td>
                <td class="job-title">{{ $job->title ?? '---' }}</td>
                <td class="job-description">{{ strip_tags($job->description ?? '---') }}</td>
                <td class="link-cell">
                    @if($job->link)
                        {{ $job->link }}
                    @else
                        ---
                    @endif
                </td>
                <td>
                    <span class="{{ $job->status ? 'status-active' : 'status-inactive' }}">
                        {{ $job->status ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="company-info">{{ $job->company?->name ?? '---' }}</td>
                <td>{{ $job->created_at ? $job->created_at->format('M d, Y') : '---' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="no-data">
        <p>No job vacancies found.</p>
    </div>
    @endif

    <div class="footer">
        <p>This report contains current job vacancy information.</p>
        <p>{{ $company ? $company->name : 'Onchain Software & Research Limited' }} - Human Resource Management System</p>
        <p>Report generated automatically on {{ $generated_at }}</p>
    </div>
</body>
</html>
