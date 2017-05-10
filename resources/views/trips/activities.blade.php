<div class="right-col clearfix">

<post-form ref="post" :trip_id="{{$trip->id}}"></post-form>

<div class="ui-input-duo-mobile clearfix" style="margin-bottom: .6rem">
<button class="btn" @click="showTransactionForm(null)">
    + New Transaction
</button>

<button class="btn form-button" @click="createPost" type="submit">Post</button>
</div>

<div id="activity" class="clearfix">
<h4 class="margin-top">Recent Activity</h4>

@foreach($activities as $item)

@if (get_class($item) === 'App\Transaction')
	<div class="activity-item clearfix">
	    <div class="type"><p>$</p></div>
	    <div class="info">
	        <p>
	            <strong>{{ $item->created_at->diffForHumans() }}</strong>
	            <img style="float: right;" class="editButton"
	                src="/img/icon/edit.png" @click="showTransactionForm({{ $item->id }})">
	        </p>
	        <p>
	            <strong>{{ $item->dateFormat }}</strong>
	             - ${{ $item->amount }}
	        </p>
            @if ($item->description)
    	        <p>{{ $item->description }}</p>
            @endif
            <ul class="hashtags-plaintext clearfix">
                @foreach($item->hashtags as $hashtag)
                    <li>#{{ $hashtag->tag }}</li>
                @endforeach
            </ul>
	    </div>
	</div>
@else
	<div class="activity-item clearfix">
	    <div class="type">
	        <div class="speech-bubble"></div>
	    </div>
		<post :data="{{json_encode($item)}}" :trip_id="'{{$trip->id}}'"></post>
	</div>
@endif

@endforeach
</div>

</div>
