<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transport Cost Analysis & Optimization Report (TCAO)</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #333; margin: 30px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #0d6efd; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { font-size: 22px; color: #0d6efd; margin: 0; }
        .header p { margin: 3px 0 0 0; color: #6c757d; font-size: 11px; }
        .summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 25px; }
        .card { border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; background: #f8fafc; }
        .card-title { font-size: 10px; text-transform: uppercase; color: #64748b; font-weight: bold; margin-bottom: 4px; }
        .card-value { font-size: 18px; font-weight: bold; color: #0f172a; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: #0d6efd; color: white; text-align: left; padding: 8px 12px; font-size: 11px; text-transform: uppercase; }
        td { padding: 8px 12px; border-bottom: 1px solid #e2e8f0; }
        tr:nth-child(even) { background: #f8fafc; }
        .footer { margin-top: 40px; text-align: center; font-size: 10px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 10px; }
    </style>
</head>
<body onload="window.print();">
    <div class="header">
        <div>
            <h1>HIRNA TNVS Fleet Management</h1>
            <p>Transport Cost Analysis & Optimization (TCAO) Official Report</p>
        </div>
        <div style="text-align: right;">
            <p><strong>Generated:</strong> {{ date('F j, Y - H:i') }}</p>
            <p><strong>Status:</strong> Verified Operational Audit</p>
        </div>
    </div>

    <div class="summary-grid">
        <div class="card">
            <div class="card-title">Total Operating Cost</div>
            <div class="card-value">PHP {{ number_format($totalOperationalCost, 2) }}</div>
        </div>
        <div class="card">
            <div class="card-title">Average Cost / KM</div>
            <div class="card-value">PHP {{ $costPerKm }} / km</div>
        </div>
        <div class="card">
            <div class="card-title">Fuel Cost Share</div>
            <div class="card-value">PHP {{ number_format($totalFuelCost, 2) }}</div>
        </div>
        <div class="card">
            <div class="card-title">Maintenance Cost</div>
            <div class="card-value">PHP {{ number_format($totalMaintenanceCost, 2) }}</div>
        </div>
    </div>

    <h3>Vehicle Cost-per-KM Breakdown</h3>
    <table>
        <thead>
            <tr>
                <th>License Plate</th>
                <th>Model</th>
                <th>Fuel / Energy Source</th>
                <th>Distance (KM)</th>
                <th>Fuel Expense</th>
                <th>Maintenance Expense</th>
                <th>Total Cost</th>
                <th>Cost / KM</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vehicles as $veh)
            <tr>
                <td><strong>{{ $veh['license_plate'] }}</strong></td>
                <td>{{ $veh['model'] }}</td>
                <td>
                    <strong>{{ $veh['fuel_type'] }}</strong>
                </td>
                <td>{{ $veh['distance_km'] }} km</td>
                <td>PHP {{ number_format($veh['fuel_cost'], 2) }}</td>
                <td>PHP {{ number_format($veh['maintenance_cost'], 2) }}</td>
                <td>PHP {{ number_format($veh['total_cost'], 2) }}</td>
                <td><strong>PHP {{ $veh['cost_per_km'] }} / km</strong></td>
            </tr>
            @endforeach
        </tbody>

    </table>

    <div class="footer">
        Hirna TNVS Fleet Operations Management System &bull; Enterprise Capstone Integration &bull; Page 1 of 1
    </div>
</body>
</html>
