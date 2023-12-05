<table>
    <thead>
        <tr>
            <th>Nhân viên</th>
            <th>Doanh thu</th>
            <th>SĐT</th>
            <th>CODE</th>
            <th>Loại</th>
            <th>Chi nhánh</th>
            <th>Ngày tạo</th>
        </tr>
    </thead>
    <tbody>
        @foreach($list as $refund)
        <tr>
            <td>{{ $refund['name'] ?? '' }}</td>
            <td data-format="#,##0">{{ $refund['revenue'] ?? '' }}</td>
            <td>{{ $refund['phone_number'] ?? '' }}</td>
            <td>{{ $refund['pack_code'] ?? '' }}</td>
            <td>{{ \App\Helpers\ConvertToString::instance()->packCodeType($refund['type']) }}</td>
            <td>{{ $refund['display_name'] ?? '' }}</td>
            <td data-format="DD-MM-YYYY">{{ $refund['created_at'] ?? '' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>