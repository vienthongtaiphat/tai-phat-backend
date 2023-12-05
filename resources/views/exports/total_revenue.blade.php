<table>
    <thead>
        <tr>
            <th>Nhân viên</th>
            <th>Mã NV</th>
            <th>Doanh thu</th>
            <th>Doanh thu thật</th>
            <th>Thực hoàn</th>
            <th>Hoàn dư</th>
            <th>Hoàn lỗi</th>
            <th>SĐT</th>
            <th>Chi nhánh</th>
            <th>CODE</th>
            <th>KÊNH ĐKÝ</th>
            <th>TẶNG SIM</th>
            <th>NGÀY</th>
        </tr>
    </thead>
    <tbody>
        @foreach($upgrade_histories as $row)
        <tr>
            <td>{{ $row['name'] ?? '' }}</td>
            <td>{{ $row['user_code'] ?? '' }}</td>
            <td data-format="#,##0">{{ $row['revenue'] ?? 0}}</td>
            <td data-format="#,##0">{{ $row['pack_revenue'] ?? 0}}</td>
            <td data-format="#,##0">{{ $row['amount'] ?? 0}}</td>
            <td data-format="#,##0">{{ $row['res_amount'] ?? 0 }}</td>
            <td data-format="#,##0">{{ $row['err_amount'] ?? 0 }}</td>
            <td>{{ $row['phone_number'] }}</td>
            <td>{{ $row['display_name'] }}</td>
            <td>{{ $row['code'] }}</td>
            <td>{{ $row['register_channel'] }}</td>
            <td>{{ $row['gift_type']  === 1 ? "Sim không gói" : ""}}
                {{  $row['gift_type']  === 2 ? "Sim kèm gói": ""}}
            </td>
            <td data-format="DD-MM-YYYY">{{ $row['created_at'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
