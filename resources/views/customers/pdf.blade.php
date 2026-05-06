<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>顧客一覧PDF</title>
    <style>
        body {
            font-family: "ipaexg_custom", sans-serif;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #666;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #eee;
        }
    </style>
</head>
<body>
    <h1>顧客一覧</h1>

    <table>
        <thead>
            <tr>
                <th>会社名</th>
                <th>電話番号</th>
                <th>担当者名</th>
                <th>ステータス</th>
                <th>業種</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customers as $customer)
            <tr>
                <td>{{ $customer->company_name }}</td>
                <td>{{ $customer->phone_number }}</td>
                <td>{{ $customer->contact_person_name }}</td>
                <td>{{ $customer->status }}</td>
                <td>{{ $customer->industry }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>