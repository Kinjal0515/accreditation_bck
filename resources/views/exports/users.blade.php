<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Contact</th>
            <th>Email</th>
            <th>Role</th>
            <th>Organisation</th>
            <th>Account Manager</th>
            <th>Status</th>
            <th>Created At</th>
        </tr>
    </thead>
    <tbody>
        @foreach($users as $user)
        <tr>
            <td>{{ $user->id }}</td>
            <td>{{ $user->name }}</td>
            <td>{{ $user->contact }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->role }}</td>
            <td>{{ $user->organisation }}</td>
            <td>{{ $user->account_manager }}</td>
            <td>{{ $user->status }}</td>
            <td>{{ $user->created_at }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
