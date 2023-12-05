<table>
    <thead>
        <tr>
            <th>Thuê bao</th>
            <th>Tiền hoàn</th>
            <th>Thực hoàn</th>
            <th>Tài khoản hoàn tiền</th>
            <th>Nhân viên</th>
            <th>Gói cước</th>
            <th>Có/Không</th>
            <th>Trạng thái</th>
            <th>Ngày tạo</th>
        </tr>
    </thead>
    <tbody>
        @foreach($refund_histories as $refund)
        <tr>
            <td>{{ $refund->phone_number }}</td>
            <td data-format="#,##0">{{ $refund->amount ?? 0}}</td>
            <td data-format="#,##0">{{ $refund->amount_tran ?? 0 }}</td>
            <td>{{ $refund->refundAccount->username ?? '' }}</td>
            <td>{{ $refund->user->name ?? '' }}</td>
            <td>{{ $refund->code ?? '' }}</td>
            <td>
                {{  $refund->is_exist === 1 ? "Có": "Không"}}
            </td>
            <td>{{ \App\Helpers\ConvertToString::instance()->refundStatus($refund->status) }}</td>
            <td>{{ $refund->created_at }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
