<!DOCTYPE html>
<html>
<head>
    <title>Approval Slip</title>
    <link href="{{ asset('css/main.css') }}" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
        }

        .header h1 {
            font-size: 2rem;
            color: #2d3748;
            margin: 0;
        }

        .header p {
            font-size: 1rem;
            color: #4a5568;
            margin: 5px 0;
        }

        .content {
            margin-bottom: 20px;
        }

        .content p {
            margin: 8px 0;
            line-height: 1.6;
            color: #2d3748;
        }

        .section {
            margin-bottom: 15px;
        }

        .section h2 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #2d3748;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }

        .section table {
            width: 100%;
            border-collapse: collapse;
        }

        .section table,
        .section th,
        .section td {
            border: 1px solid #e2e8f0;
        }

        .section th,
        .section td {
            padding: 10px;
            text-align: left;
        }

        .section th {
            background-color: #f9fafb;
            color: #2d3748;
        }

        .section td {
            background-color: #ffffff;
            color: #2d3748;
        }

        .section .status {
            font-weight: 700;
            color: #e53e3e;
        }

        .footer {
            text-align: center;
            font-size: 0.875rem;
            color: #6b7280;
            margin-top: 20px;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }

        .signature {
            margin-top: 20px;
        }

        .signature table {
            width: 100%;
            border-collapse: collapse;
        }

        .signature td {
            padding: 10px;
        }

        .signature .title {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            text-align: left;
        }

        .signature .name {
            border-bottom: 1px solid #2d3748;
            padding-bottom: 2px;
            margin-top: 0;
        }

        .signature .position {
            margin-top: 5px;
            font-style: italic;
            color: #4a5568;
        }

        .signature .noted {
            text-align: left;
        }

        .signature .approved {
            text-align: right;
        }

        .disclaimer {
            margin-top: 0;
            font-size: 0.875rem;
            color: #4a5568;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Reservation Approval Slip</h1>
            <p>Certificate of Reservation Confirmation</p>
        </div>
        <div class="content">
            <p>This document certifies that the reservation details below have been reviewed and approved. Please retain this document as proof of the reservation's confirmation and approval.</p>
            <p>Reservation Details are as follows:</p>
        </div>
        <div class="section">
            <h2>Details</h2>
            <table>
                <tr>
                    <th>ID</th>
                    <td>{{ $id }}</td>
                </tr>
                <tr>
                    <th>Reserved by</th>
                    <td>{{ $reserved_by }}</td>
                </tr>
                <tr>
                    <th>Schedule</th>
                    <td>{{ $schedule }}</td>
                </tr>
                <tr>
                    <th>Options</th>
                    <td>{{ $options }}</td>
                </tr>
                <tr>
                    <th>Purpose</th>
                    <td>{{ $purpose }}</td>
                </tr>
                <tr>
                    <th>Remarks</th>
                    <td>{{ $remarks }}</td>
                </tr>
                <tr>
                    <th class="status">Status</th>
                    <td class="status">{{ $status }}</td>
                </tr>
            </table>
        </div>
        <div class="footer">
            <p>Issued on {{ date('F j, Y') }}</p>
        </div>
        <div class="signature">
            <table>
                <tr>
                    <td class="noted">
                        <p class="title">Noted by:</p>
                        <p class="name">{{ $noted_by }}</p>
                    </td>
                    <td class="approved">
                        <p class="title">Approved by:</p>
                        <p class="name">{{ $approved_by }}</p>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div class="disclaimer">
        <i>This document has been automatically generated and is certified as accurate and valid by the authorized signatories. Any alterations or unauthorized use may invalidate the document's integrity.</i>
    </div>
</body>
</html>
