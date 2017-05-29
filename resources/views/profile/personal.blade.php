@extends('profile.index')

@section('personal')

	<profile-info-form v-if="profileInfoForm.visible"
		@hide="profileInfoForm.visible = false">
    </profile-info-form>


	@if($friendRequests > 0)
		<request-popup :type="'friend'" >
    	</request-popup>
	@endif

	@if($tripRequests > 0)
		<request-popup :type="'trip'" >
    	</request-popup>
	@endif

@stop

@section('vue')
	<script>
		new Vue({
		    el: '#app',

		    data: {
		        profileInfoForm: { visible: false }
		    }
		});
	</script>
@overwrite
