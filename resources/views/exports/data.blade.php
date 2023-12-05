<table>
    <thead>
        <tr>
            <th>Thuê bao</th>
            <th>Gói cước</th>
            <th>Loại</th>
            <th>Đăng ký lần đầu</th>
            <th>Ngày hết hạn</th>
            <th>Nhân viên</th>
            <th>Chi nhánh</th>
            <th>Trạng thái gọi</th>
            <th>Ngày tạo</th>
            <th>Số dư</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data_list as $data)
        <tr>
            <td data-format="0##########">{{ $data->phone_number }}</td>
            <td>{{ $data->code ?? '' }}</td>
            <td>{{ $data->phone_type ?? '' }}</td>
            <td>{{ $data->first_register_date ?? '' }}</td>
            <td>{{ $data->first_expired_date ?? '' }}</td>
            <td>{{ $data->assigned_to_user->name ?? '' }}</td>
            <td>{{ $data->branch->name ?? '' }}</td>
            <td>{{ \App\Helpers\ConvertToString::instance()->callStatus($data->status) }}</td>
            <td>{{ $data->created_at }}</td>
            <td data-format="@">{{ $data->balance ?? '' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>