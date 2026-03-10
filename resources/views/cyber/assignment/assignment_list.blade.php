@foreach($assignments as $assignment)
    @include('cyber.assignment.card', ['assignment' => $assignment, 'submittedIds' => $submittedIds ?? []])
@endforeach
@if($assignments->count() === 0)
    <div class="col-12">
        <div class="alert alert-light border">Belum ada tugas.</div>
    </div>
@endif
@if(method_exists($assignments, 'links'))
    <div class="col-12 d-flex justify-content-end">
        {{ $assignments->links() }}
    </div>
@endif
