<div class="right-col clearfix">

<post-form :trip_id="{{$trip->id}}"></post-form>

<div class="clearfix" style="margin-bottom: .6rem">
<button class="btn" @click="transactionForm.visible = true"
    style="float: right; margin-right: .5rem;">
    + New Transaction
</button>
</div>

<div id="activity" class="clearfix">
<h4 class="margin-top">Recent Activity</h4>

@foreach($activities as $item)

@if (get_class($item) === 'App\Post')

<div class="activity-item clearfix">
    <div class="type">
        <div class="speech-bubble"></div>
    </div>
    <div class="info">
        <p>
            <strong>{{ $item->diffForHumans }}</strong>
            | {{ $item->user->full_name }}
        </p>
        <p>{{ $item->content }}</p>
    </div>
</div>

@else

<div class="activity-item clearfix">
    <div class="type"><p>$</p></div>
    <div class="info">
        <p>
            <strong>{{ $item->created_at->diffForHumans() }}</strong>
            <img style="float: right;" class="editButton"
                src="/img/icon/edit.png" @click="openTransactionForm({{ $item->id }})">
        </p>
        <p>
            <strong>{{ $item->dateFormat }}</strong>
             - ${{ $item->amount }}

        </p>
        <p>{{ $item->description }}</p>
    </div>
</div>

@endif

@endforeach
</div>

</div>
