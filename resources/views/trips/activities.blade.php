<div class="right-col clearfix">
    <post-form ref="post" :id="{{$trip->id}}" :type="'trip'"></post-form>

    <div class="ui-input-duo-mobile clearfix" style="margin-bottom: .6rem">
        <button @click="showTransactionForm" :disabled="tripIsActive ? false : true">
            + New Transaction
        </button>
        <button @click="createPost" type="submit">Post</button>
    </div>

    <div id="activity" class="clearfix section">
    <h4 class="margin-top">Recent Activity</h4>

    <activity-feed :feed="{{json_encode($activities)}}"
        :trip_id="{{ $trip->id }}" :active="tripIsActive">
    </activity-feed>

    </div>
</div>
