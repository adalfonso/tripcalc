<div class="right-col clearfix">
    <post-form ref="post" :trip_id="{{$trip->id}}"></post-form>

    <div class="ui-input-duo-mobile clearfix" style="margin-bottom: .6rem">
        <button class="btn" @click="showTransactionForm">
            + New Transaction
        </button>

        <button class="btn form-button" @click="createPost" type="submit">Post</button>
    </div>

    <div id="activity" class="clearfix">
    <h4 class="margin-top">Recent Activity</h4>

    <activity-feed :feed="{{json_encode($activities)}}" :trip_id="{{ $trip->id }}"></activity-feed>

    </div>
</div>
