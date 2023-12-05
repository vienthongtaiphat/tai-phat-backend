<table>
    <thead>
        <tr>
            <th>Nhân viên</th>
            <th>Thuê bao</th>
            <th>Code</th>
            <th>Kênh nạp</th>
            <th>Kênh đăng ký</th>
            <th>Doanh thu</th>
            <th>Hoàn dư</th>
            <th>Hoàn lỗi</th>
            <th>Tặng sim</th>
            <th>Ngày tạo</th>
        </tr>
    </thead>
    <tbody>
        @foreach($upgrade_histories as $refund)
        <tr>
            <td>{{ $refund->user->name ?? '' }}</td>
            <td>{{ $refund->phone_number }}</td>
            <td>{{ $refund->code ?? '' }}</td>
            <td>{{ $refund->refund->channel === 1 ? "Mặc định" : "EZ" }}</td>
            <td>{{ $refund->refund->register_channel ?? '' }}</td>
            <td data-format="#,##0">{{ $refund->pack->revenue ?? 0}}</td>
            <td data-format="#,##0">{{ $refund->res_amount ?? 0 }}</td>
            <td data-format="#,##0">{{ $refund->err_amount ?? 0 }}</td>
            <td>{{ $refund->refund->gift_type === 1 ? "Sim không gói" : ""}}
                {{ $refund->refund->gift_type === 2 ? "Sim kèm gói": ""}}
            </td>
            <td data-format="DD-MM-YYYY">{{ $refund->created_at }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
