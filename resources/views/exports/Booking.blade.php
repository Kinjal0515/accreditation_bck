<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Event Name</th>
            <th>Org Name</th>
            <th>Attendee</th>
            <th>Number</th>
            <th>Ticket</th>
            <th>Qty</th>
            <th>Disc</th>
            <th>B Amt</th>
            <th>Total</th>
            <th>Status</th>
            <th>Disable</th>
            <th>Purchase Date</th>
        </tr>
    </thead>
    <tbody>
            @foreach($Booking as $index => $bookings)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $booking->ticket->event->name ?? 'N/A' }}</td>
                    <td>{{ $booking->ticket->event->user->name ?? 'N/A' }}</td>
                    <td>{{ $bookings->userData->name ?? 'No User' }}</td>
                    <td>{{ $bookings->number ?? '' }}</td>
                    <td>{{ $bookings->ticket_type ?? '' }}</td>
                    <td>{{ $bookings->quantity ?? 0 }}</td>
                    <td>{{ $bookings->discount ?? 0 }}</td>
                    <td>{{ $bookings->base_amount ?? 0 }}</td>
                    <td>{{ $bookings->amount ?? 0 }}</td>
                    <td>{{ $bookings->status }}</td>
                    <td>
                        <!-- If you want a checkbox for 'Disable', use this -->
                        <input type="checkbox" {{ $bookings->disabled ? 'checked' : '' }} disabled>
                    </td>
                    <td>{{ $bookings->created_at->format('d-m-Y | h:i:s A') }}</td>
                </tr>
            @endforeach
        </tbody>
</table>
