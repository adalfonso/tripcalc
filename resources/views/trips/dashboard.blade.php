@extends('layout')

@section('content')
<div class='centered' id="trips">

    @if (count($errors) > 0)
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <h1><strong>Trips</strong></h1>
    <div class='trip-tile' @click="tripForm.visible = true">
        <h3>+ Add New Trip</h3>
    </div>

    <trip-form v-if="tripForm.visible" @close="tripForm.visible = false">
    </trip-form>

    @if (isset($trips))
        @foreach($trips as $trip)
            <a href="/trips/{{ $trip->id }}">
            <div class="trip-tile">
                <h3>{{ $trip->name }}</h3>
                @if ($trip->budget)
                    <p><strong>Budget:</strong> ${{ $trip->budget }}</p>
                @endif

                @if ($trip->description)
                    <p>{{ $trip->description }}</p>
                @endif
            </div>
            </a>
        @endforeach
    @else
        <h5>No trips yet!? Click that big button to get started!</h5>
    @endif
</div>
@endsection

@section('vue')
    <script>
        new Vue({
            el: '#app',

            data: {
                tripForm: { visible: false }
            }
        });
    </script>
@overwrite