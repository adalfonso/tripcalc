<template>
<div class="popup-wrap" @click.self="hide">
<div class="popup">

    <alert v-if="alert" :type="'agree'" @hide="alert = false"
        :message="'Please select a resolution for each conflict.'">
    </alert>

    <div class="popup-close" @click="hide">&times;</div>

    <h4 class="centered">Merge Conflicts ({{ localData.count() }})</h4>

    <hr>
    <div v-if="!handling" class="centered">
        <p>Conflicts were found when trying to merge this user's transactions with your own.</p>
        <button class="btn btn-extra-thin" @click="handling = true">
            Handle
        </button>
        <button class="btn btn-extra-thin" @click="hide">
            Exit
        </button>
    </div>
    <div v-else class="report centered">
        <p>Please select a resolution for each conflict.</p>
        <hr>
        <table id="mergeVirtualUser">
            <template v-for="conflict in localData.data">
                <tr v-if="conflict.description">
                    <td colspan="2">{{ conflict.description }}</td>
                </tr>
                <tr>
                    <td><b>Date:</b> {{ conflict.date }}</td>
                    <td><b>Amount:</b> {{ conflict.amount }}</td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div class="toggleGroup clearfix">
                            <div :active="conflict.resolution == 'user' "
                                @click="conflict.resolution = 'user'">
                                {{ conflict.conflict.user }}
                            </div>

                            <div :active="conflict.resolution == 'virtual' "
                                @click="conflict.resolution = 'virtual'">
                                <b class="font-dark">[V]</b>
                                {{ conflict.conflict.virtual }}
                            </div>

                            <div :active="conflict.resolution == 'combine' "
                                @click="conflict.resolution = 'combine'">
                                Combine
                            </div>
                        </div>
                        <hr>
                    </td>
                </tr>
            </template>
        </table>
        <div class="btn btn-full" @click.prevent="merge">Merge</div>
    </div>
</div>
</div>
</template>

<script>
export default {

    props: {
        data: { required: true }
    },

    data() {
        return {
            handling: false,
            localData: new Collect(this.data),
            alert: false
        };
    },

    methods: {
        hide() {
            this.$emit('hide');
        },

        merge() {
            let options = ['user', 'virtual', 'combine'];
            let resolved = this.localData.whereIn('resolution', options).count();

            if (resolved !== this.localData.count()) {
                return this.alert = true;
            }

            this.$emit('resolve', this.localData.data);
        }
    }
}
</script>
