<table>
    <thead>
        <tr>
            <th>Thuê bao</th>
            <th>Nhân viên</th>
            <th>Gói cước</th>
            <th>Ngày tạo</th>
        </tr>
    </thead>
    <tbody>
        @foreach($otp_histories as $otp)
        <tr>
            <td>{{ $otp->phone_number }}</td>
            <td>{{ $otp->user->name ?? '' }}</td>
            <td>{{ $otp->code }}</td>
            <td data-format="dd-mm-yyyy">{{ $otp->created_at }}</td>
        </tr>
        @endforeach
    </tbody>
</table>