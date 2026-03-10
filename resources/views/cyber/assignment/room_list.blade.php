@foreach($rooms as $room)
    @include('cyber.assignment.room_card', ['room' => $room])
@endforeach
@if($rooms->count() === 0)
    <div class="col-12">
        <div class="alert alert-light border">Belum ada ruang mapel.</div>
    </div>
@endif
<div class="col-12 d-flex justify-content-end">
    {{ $rooms->links() }}
</div>
