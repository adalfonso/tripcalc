<template>
    <div class="popup-wrap" @click.self="hide">
        <div class="popup report closeoutReport">
            <div class="popup-close" @click="hide">&times;</div>
            <h4 class="centered">Closeout Report</h4>
            <hr>

            <div v-for="spender in spendersByLastName" class="spenderGroup">
                <h5>
                    <strong>
                        <div v-if="spender.type === 'virtual'">
                            <span style="font-weight: normal">[V]</span>
                            {{ spender.name }}
                        </div>
                        <div v-else>
                            {{ spender.last_name }}, {{ spender.first_name}}
                        </div>
                    </strong>
                </h5>
                <p class="description">{{ description(spender) }}</p>

                <p v-for="(credit, id) in spender.credits" class="line">
                    <span>${{ currency(credit) }} &larr;</span>
                    <span v-if="allUsers[id].type === 'virtual'">[V]</span>
                    {{ allUsers[id].name }}
                </p>

                <p v-for="(debit, id) in spender.debits" class="line">
                    <span>${{ currency(debit) }} &rarr;</span>
                    <span v-if="allUsers[id].type === 'virtual'">[V]</span>
                    {{ allUsers[id].name }}
                </p>
                <hr>
            </div>
        </div>
    </div>
</template>

<script>

export default {

    props: {
        trip_id: { required: true }
    },

    data() {
        return {
            spenders: [],
            allUsers: {}
        };
    },

	created() {
		axios.get(`/trip/${ this.trip_id }/report/closeout`)
        .then(response => {
           this.spenders = response.data.spenders;
           this.allUsers = response.data.allUsers;
        });
	},

    computed: {
        spendersByLastName (){
            return this.spenders.sort(function(a, b) {
                let person1 = a.last_name + ' ' + a.first_name;
                let person2 = b.last_name + ' ' + b.first_name;

                if(person1 < person2) {
                    return -1;
                }

                if(person1 > person2) {
                    return 1;
                }

                return 0;
            });
        }
    },

    methods: {
        hide() {
            this.$emit('hide');
        },

        currency(amount) {
            return Number(amount).toFixed(2);
        },

        description (spender) {
            if (this.length(spender.credits) + this.length(spender.debits) === 0 ) {
                return 'Perfectly square.';
            }

            return this.length(spender.credits) > 0
            ? 'Paid by ' + this.length(spender.credits)
            : 'Pays ' + this.length(spender.debits);
        },

        length(data) {
            return Object.keys(data).length;
        }
    }
}
</script>
