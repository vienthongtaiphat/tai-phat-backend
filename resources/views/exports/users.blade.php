<table>
    <thead>
        <tr>
            <th>Họ tên</th>
            <th>Mã NV</th>
            <th>Ca</th>
            <th>Sđt</th>
            <th>Email</th>
            <th>Username</th>
            <th>CCCD</th>
            <th>Chi nhánh</th>
            <th>Cấp bậc</th>
        </tr>
    </thead>
    <tbody>
        @foreach($otp_histories as $otp)
        <tr>
            <td>{{ $otp->name ?? '' }}</td>
            <td>{{ $otp->user_code ?? '' }}</td>
            <td>{{ \App\Helpers\ConvertToString::instance()->userTypes($otp->type) }}</td>
            <td>{{ $otp->phone ?? '' }}</td>
            <td>{{ $otp->email ?? '' }}</td>
            <td>{{ $otp->username ?? '' }}</td>
            <td>{{ $otp->identity_card ?? '' }}</td>
            <td>{{ $otp->branch->name ?? '' }}</td>
            <td>{{ \App\Helpers\ConvertToString::instance()->roles($otp->role) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>